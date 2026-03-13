<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Category;
use App\Models\ContributorProfile;
use App\Services\DocumentImportService;
use App\Services\MediaService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ContributorController extends Controller
{
    public function dashboard()
    {
        $user = auth()->user();

        $myArticles        = Article::where('author_id', $user->id)->orderByDesc('created_at')->take(5)->get();
        $totalArticles     = Article::where('author_id', $user->id)->count();
        $publishedArticles = Article::where('author_id', $user->id)->where('status', 'published')->count();
        $draftArticles     = Article::where('author_id', $user->id)->where('status', 'draft')->count();
        $totalViews        = Article::where('author_id', $user->id)->sum('views');
        $profile           = ContributorProfile::firstOrCreate(['user_id' => $user->id], [
            'expertise' => 'General', 'bio' => '', 'reputation' => 0,
        ]);

        return view('contributor.dashboard', compact(
            'myArticles', 'totalArticles', 'publishedArticles', 'draftArticles', 'totalViews', 'profile'
        ));
    }

    public function articles(Request $request)
    {
        $user     = auth()->user();
        $articles = Article::where('author_id', $user->id)
            ->with('category')
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->orderByDesc('created_at')
            ->paginate(20);
        return view('contributor.articles', compact('articles'));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();
        return view('contributor.edit', ['article' => new Article(), 'categories' => $categories]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'       => 'required_without:document_file|string|max:255',
            'summary'     => 'required_without:document_file|string',
            'content'     => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'status'      => 'required|in:draft,review',
            'featured_image' => 'nullable|url|max:500',
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
            'read_time'   => 'nullable|integer|min:1',
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

        $data['author_id'] = auth()->id();
        $data['slug']      = Str::slug($data['title']) . '-' . Str::random(5);
        Article::create($data);
        return redirect()->route('contributor.articles')->with('success', 'Article submitted for review.');
    }

    public function edit(Article $article)
    {
        $this->authorizeArticle($article);
        $categories = Category::orderBy('name')->get();
        return view('contributor.edit', compact('article', 'categories'));
    }

    public function update(Request $request, Article $article)
    {
        $this->authorizeArticle($article);
        $data = $request->validate([
            'title'       => 'required_without:document_file|string|max:255',
            'summary'     => 'required_without:document_file|string',
            'content'     => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'status'      => 'required|in:draft,review',
            'featured_image' => 'nullable|url|max:500',
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
            'read_time'   => 'nullable|integer|min:1',
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

        $article->update($data);
        return redirect()->route('contributor.articles')->with('success', 'Article updated.');
    }

    public function destroy(Article $article)
    {
        $this->authorizeArticle($article);
        $article->delete();
        return redirect()->route('contributor.articles')->with('success', 'Article deleted.');
    }

    private function authorizeArticle(Article $article): void
    {
        if ($article->author_id !== auth()->id() && auth()->user()->role !== 'admin') {
            abort(403);
        }
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
}
