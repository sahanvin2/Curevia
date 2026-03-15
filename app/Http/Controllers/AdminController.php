<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Category;
use App\Models\ContentView;
use App\Models\ContributorProfile;
use App\Models\Product;
use App\Models\Story;
use App\Models\User;
use App\Services\ArticleCategoryClassifier;
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
            'existing_gallery_images' => 'nullable|array|max:40',
            'existing_gallery_images.*' => 'nullable|url|max:500',
            'gallery_image_files' => 'nullable|array|max:40',
            'gallery_image_files.*' => 'nullable|file|image|max:10240',
            'video_url' => 'nullable|url|max:500',
            'video_file' => 'nullable|file|mimetypes:video/mp4,video/webm,video/quicktime|max:102400',
            'remove_featured_image' => 'nullable|boolean',
            'remove_video' => 'nullable|boolean',
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
            'remove_section_images' => 'nullable|array|max:20',
            'remove_section_images.*' => 'nullable|array|max:10',
            'remove_section_images.*.*' => 'nullable|url|max:500',
            'remove_section_video' => 'nullable|array|max:20',
            'remove_section_video.*' => 'nullable|in:0,1',
            'read_time'     => 'nullable|integer|min:1',
        ]);

        $media = app(MediaService::class);
        $documentSections = [];
        $sourceDocumentMeta = [];

        if ($request->hasFile('document_file')) {
            $documentFile = $request->file('document_file');
            $parsed = app(DocumentImportService::class)->parse($documentFile);
            if (!$parsed) {
                throw ValidationException::withMessages([
                    'document_file' => 'Unable to parse this document. Use DOCX, PDF, TXT, or MD.',
                ]);
            }
            $data['title'] = $data['title'] ?: ($parsed['title'] ?? null);
            $data['summary'] = $data['summary'] ?: ($parsed['summary'] ?? null);
            $data['content'] = $data['content'] ?: ($parsed['content'] ?? null);
            $documentSections = $parsed['sections'] ?? [];

            $classification = app(ArticleCategoryClassifier::class)->classify(
                (string) ($data['title'] ?? ''),
                (string) ($data['summary'] ?? ''),
                (string) ($data['content'] ?? '')
            );
            $detectedSlug = (string) ($classification['slug'] ?? '');
            if ($detectedSlug !== '' && (float) ($classification['confidence'] ?? 0) >= 0.62) {
                $detectedCategoryId = Category::where('slug', $detectedSlug)->value('id');
                if ($detectedCategoryId) {
                    $data['category_id'] = (int) $detectedCategoryId;
                }
            }

            $sourceDocumentUrl = $media->uploadFile($documentFile, 'articles/documents');
            if ($sourceDocumentUrl) {
                $sourceDocumentMeta = [
                    'source_document_url' => $sourceDocumentUrl,
                    'source_document_name' => $documentFile->getClientOriginalName(),
                    'source_document_ext' => strtolower((string) $documentFile->getClientOriginalExtension()),
                    'source_document_uploaded_at' => now()->toIso8601String(),
                ];
            }
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

        $galleryUrls = $this->syncGalleryFromRequest($request, $media, 'articles', []);

        $sections = $this->buildSectionsFromRequest($request, $media, $documentSections, 'articles');
        if (!empty($sections)) {
            $data['content_sections'] = $sections;
            if (empty($data['content'])) {
                $data['content'] = $this->sectionsToContent($sections);
            }
        }

        $data['images'] = $galleryUrls;
        if (!empty($sourceDocumentMeta)) {
            $data['meta'] = array_merge(is_array($data['meta'] ?? null) ? $data['meta'] : [], $sourceDocumentMeta);
        }
        if (empty($data['featured_image'])) {
            $data['featured_image'] = $this->resolveFeaturedImage(null, (string) $data['title'], null, $galleryUrls, $sections, $media, 'articles/thumbnails');
        }

        unset(
            $data['featured_image_file'],
            $data['existing_gallery_images'],
            $data['gallery_image_files'],
            $data['video_file'],
            $data['remove_featured_image'],
            $data['remove_video'],
            $data['document_file'],
            $data['section_titles'],
            $data['section_bodies'],
            $data['section_video_urls'],
            $data['section_image_files'],
            $data['section_video_files'],
            $data['remove_section_images'],
            $data['remove_section_video']
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
            'existing_gallery_images' => 'nullable|array|max:40',
            'existing_gallery_images.*' => 'nullable|url|max:500',
            'gallery_image_files' => 'nullable|array|max:40',
            'gallery_image_files.*' => 'nullable|file|image|max:10240',
            'video_url' => 'nullable|url|max:500',
            'video_file' => 'nullable|file|mimetypes:video/mp4,video/webm,video/quicktime|max:102400',
            'remove_featured_image' => 'nullable|boolean',
            'remove_video' => 'nullable|boolean',
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
            'remove_section_images' => 'nullable|array|max:20',
            'remove_section_images.*' => 'nullable|array|max:10',
            'remove_section_images.*.*' => 'nullable|url|max:500',
            'remove_section_video' => 'nullable|array|max:20',
            'remove_section_video.*' => 'nullable|in:0,1',
            'read_time'     => 'nullable|integer|min:1',
        ]);

        $media = app(MediaService::class);
        $documentSections = [];
        $sourceDocumentMeta = [];

        if ($request->hasFile('document_file')) {
            $documentFile = $request->file('document_file');
            $parsed = app(DocumentImportService::class)->parse($documentFile);
            if (!$parsed) {
                throw ValidationException::withMessages([
                    'document_file' => 'Unable to parse this document. Use DOCX, PDF, TXT, or MD.',
                ]);
            }
            $data['title'] = $data['title'] ?: ($parsed['title'] ?? $article->title);
            $data['summary'] = $data['summary'] ?: ($parsed['summary'] ?? $article->summary);
            $data['content'] = $data['content'] ?: ($parsed['content'] ?? $article->content);
            $documentSections = $parsed['sections'] ?? [];

            $classification = app(ArticleCategoryClassifier::class)->classify(
                (string) ($data['title'] ?? $article->title),
                (string) ($data['summary'] ?? $article->summary),
                (string) ($data['content'] ?? $article->content)
            );
            $detectedSlug = (string) ($classification['slug'] ?? '');
            if ($detectedSlug !== '' && (float) ($classification['confidence'] ?? 0) >= 0.62) {
                $detectedCategoryId = Category::where('slug', $detectedSlug)->value('id');
                if ($detectedCategoryId) {
                    $data['category_id'] = (int) $detectedCategoryId;
                }
            }

            $sourceDocumentUrl = $media->uploadFile($documentFile, 'articles/documents');
            if ($sourceDocumentUrl) {
                $sourceDocumentMeta = [
                    'source_document_url' => $sourceDocumentUrl,
                    'source_document_name' => $documentFile->getClientOriginalName(),
                    'source_document_ext' => strtolower((string) $documentFile->getClientOriginalExtension()),
                    'source_document_uploaded_at' => now()->toIso8601String(),
                ];
            }
        }

        if (empty($data['title']) || empty($data['summary']) || empty($data['content'])) {
            throw ValidationException::withMessages([
                'document_file' => 'Title, summary, and content are required. Upload a richer document or fill missing fields.',
            ]);
        }

        $originalGallery = is_array($article->images) ? $article->images : [];

        if ($request->boolean('remove_featured_image') && $article->featured_image && $media->isOwnCdn($article->featured_image)) {
            $media->delete($article->featured_image);
            $data['featured_image'] = null;
        }

        if ($request->hasFile('featured_image_file')) {
            $uploaded = $media->uploadFile($request->file('featured_image_file'), 'articles');
            if ($uploaded) {
                $data['featured_image'] = $uploaded;
            }
        }

        if ($request->boolean('remove_video')) {
            if ($article->video_url && $media->isOwnCdn($article->video_url)) {
                $media->delete($article->video_url);
            }
            $data['video_url'] = null;
        }

        if ($request->hasFile('video_file')) {
            if ($article->video_url && $media->isOwnCdn($article->video_url)) {
                $media->delete($article->video_url);
            }
            $videoUploaded = $media->uploadFile($request->file('video_file'), 'articles/videos');
            if ($videoUploaded) $data['video_url'] = $videoUploaded;
        }

        $galleryUrls = $this->syncGalleryFromRequest($request, $media, 'articles', $originalGallery);
        $sections = $this->buildSectionsFromRequest($request, $media, !empty($documentSections) ? $documentSections : (array) $article->content_sections, 'articles');
        if (!empty($sections)) {
            $data['content_sections'] = $sections;
            if (empty($data['content'])) {
                $data['content'] = $this->sectionsToContent($sections);
            }
        }

        $data['images'] = array_values(array_filter($galleryUrls));
        if (!empty($sourceDocumentMeta)) {
            $data['meta'] = array_merge(is_array($article->meta) ? $article->meta : [], $sourceDocumentMeta);
        }
        if (empty($data['featured_image'])) {
            $data['featured_image'] = $this->resolveFeaturedImage($article->featured_image, (string) $data['title'], $data['featured_image'] ?? null, $data['images'], $sections, $media, 'articles/thumbnails');
        }

        unset(
            $data['featured_image_file'],
            $data['existing_gallery_images'],
            $data['gallery_image_files'],
            $data['video_file'],
            $data['remove_featured_image'],
            $data['remove_video'],
            $data['document_file'],
            $data['section_titles'],
            $data['section_bodies'],
            $data['section_video_urls'],
            $data['section_image_files'],
            $data['section_video_files'],
            $data['remove_section_images'],
            $data['remove_section_video']
        );

        if ($data['status'] === 'published' && !$article->published_at) {
            $data['published_at'] = now();
        }
        $article->update($data);
        return redirect()->route('admin.articles.index')->with('success', 'Article updated.');
    }

    private function buildSectionsFromRequest(Request $request, MediaService $media, array $seedSections = [], string $folderPrefix = 'articles'): array
    {
        $titles = (array) $request->input('section_titles', []);
        $bodies = (array) $request->input('section_bodies', []);
        $videoUrls = (array) $request->input('section_video_urls', []);
        $removeImageLists = (array) $request->input('remove_section_images', []);
        $removeVideos = (array) $request->input('remove_section_video', []);

        $max = max(count($titles), count($bodies), count($videoUrls), count($seedSections), count($removeImageLists), count($removeVideos));
        $sections = [];

        for ($i = 0; $i < $max; $i++) {
            $seed = $seedSections[$i] ?? [];
            $title = trim((string) ($titles[$i] ?? ($seed['title'] ?? '')));
            $body = trim((string) ($bodies[$i] ?? ($seed['body'] ?? '')));

            $images = [];
            if (!empty($seed['images']) && is_array($seed['images'])) {
                $images = array_values(array_filter($seed['images']));
            }

            $toRemove = array_values(array_filter((array) ($removeImageLists[$i] ?? [])));
            if (!empty($toRemove)) {
                foreach ($toRemove as $removeUrl) {
                    if ($media->isOwnCdn($removeUrl)) {
                        $media->delete($removeUrl);
                    }
                }
                $images = array_values(array_filter($images, fn ($url) => !in_array($url, $toRemove, true)));
            }

            if ($request->hasFile("section_image_files.$i")) {
                foreach ((array) $request->file("section_image_files.$i") as $sectionImage) {
                    if (!$sectionImage) {
                        continue;
                    }
                    $uploaded = $media->uploadFile($sectionImage, $folderPrefix . '/sections');
                    if ($uploaded) {
                        $images[] = $uploaded;
                    }
                }
            }
            $images = array_slice(array_values(array_filter($images)), 0, 5);

            $videoUrl = trim((string) ($videoUrls[$i] ?? ($seed['video_url'] ?? '')));
            if (!empty($removeVideos[$i]) && !empty($seed['video_url']) && $media->isOwnCdn((string) $seed['video_url'])) {
                $media->delete((string) $seed['video_url']);
                $videoUrl = '';
            }

            if ($request->hasFile("section_video_files.$i")) {
                if (!empty($seed['video_url']) && $media->isOwnCdn((string) $seed['video_url'])) {
                    $media->delete((string) $seed['video_url']);
                }
                $uploadedVideo = $media->uploadFile($request->file("section_video_files.$i"), $folderPrefix . '/sections/videos');
                if ($uploadedVideo) {
                    $videoUrl = $uploadedVideo;
                }
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

    private function syncGalleryFromRequest(Request $request, MediaService $media, string $folderPrefix, array $existing = []): array
    {
        $galleryStateSubmitted = $request->boolean('gallery_state_submitted') || $request->has('existing_gallery_images') || $request->hasFile('gallery_image_files');
        if (!$galleryStateSubmitted) {
            return array_values(array_filter($existing));
        }

        $galleryUrls = [];
        foreach ((array) $request->input('existing_gallery_images', []) as $url) {
            $url = trim((string) $url);
            if ($url !== '' && !in_array($url, $galleryUrls, true)) {
                $galleryUrls[] = $url;
            }
        }

        foreach (array_diff($existing, $galleryUrls) as $removedUrl) {
            if ($removedUrl && $media->isOwnCdn($removedUrl)) {
                $media->delete($removedUrl);
            }
        }

        if ($request->hasFile('gallery_image_files')) {
            foreach ((array) $request->file('gallery_image_files') as $galleryImage) {
                if (!$galleryImage) {
                    continue;
                }
                $galleryUploaded = $media->uploadFile($galleryImage, $folderPrefix . '/gallery');
                if ($galleryUploaded) {
                    $galleryUrls[] = $galleryUploaded;
                }
            }
        }

        return array_values(array_filter($galleryUrls));
    }

    private function resolveFeaturedImage(?string $currentImage, string $title, ?string $explicitImage, array $galleryUrls, array $sections, MediaService $media, string $thumbnailFolder): ?string
    {
        if (!empty($explicitImage)) {
            return $explicitImage;
        }

        $sectionImage = null;
        foreach ($sections as $section) {
            $candidate = $section['images'][0] ?? null;
            if (!empty($candidate)) {
                $sectionImage = $candidate;
                break;
            }
        }

        return $galleryUrls[0] ?? $sectionImage ?? $currentImage ?? $media->generateTitleThumbnail($title, $thumbnailFolder);
    }

    public function articlesDestroy(Article $article)
    {
        $this->purgeContentMedia(
            $article->featured_image,
            $article->video_url,
            is_array($article->images) ? $article->images : [],
            (array) $article->content_sections,
            app(MediaService::class)
        );
        $article->delete();
        return redirect()->route('admin.articles.index')->with('success', 'Article deleted.');
    }

    /* ─── STORIES ─── */
    public function storiesIndex(Request $request)
    {
        $stories = Story::with(['category', 'author'])
            ->when($request->q, fn ($q, $s) => $q->where('title', 'like', "%$s%"))
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->category, fn ($q, $c) => $q->where('category_id', $c))
            ->orderByDesc('created_at')
            ->paginate(20);

        $categories = Category::orderBy('name')->get();

        return view('admin.stories.index', compact('stories', 'categories'));
    }

    public function storiesCreate()
    {
        $categories = Category::orderBy('name')->get();
        $contributors = User::whereIn('role', ['admin', 'contributor'])->orderBy('name')->get();

        return view('admin.stories.edit', ['story' => new Story(), 'categories' => $categories, 'contributors' => $contributors]);
    }

    public function storiesStore(Request $request)
    {
        $data = $request->validate([
            'title'         => 'required_without:document_file|string|max:255',
            'slug'          => 'nullable|string|max:255|unique:stories,slug',
            'excerpt'       => 'required_without:document_file|string',
            'content'       => 'nullable|string',
            'category_id'   => 'required|exists:categories,id',
            'author_id'     => 'required|exists:users,id',
            'status'        => 'required|in:draft,review,published',
            'is_featured'   => 'nullable|boolean',
            'featured_image'=> 'nullable|url|max:500',
            'featured_image_file' => 'nullable|file|image|max:10240',
            'existing_gallery_images' => 'nullable|array|max:40',
            'existing_gallery_images.*' => 'nullable|url|max:500',
            'gallery_image_files' => 'nullable|array|max:40',
            'gallery_image_files.*' => 'nullable|file|image|max:10240',
            'video_url' => 'nullable|url|max:500',
            'video_file' => 'nullable|file|mimetypes:video/mp4,video/webm,video/quicktime|max:102400',
            'remove_featured_image' => 'nullable|boolean',
            'remove_video' => 'nullable|boolean',
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
            'remove_section_images' => 'nullable|array|max:20',
            'remove_section_images.*' => 'nullable|array|max:10',
            'remove_section_images.*.*' => 'nullable|url|max:500',
            'remove_section_video' => 'nullable|array|max:20',
            'remove_section_video.*' => 'nullable|in:0,1',
            'read_time' => 'nullable|integer|min:1',
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
            $data['excerpt'] = $data['excerpt'] ?: ($parsed['summary'] ?? null);
            $data['content'] = $data['content'] ?: ($parsed['content'] ?? null);
            $documentSections = $parsed['sections'] ?? [];
        }

        if (empty($data['title']) || empty($data['excerpt']) || empty($data['content'])) {
            throw ValidationException::withMessages([
                'document_file' => 'Title, excerpt, and content are required. Upload a richer document or fill missing fields.',
            ]);
        }

        $data['slug'] = $data['slug'] ?? Str::slug($data['title']);

        if ($request->hasFile('featured_image_file')) {
            $uploaded = $media->uploadFile($request->file('featured_image_file'), 'stories');
            if ($uploaded) {
                $data['featured_image'] = $uploaded;
            }
        }

        if ($request->hasFile('video_file')) {
            $videoUploaded = $media->uploadFile($request->file('video_file'), 'stories/videos');
            if ($videoUploaded) {
                $data['video_url'] = $videoUploaded;
            }
        }

        $galleryUrls = $this->syncGalleryFromRequest($request, $media, 'stories', []);

        $sections = $this->buildSectionsFromRequest($request, $media, $documentSections, 'stories');
        if (!empty($sections)) {
            $data['content_sections'] = $sections;
            if (empty($data['content'])) {
                $data['content'] = $this->sectionsToContent($sections);
            }
        }

        $data['images'] = $galleryUrls;
        $data['is_featured'] = $request->boolean('is_featured');

        if (empty($data['featured_image'])) {
            $data['featured_image'] = $this->resolveFeaturedImage(null, (string) $data['title'], null, $galleryUrls, $sections, $media, 'stories/thumbnails');
        }

        unset(
            $data['featured_image_file'],
            $data['existing_gallery_images'],
            $data['gallery_image_files'],
            $data['video_file'],
            $data['remove_featured_image'],
            $data['remove_video'],
            $data['document_file'],
            $data['section_titles'],
            $data['section_bodies'],
            $data['section_video_urls'],
            $data['section_image_files'],
            $data['section_video_files'],
            $data['remove_section_images'],
            $data['remove_section_video']
        );

        if ($data['status'] === 'published') {
            $data['published_at'] = now();
        }

        Story::create($data);

        return redirect()->route('admin.stories.index')->with('success', 'Story created.');
    }

    public function storiesEdit(Story $story)
    {
        $categories = Category::orderBy('name')->get();
        $contributors = User::whereIn('role', ['admin', 'contributor'])->orderBy('name')->get();

        return view('admin.stories.edit', compact('story', 'categories', 'contributors'));
    }

    public function storiesUpdate(Request $request, Story $story)
    {
        $data = $request->validate([
            'title'         => 'required_without:document_file|string|max:255',
            'excerpt'       => 'required_without:document_file|string',
            'content'       => 'nullable|string',
            'category_id'   => 'required|exists:categories,id',
            'author_id'     => 'required|exists:users,id',
            'status'        => 'required|in:draft,review,published',
            'is_featured'   => 'nullable|boolean',
            'featured_image'=> 'nullable|url|max:500',
            'featured_image_file' => 'nullable|file|image|max:10240',
            'existing_gallery_images' => 'nullable|array|max:40',
            'existing_gallery_images.*' => 'nullable|url|max:500',
            'gallery_image_files' => 'nullable|array|max:40',
            'gallery_image_files.*' => 'nullable|file|image|max:10240',
            'video_url' => 'nullable|url|max:500',
            'video_file' => 'nullable|file|mimetypes:video/mp4,video/webm,video/quicktime|max:102400',
            'remove_featured_image' => 'nullable|boolean',
            'remove_video' => 'nullable|boolean',
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
            'remove_section_images' => 'nullable|array|max:20',
            'remove_section_images.*' => 'nullable|array|max:10',
            'remove_section_images.*.*' => 'nullable|url|max:500',
            'remove_section_video' => 'nullable|array|max:20',
            'remove_section_video.*' => 'nullable|in:0,1',
            'read_time' => 'nullable|integer|min:1',
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
            $data['title'] = $data['title'] ?: ($parsed['title'] ?? $story->title);
            $data['excerpt'] = $data['excerpt'] ?: ($parsed['summary'] ?? $story->excerpt);
            $data['content'] = $data['content'] ?: ($parsed['content'] ?? $story->content);
            $documentSections = $parsed['sections'] ?? [];
        }

        if (empty($data['title']) || empty($data['excerpt']) || empty($data['content'])) {
            throw ValidationException::withMessages([
                'document_file' => 'Title, excerpt, and content are required. Upload a richer document or fill missing fields.',
            ]);
        }

        $originalGallery = is_array($story->images) ? $story->images : [];

        if ($request->boolean('remove_featured_image') && $story->featured_image && $media->isOwnCdn($story->featured_image)) {
            $media->delete($story->featured_image);
            $data['featured_image'] = null;
        }

        if ($request->hasFile('featured_image_file')) {
            $uploaded = $media->uploadFile($request->file('featured_image_file'), 'stories');
            if ($uploaded) {
                $data['featured_image'] = $uploaded;
            }
        }

        if ($request->boolean('remove_video')) {
            if ($story->video_url && $media->isOwnCdn($story->video_url)) {
                $media->delete($story->video_url);
            }
            $data['video_url'] = null;
        }

        if ($request->hasFile('video_file')) {
            if ($story->video_url && $media->isOwnCdn($story->video_url)) {
                $media->delete($story->video_url);
            }
            $videoUploaded = $media->uploadFile($request->file('video_file'), 'stories/videos');
            if ($videoUploaded) {
                $data['video_url'] = $videoUploaded;
            }
        }

        $galleryUrls = $this->syncGalleryFromRequest($request, $media, 'stories', $originalGallery);
        $sections = $this->buildSectionsFromRequest($request, $media, !empty($documentSections) ? $documentSections : (array) $story->content_sections, 'stories');
        if (!empty($sections)) {
            $data['content_sections'] = $sections;
            if (empty($data['content'])) {
                $data['content'] = $this->sectionsToContent($sections);
            }
        }

        $data['images'] = array_values(array_filter($galleryUrls));
        $data['is_featured'] = $request->boolean('is_featured');

        if (empty($data['featured_image'])) {
            $data['featured_image'] = $this->resolveFeaturedImage($story->featured_image, (string) $data['title'], $data['featured_image'] ?? null, $data['images'], $sections, $media, 'stories/thumbnails');
        }

        unset(
            $data['featured_image_file'],
            $data['existing_gallery_images'],
            $data['gallery_image_files'],
            $data['video_file'],
            $data['remove_featured_image'],
            $data['remove_video'],
            $data['document_file'],
            $data['section_titles'],
            $data['section_bodies'],
            $data['section_video_urls'],
            $data['section_image_files'],
            $data['section_video_files'],
            $data['remove_section_images'],
            $data['remove_section_video']
        );

        if ($data['status'] === 'published' && !$story->published_at) {
            $data['published_at'] = now();
        }

        $story->update($data);

        return redirect()->route('admin.stories.index')->with('success', 'Story updated.');
    }

    public function storiesDestroy(Story $story)
    {
        $this->purgeContentMedia(
            $story->featured_image,
            $story->video_url,
            is_array($story->images) ? $story->images : [],
            (array) $story->content_sections,
            app(MediaService::class)
        );
        $story->delete();
        return redirect()->route('admin.stories.index')->with('success', 'Story deleted.');
    }

    private function purgeContentMedia(?string $featuredImage, ?string $videoUrl, array $galleryUrls, array $sections, MediaService $media): void
    {
        $urls = [];

        foreach ([$featuredImage, $videoUrl] as $singleUrl) {
            if (!empty($singleUrl)) {
                $urls[] = $singleUrl;
            }
        }

        foreach ($galleryUrls as $galleryUrl) {
            if (!empty($galleryUrl)) {
                $urls[] = $galleryUrl;
            }
        }

        foreach ($sections as $section) {
            foreach ((array) ($section['images'] ?? []) as $sectionImage) {
                if (!empty($sectionImage)) {
                    $urls[] = $sectionImage;
                }
            }

            $sectionVideoUrl = $section['video_url'] ?? null;
            if (!empty($sectionVideoUrl)) {
                $urls[] = $sectionVideoUrl;
            }
        }

        foreach (array_unique($urls) as $url) {
            if ($media->isOwnCdn((string) $url)) {
                $media->delete((string) $url);
            }
        }
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

    /* ─── BULK IMPORT ─── */
    public function bulkImport()
    {
        $categories = Category::orderBy('name')->get();
        $contributors = User::whereIn('role', ['admin', 'contributor'])->orderBy('name')->get();
        return view('admin.articles.bulk-import', compact('categories', 'contributors'));
    }

    public function bulkImportStore(Request $request)
    {
        $request->validate([
            'documents'   => 'required|array|min:1|max:300',
            'documents.*' => 'required|file|mimes:doc,docx,pdf,txt,md|max:20480',
            'category_id' => 'required|exists:categories,id',
            'author_id'   => 'required|exists:users,id',
            'status'      => 'required|in:draft,review,published',
        ]);

        $importer = app(DocumentImportService::class);
        $classifier = app(ArticleCategoryClassifier::class);
        $media = app(MediaService::class);
        $chosenCategory = Category::find($request->category_id);
        $categoriesBySlug = Category::pluck('id', 'slug');
        $categoryNames = Category::pluck('name', 'id');
        $results  = [];
        $success  = 0;
        $failed   = 0;

        foreach ($request->file('documents') as $file) {
            $name = $file->getClientOriginalName();
            try {
                $parsed = $importer->parse($file);
                if (!$parsed || empty($parsed['title'])) {
                    $results[] = ['file' => $name, 'ok' => false, 'msg' => 'Could not parse — skipped.'];
                    $failed++;
                    continue;
                }

                $slug = \Illuminate\Support\Str::slug($parsed['title']);

                if (\App\Models\Article::where('slug', $slug)->exists()) {
                    $results[] = ['file' => $name, 'ok' => false, 'msg' => "Duplicate title '{$parsed['title']}' — skipped."];
                    $failed++;
                    continue;
                }

                $sections = $parsed['sections'] ?? [];
                $content  = $parsed['content'] ?? collect($sections)
                    ->map(fn ($s) => "## {$s['title']}\n\n{$s['body']}")
                    ->implode("\n\n");

                if (empty($content)) {
                    $results[] = ['file' => $name, 'ok' => false, 'msg' => 'No content extracted — skipped.'];
                    $failed++;
                    continue;
                }

                $wordCount = str_word_count(strip_tags($content));
                $readTime  = max(3, (int) round($wordCount / 200));

                $classification = $classifier->classify(
                    (string) ($parsed['title'] ?? ''),
                    (string) ($parsed['summary'] ?? ''),
                    (string) $content
                );

                $detectedSlug = (string) ($classification['slug'] ?? '');
                $detectedCategoryId = ($detectedSlug !== '' && $categoriesBySlug->has($detectedSlug))
                    ? (int) $categoriesBySlug->get($detectedSlug)
                    : null;
                $effectiveCategoryId = ($detectedCategoryId && (float) ($classification['confidence'] ?? 0) >= 0.56)
                    ? $detectedCategoryId
                    : (int) $request->category_id;

                $sourceDocumentUrl = $media->uploadFile($file, 'articles/documents');
                $meta = [];
                if ($sourceDocumentUrl) {
                    $meta = [
                        'source_document_url' => $sourceDocumentUrl,
                        'source_document_name' => $name,
                        'source_document_ext' => strtolower((string) $file->getClientOriginalExtension()),
                        'source_document_uploaded_at' => now()->toIso8601String(),
                    ];
                }

                \App\Models\Article::create([
                    'title'            => $parsed['title'],
                    'slug'             => $slug,
                    'summary'          => $parsed['summary'] ?? mb_substr(strip_tags($content), 0, 200),
                    'content'          => $content,
                    'content_sections' => !empty($sections) ? $sections : null,
                    'category_id'      => $effectiveCategoryId,
                    'author_id'        => $request->author_id,
                    'status'           => $request->status,
                    'read_time'        => $readTime,
                    'views'            => 0,
                    'quick_facts'      => !empty($parsed['quickFacts']) ? $parsed['quickFacts'] : null,
                    'images'           => [],
                    'meta'             => !empty($meta) ? $meta : null,
                    'published_at'     => $request->status === 'published' ? now() : null,
                ]);

                $categoryName = (string) ($categoryNames[$effectiveCategoryId] ?? ($chosenCategory->name ?? 'Uncategorized'));
                $results[] = ['file' => $name, 'ok' => true, 'msg' => "Imported as \"{$parsed['title']}\" in {$categoryName}."];
                $success++;

            } catch (\Throwable $e) {
                $results[] = ['file' => $name, 'ok' => false, 'msg' => 'Error: ' . $e->getMessage()];
                $failed++;
            }
        }

        return back()
            ->with('bulk_results', $results)
            ->with('bulk_success', $success)
            ->with('bulk_failed', $failed);
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

        $readerInsights = ContentView::query()
            ->selectRaw('users.id as user_id, users.name as name, users.email as email, COUNT(content_views.id) as reads, AVG(content_views.dwell_seconds) as avg_dwell, AVG(content_views.completion_pct) as avg_completion')
            ->join('users', 'users.id', '=', 'content_views.user_id')
            ->whereNotNull('content_views.user_id')
            ->groupBy('users.id', 'users.name', 'users.email')
            ->orderByDesc('reads')
            ->limit(10)
            ->get();

        $behaviorSummary = [
            'avg_dwell' => (int) round((float) ContentView::avg('dwell_seconds')),
            'avg_completion' => (int) round((float) ContentView::avg('completion_pct')),
            'returning_readers' => ContentView::where('is_returning', true)->distinct('session_id')->count('session_id'),
            'unique_readers' => ContentView::distinct('session_id')->count('session_id'),
        ];

        return view('admin.analytics', compact(
            'totalViews', 'topArticles', 'topStories', 'usersByRole', 'articlesByCategory', 'recentUsers', 'readerInsights', 'behaviorSummary'
        ));
    }
}
