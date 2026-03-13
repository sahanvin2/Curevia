<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Category;
use App\Models\ContributorProfile;
use App\Models\Product;
use App\Models\Story;
use App\Models\User;
use App\Services\DocumentImportService;
use App\Services\MediaService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AdminController extends Controller
{
    public function dashboard()
    {
        $totalArticles = Article::count();
        $totalUsers = User::count();
        $totalPageViews = Article::sum('views') + Story::sum('views');
        $totalProducts = Product::count();

        $articlesThisWeek = Article::where('created_at', '>=', now()->subWeek())->count();
        $usersThisWeek = User::where('created_at', '>=', now()->subWeek())->count();

        $recentArticles = Article::with('category')
            ->orderByDesc('created_at')
            ->take(5)
            ->get();

        $topContributors = ContributorProfile::with('user')
            ->orderByDesc('reputation')
            ->take(4)
            ->get();

        return view('admin.dashboard', compact(
            'totalArticles', 'totalUsers', 'totalPageViews', 'totalProducts',
            'articlesThisWeek', 'usersThisWeek',
            'recentArticles', 'topContributors'
        ));
    }

    public function productsIndex()
    {
        $products = Product::orderByDesc('created_at')->get();
        return view('admin.products.index', compact('products'));
    }

    public function productsCreate()
    {
        return view('admin.products.edit', ['product' => new Product()]);
    }

    public function productsStore(Request $request)
    {
        $data = $request->validate([
            'name'          => 'required|string|max:255',
            'slug'          => 'nullable|string|max:255|unique:products,slug',
            'description'   => 'nullable|string',
            'price'         => 'required|numeric|min:0',
            'original_price'=> 'nullable|numeric|min:0',
            'category'      => 'nullable|string|max:100',
            'badge'         => 'nullable|string|max:50',
            'image'         => 'nullable|url',
            'image_file'    => 'nullable|file|image|max:10240',
            'affiliate_url' => 'nullable|url',
            'is_active'     => 'boolean',
        ]);
        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);
        $data['is_active'] = $request->boolean('is_active', true);
        if ($request->hasFile('image_file')) {
            $uploaded = app(MediaService::class)->uploadFile($request->file('image_file'), 'products');
            if ($uploaded) $data['image'] = $uploaded;
        }
        unset($data['image_file']);
        Product::create($data);
        return redirect()->route('admin.products.index')->with('success', 'Product created successfully.');
    }

    public function productsEdit(Product $product)
    {
        return view('admin.products.edit', compact('product'));
    }

    public function productsUpdate(Request $request, Product $product)
    {
        $data = $request->validate([
            'name'          => 'required|string|max:255',
            'description'   => 'nullable|string',
            'price'         => 'required|numeric|min:0',
            'original_price'=> 'nullable|numeric|min:0',
            'category'      => 'nullable|string|max:100',
            'badge'         => 'nullable|string|max:50',
            'image'         => 'nullable|url',
            'image_file'    => 'nullable|file|image|max:10240',
            'affiliate_url' => 'nullable|url',
            'is_active'     => 'boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);
        if ($request->hasFile('image_file')) {
            $media    = app(MediaService::class);
            $uploaded = $media->uploadFile($request->file('image_file'), 'products');
            if ($uploaded) {
                if ($product->image && $media->isOwnCdn($product->image)) {
                    $media->delete($product->image);
                }
                $data['image'] = $uploaded;
            }
        }
        unset($data['image_file']);
        $product->update($data);
        return redirect()->route('admin.products.index')->with('success', 'Product updated successfully.');
    }

    public function productsDestroy(Product $product)
    {
        $product->delete();
        return redirect()->route('admin.products.index')->with('success', 'Product deleted.');
    }

    /* ─── ARTICLES ─── */
    public function articlesIndex(Request $request)
    {
        $articles = Article::with(['category', 'author'])
            ->when($request->q, fn($q, $s) => $q->where('title', 'like', "%$s%"))
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->when($request->category, fn($q, $c) => $q->where('category_id', $c))
            ->orderByDesc('created_at')
            ->paginate(20);
        $categories = Category::orderBy('name')->get();
        return view('admin.articles.index', compact('articles', 'categories'));
    }

    public function articlesCreate()
    {
        $categories = Category::orderBy('name')->get();
        $contributors = User::whereIn('role', ['admin', 'contributor'])->orderBy('name')->get();
        return view('admin.articles.edit', ['article' => new Article(), 'categories' => $categories, 'contributors' => $contributors]);
    }

    public function articlesStore(Request $request)
    {
        $data = $request->validate([
            'title'         => 'required_without:document_file|string|max:255',
            'slug'          => 'nullable|string|max:255|unique:articles,slug',
            'summary'       => 'required_without:document_file|string',
            'content'       => 'nullable|string',
            'category_id'   => 'required|exists:categories,id',
            'author_id'     => 'required|exists:users,id',
            'status'        => 'required|in:draft,review,published',
            'featured_image'=> 'nullable|url|max:500',
            'featured_image_file' => 'nullable|file|image|max:10240',
            'gallery_image_files' => 'nullable|array|max:40',
            'gallery_image_files.*' => 'nullable|file|image|max:10240',
            'video_url' => 'nullable|url|max:500',
            'video_file' => 'nullable|file|mimetypes:video/mp4,video/webm,video/quicktime|max:102400',
            'document_file' => 'nullable|file|mimes:doc,docx,pdf,txt,md|max:20480',
            'section_titles' => 'nullable|array|max:20',
            'section_titles.*' => 'nullable|string|max:255',
            'section_bodies' => 'nullable|array|max:20',
            'section_bodies.*' => 'nullable|string',
            'section_video_urls' => 'nullable|array|max:20',
            'section_video_urls.*' => 'nullable|url|max:500',
            'section_image_files' => 'nullable|array|max:20',
            'section_image_files.*' => 'nullable|array|max:5',
            'section_image_files.*.*' => 'nullable|file|image|max:10240',
            'section_video_files' => 'nullable|array|max:20',
            'section_video_files.*' => 'nullable|file|mimetypes:video/mp4,video/webm,video/quicktime|max:102400',
            'read_time'     => 'nullable|integer|min:1',
        ]);

        $media = app(MediaService::class);
        $documentSections = [];

        if ($request->hasFile('document_file')) {
            $parsed = app(DocumentImportService::class)->parse($request->file('document_file'));
            if (!$parsed) {
                throw ValidationException::withMessages([
                    'document_file' => 'Unable to parse this document. Use DOCX, PDF, TXT, or MD.',
                ]);
            }
            $data['title'] = $data['title'] ?: ($parsed['title'] ?? null);
            $data['summary'] = $data['summary'] ?: ($parsed['summary'] ?? null);
            $data['content'] = $data['content'] ?: ($parsed['content'] ?? null);
            $documentSections = $parsed['sections'] ?? [];
        }

        if (empty($data['title']) || empty($data['summary']) || empty($data['content'])) {
            throw ValidationException::withMessages([
                'document_file' => 'Title, summary, and content are required. Upload a richer document or fill missing fields.',
            ]);
        }

        $data['slug'] = $data['slug'] ?? Str::slug($data['title']);

        if ($request->hasFile('featured_image_file')) {
            $uploaded = $media->uploadFile($request->file('featured_image_file'), 'articles');
            if ($uploaded) $data['featured_image'] = $uploaded;
        }

        if ($request->hasFile('video_file')) {
            $videoUploaded = $media->uploadFile($request->file('video_file'), 'articles/videos');
            if ($videoUploaded) $data['video_url'] = $videoUploaded;
        }

        $galleryUrls = [];
        if ($request->hasFile('gallery_image_files')) {
            foreach ((array) $request->file('gallery_image_files') as $galleryImage) {
                if (!$galleryImage) continue;
                $galleryUploaded = $media->uploadFile($galleryImage, 'articles/gallery');
                if ($galleryUploaded) $galleryUrls[] = $galleryUploaded;
            }
        }

        $sections = $this->buildSectionsFromRequest($request, $media, $documentSections);
        if (!empty($sections)) {
            $data['content_sections'] = $sections;
            if (empty($data['content'])) {
                $data['content'] = $this->sectionsToContent($sections);
            }
        }

        $data['images'] = $galleryUrls;

        unset(
            $data['featured_image_file'],
            $data['gallery_image_files'],
            $data['video_file'],
            $data['document_file'],
            $data['section_titles'],
            $data['section_bodies'],
            $data['section_video_urls'],
            $data['section_image_files'],
            $data['section_video_files']
        );

        if ($data['status'] === 'published') $data['published_at'] = now();
        Article::create($data);
        return redirect()->route('admin.articles.index')->with('success', 'Article created.');
    }

    public function articlesEdit(Article $article)
    {
        $categories = Category::orderBy('name')->get();
        $contributors = User::whereIn('role', ['admin', 'contributor'])->orderBy('name')->get();
        return view('admin.articles.edit', compact('article', 'categories', 'contributors'));
    }

    public function articlesUpdate(Request $request, Article $article)
    {
        $data = $request->validate([
            'title'         => 'required_without:document_file|string|max:255',
            'summary'       => 'required_without:document_file|string',
            'content'       => 'nullable|string',
            'category_id'   => 'required|exists:categories,id',
            'author_id'     => 'required|exists:users,id',
            'status'        => 'required|in:draft,review,published',
            'featured_image'=> 'nullable|url|max:500',
            'featured_image_file' => 'nullable|file|image|max:10240',
            'gallery_image_files' => 'nullable|array|max:40',
            'gallery_image_files.*' => 'nullable|file|image|max:10240',
            'video_url' => 'nullable|url|max:500',
            'video_file' => 'nullable|file|mimetypes:video/mp4,video/webm,video/quicktime|max:102400',
            'document_file' => 'nullable|file|mimes:doc,docx,pdf,txt,md|max:20480',
            'section_titles' => 'nullable|array|max:20',
            'section_titles.*' => 'nullable|string|max:255',
            'section_bodies' => 'nullable|array|max:20',
            'section_bodies.*' => 'nullable|string',
            'section_video_urls' => 'nullable|array|max:20',
            'section_video_urls.*' => 'nullable|url|max:500',
            'section_image_files' => 'nullable|array|max:20',
            'section_image_files.*' => 'nullable|array|max:5',
            'section_image_files.*.*' => 'nullable|file|image|max:10240',
            'section_video_files' => 'nullable|array|max:20',
            'section_video_files.*' => 'nullable|file|mimetypes:video/mp4,video/webm,video/quicktime|max:102400',
            'read_time'     => 'nullable|integer|min:1',
        ]);

        $media = app(MediaService::class);
        $documentSections = [];

        if ($request->hasFile('document_file')) {
            $parsed = app(DocumentImportService::class)->parse($request->file('document_file'));
            if (!$parsed) {
                throw ValidationException::withMessages([
                    'document_file' => 'Unable to parse this document. Use DOCX, PDF, TXT, or MD.',
                ]);
            }
            $data['title'] = $data['title'] ?: ($parsed['title'] ?? $article->title);
            $data['summary'] = $data['summary'] ?: ($parsed['summary'] ?? $article->summary);
            $data['content'] = $data['content'] ?: ($parsed['content'] ?? $article->content);
            $documentSections = $parsed['sections'] ?? [];
        }

        if (empty($data['title']) || empty($data['summary']) || empty($data['content'])) {
            throw ValidationException::withMessages([
                'document_file' => 'Title, summary, and content are required. Upload a richer document or fill missing fields.',
            ]);
        }

        if ($request->hasFile('featured_image_file')) {
            $uploaded = $media->uploadFile($request->file('featured_image_file'), 'articles');
            if ($uploaded) {
                // Delete old B2 image when replacing
                if ($article->featured_image && $media->isOwnCdn($article->featured_image)) {
                    $media->delete($article->featured_image);
                }
                $data['featured_image'] = $uploaded;
            }
        }

        if ($request->hasFile('video_file')) {
            if ($article->video_url && $media->isOwnCdn($article->video_url)) {
                $media->delete($article->video_url);
            }
            $videoUploaded = $media->uploadFile($request->file('video_file'), 'articles/videos');
            if ($videoUploaded) $data['video_url'] = $videoUploaded;
        }

        $galleryUrls = is_array($article->images) ? $article->images : [];
        if ($request->hasFile('gallery_image_files')) {
            foreach ((array) $request->file('gallery_image_files') as $galleryImage) {
                if (!$galleryImage) continue;
                $galleryUploaded = $media->uploadFile($galleryImage, 'articles/gallery');
                if ($galleryUploaded) $galleryUrls[] = $galleryUploaded;
            }
        }

        $sections = $this->buildSectionsFromRequest($request, $media, !empty($documentSections) ? $documentSections : (array) $article->content_sections);
        if (!empty($sections)) {
            $data['content_sections'] = $sections;
            if (empty($data['content'])) {
                $data['content'] = $this->sectionsToContent($sections);
            }
        }

        $data['images'] = array_values(array_filter($galleryUrls));

        unset(
            $data['featured_image_file'],
            $data['gallery_image_files'],
            $data['video_file'],
            $data['document_file'],
            $data['section_titles'],
            $data['section_bodies'],
            $data['section_video_urls'],
            $data['section_image_files'],
            $data['section_video_files']
        );

        if ($data['status'] === 'published' && !$article->published_at) {
            $data['published_at'] = now();
        }
        $article->update($data);
        return redirect()->route('admin.articles.index')->with('success', 'Article updated.');
    }

    private function buildSectionsFromRequest(Request $request, MediaService $media, array $seedSections = []): array
    {
        $titles = (array) $request->input('section_titles', []);
        $bodies = (array) $request->input('section_bodies', []);
        $videoUrls = (array) $request->input('section_video_urls', []);

        $max = max(count($titles), count($bodies), count($videoUrls), count($seedSections));
        $sections = [];

        for ($i = 0; $i < $max; $i++) {
            $seed = $seedSections[$i] ?? [];
            $title = trim((string) ($titles[$i] ?? ($seed['title'] ?? '')));
            $body = trim((string) ($bodies[$i] ?? ($seed['body'] ?? '')));

            $images = [];
            if (!empty($seed['images']) && is_array($seed['images'])) {
                $images = array_values(array_filter($seed['images']));
            }

            if ($request->hasFile("section_image_files.$i")) {
                foreach ((array) $request->file("section_image_files.$i") as $sectionImage) {
                    if (!$sectionImage) continue;
                    $uploaded = $media->uploadFile($sectionImage, 'articles/sections');
                    if ($uploaded) $images[] = $uploaded;
                }
                $images = array_slice(array_values(array_filter($images)), 0, 5);
            }

            $videoUrl = trim((string) ($videoUrls[$i] ?? ($seed['video_url'] ?? '')));
            if ($request->hasFile("section_video_files.$i")) {
                $uploadedVideo = $media->uploadFile($request->file("section_video_files.$i"), 'articles/sections/videos');
                if ($uploadedVideo) $videoUrl = $uploadedVideo;
            }

            if ($title === '' && $body === '' && empty($images) && $videoUrl === '') {
                continue;
            }

            $sections[] = [
                'title' => $title !== '' ? $title : ('Section ' . ($i + 1)),
                'body' => $body,
                'images' => $images,
                'video_url' => $videoUrl !== '' ? $videoUrl : null,
            ];
        }

        return $sections;
    }

    private function sectionsToContent(array $sections): string
    {
        return collect($sections)
            ->map(function ($section) {
                $title = trim((string) ($section['title'] ?? 'Section'));
                $body = trim((string) ($section['body'] ?? ''));
                return '## ' . $title . "\n\n" . $body;
            })
            ->implode("\n\n");
    }

    public function articlesDestroy(Article $article)
    {
        $article->delete();
        return redirect()->route('admin.articles.index')->with('success', 'Article deleted.');
    }

    /* ─── USERS ─── */
    public function usersIndex(Request $request)
    {
        $users = User::withCount(['contributorProfile as articles_count' => function ($q) {
                // join articles authored
            }])
            ->when($request->q, fn($q, $s) => $q->where('name', 'like', "%$s%")->orWhere('email', 'like', "%$s%"))
            ->when($request->role, fn($q, $r) => $q->where('role', $r))
            ->orderByDesc('created_at')
            ->paginate(25);
        return view('admin.users.index', compact('users'));
    }

    public function usersEdit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function usersUpdate(Request $request, User $user)
    {
        $data = $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role'  => 'required|in:admin,contributor,user',
        ]);
        $user->update($data);
        if ($request->filled('password')) {
            $request->validate(['password' => 'min:8|confirmed']);
            $user->update(['password' => bcrypt($request->password)]);
        }
        return redirect()->route('admin.users.index')->with('success', 'User updated.');
    }

    public function usersDestroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }
        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'User deleted.');
    }

    /* ─── ANALYTICS ─── */
    public function analytics()
    {
        $totalViews = Article::sum('views') + Story::sum('views');
        $topArticles = Article::with('category')->orderByDesc('views')->take(10)->get();
        $topStories = Story::with('category')->orderByDesc('views')->take(5)->get();
        $usersByRole = User::selectRaw('role, count(*) as count')->groupBy('role')->get();
        $articlesByCategory = Category::withCount('articles')->orderByDesc('articles_count')->get();
        $recentUsers = User::orderByDesc('created_at')->take(8)->get();
        return view('admin.analytics', compact(
            'totalViews', 'topArticles', 'topStories', 'usersByRole', 'articlesByCategory', 'recentUsers'
        ));
    }
}
