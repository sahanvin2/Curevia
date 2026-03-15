<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Category;
use App\Models\ContentView;
use App\Models\ContributorProfile;
use App\Models\Story;
use App\Services\ArticleCategoryClassifier;
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

        $myArticles = Article::where('author_id', $user->id)->orderByDesc('created_at')->take(5)->get();
        $myStories = Story::where('author_id', $user->id)->orderByDesc('created_at')->take(5)->get();
        $allArticleIds = Article::where('author_id', $user->id)->pluck('id');
        $allStoryIds = Story::where('author_id', $user->id)->pluck('id');
        $totalArticles = Article::where('author_id', $user->id)->count();
        $totalStories = Story::where('author_id', $user->id)->count();
        $publishedArticles = Article::where('author_id', $user->id)->where('status', 'published')->count();
        $publishedStories = Story::where('author_id', $user->id)->where('status', 'published')->count();
        $draftArticles = Article::where('author_id', $user->id)->where('status', 'draft')->count();
        $draftStories = Story::where('author_id', $user->id)->where('status', 'draft')->count();

        $totalViews = ContentView::query()
            ->where(function ($q) use ($allArticleIds, $allStoryIds) {
                if ($allArticleIds->isNotEmpty()) {
                    $q->orWhere(function ($inner) use ($allArticleIds) {
                        $inner->where('content_type', 'article')->whereIn('content_id', $allArticleIds);
                    });
                }

                if ($allStoryIds->isNotEmpty()) {
                    $q->orWhere(function ($inner) use ($allStoryIds) {
                        $inner->where('content_type', 'story')->whereIn('content_id', $allStoryIds);
                    });
                }
            })
            ->count();

        $totalUniqueReaders = ContentView::query()
            ->where(function ($q) use ($allArticleIds, $allStoryIds) {
                if ($allArticleIds->isNotEmpty()) {
                    $q->orWhere(function ($inner) use ($allArticleIds) {
                        $inner->where('content_type', 'article')->whereIn('content_id', $allArticleIds);
                    });
                }

                if ($allStoryIds->isNotEmpty()) {
                    $q->orWhere(function ($inner) use ($allStoryIds) {
                        $inner->where('content_type', 'story')->whereIn('content_id', $allStoryIds);
                    });
                }
            })
            ->distinct('session_id')
            ->count('session_id');

        $articleReaderStats = ContentView::query()
            ->selectRaw('content_id, COUNT(*) as reads, COUNT(DISTINCT session_id) as unique_readers')
            ->where('content_type', 'article')
            ->whereIn('content_id', $myArticles->pluck('id'))
            ->groupBy('content_id')
            ->get()
            ->keyBy('content_id');

        $storyReaderStats = ContentView::query()
            ->selectRaw('content_id, COUNT(*) as reads, COUNT(DISTINCT session_id) as unique_readers')
            ->where('content_type', 'story')
            ->whereIn('content_id', $myStories->pluck('id'))
            ->groupBy('content_id')
            ->get()
            ->keyBy('content_id');

        $profile = ContributorProfile::firstOrCreate(['user_id' => $user->id], [
            'expertise' => 'General', 'bio' => '', 'reputation' => 0,
        ]);

        return view('contributor.dashboard', compact(
            'myArticles',
            'myStories',
            'totalArticles',
            'totalStories',
            'publishedArticles',
            'publishedStories',
            'draftArticles',
            'draftStories',
            'totalViews',
            'totalUniqueReaders',
            'articleReaderStats',
            'storyReaderStats',
            'profile'
        ));
    }

    public function articles(Request $request)
    {
        $user = auth()->user();

        $baseArticles = Article::where('author_id', $user->id);

        $categories = Category::whereIn(
            'id',
            (clone $baseArticles)->select('category_id')->distinct()
        )
            ->orderBy('name')
            ->get();

        $categoryCounts = (clone $baseArticles)
            ->selectRaw('category_id, COUNT(*) as total')
            ->groupBy('category_id')
            ->pluck('total', 'category_id');

        $articles = (clone $baseArticles)
            ->with('category')
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->category, fn ($q, $c) => $q->where('category_id', (int) $c))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        $articleReaderStats = ContentView::query()
            ->selectRaw('content_id, COUNT(*) as reads, COUNT(DISTINCT session_id) as unique_readers')
            ->where('content_type', 'article')
            ->whereIn('content_id', $articles->pluck('id'))
            ->groupBy('content_id')
            ->get()
            ->keyBy('content_id');

        return view('contributor.articles', compact('articles', 'articleReaderStats', 'categories', 'categoryCounts'));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();
        return view('contributor.edit', ['article' => new Article(), 'categories' => $categories]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required_without:document_file|string|max:255',
            'summary' => 'required_without:document_file|string',
            'content' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'status' => 'required|in:draft,review',
            'featured_image' => 'nullable|url|max:500',
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

        if ($request->hasFile('featured_image_file')) {
            $uploaded = $media->uploadFile($request->file('featured_image_file'), 'articles');
            if ($uploaded) {
                $data['featured_image'] = $uploaded;
            }
        }

        if ($request->hasFile('video_file')) {
            $videoUploaded = $media->uploadFile($request->file('video_file'), 'articles/videos');
            if ($videoUploaded) {
                $data['video_url'] = $videoUploaded;
            }
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

        $data['author_id'] = auth()->id();
        $data['slug'] = Str::slug($data['title']) . '-' . Str::random(5);
        Article::create($data);

        return redirect()->route('contributor.articles')->with('success', 'Article submitted for review.');
    }

    public function edit(Article $article)
    {
        $this->authorizeArticle($article);
        if (empty($article->content) && !empty($article->content_sections) && is_array($article->content_sections)) {
            $article->content = $this->sectionsToContent((array) $article->content_sections);
        }

        if (empty($article->content_sections) || !is_array($article->content_sections)) {
            $article->content_sections = $this->extractSectionsFromContent((string) $article->content);
        }

        $categories = Category::orderBy('name')->get();
        return view('contributor.edit', compact('article', 'categories'));
    }

    public function update(Request $request, Article $article)
    {
        $this->authorizeArticle($article);
        $data = $request->validate([
            'title' => 'required_without:document_file|string|max:255',
            'summary' => 'required_without:document_file|string',
            'content' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'status' => 'required|in:draft,review',
            'featured_image' => 'nullable|url|max:500',
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
        $sourceDocumentMeta = [];
        $originalGallery = is_array($article->images) ? $article->images : [];

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
            if ($videoUploaded) {
                $data['video_url'] = $videoUploaded;
            }
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

        $article->update($data);
        return redirect()->route('contributor.articles')->with('success', 'Article updated.');
    }

    public function destroy(Article $article)
    {
        $this->authorizeArticle($article);
        $this->purgeContentMedia(
            $article->featured_image,
            $article->video_url,
            is_array($article->images) ? $article->images : [],
            (array) $article->content_sections,
            app(MediaService::class)
        );
        $article->delete();
        return redirect()->route('contributor.articles')->with('success', 'Article deleted.');
    }

    public function stories(Request $request)
    {
        $user = auth()->user();
        $stories = Story::where('author_id', $user->id)
            ->with('category')
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('contributor.stories', compact('stories'));
    }

    public function storiesCreate()
    {
        $categories = Category::orderBy('name')->get();
        return view('contributor.story-edit', ['story' => new Story(), 'categories' => $categories]);
    }

    public function storiesStore(Request $request)
    {
        $data = $request->validate([
            'title' => 'required_without:document_file|string|max:255',
            'excerpt' => 'required_without:document_file|string',
            'content' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'status' => 'required|in:draft,review',
            'is_featured' => 'nullable|boolean',
            'featured_image' => 'nullable|url|max:500',
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

        $data['slug'] = Str::slug($data['title']) . '-' . Str::random(5);

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
        $data['author_id'] = auth()->id();
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

        Story::create($data);

        return redirect()->route('contributor.stories')->with('success', 'Story submitted for review.');
    }

    public function storiesEdit(Story $story)
    {
        $this->authorizeStory($story);
        if (empty($story->content) && !empty($story->content_sections) && is_array($story->content_sections)) {
            $story->content = $this->sectionsToContent((array) $story->content_sections);
        }

        if (empty($story->content_sections) || !is_array($story->content_sections)) {
            $story->content_sections = $this->extractSectionsFromContent((string) $story->content);
        }

        $categories = Category::orderBy('name')->get();
        return view('contributor.story-edit', compact('story', 'categories'));
    }

    public function storiesUpdate(Request $request, Story $story)
    {
        $this->authorizeStory($story);
        $data = $request->validate([
            'title' => 'required_without:document_file|string|max:255',
            'excerpt' => 'required_without:document_file|string',
            'content' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'status' => 'required|in:draft,review',
            'is_featured' => 'nullable|boolean',
            'featured_image' => 'nullable|url|max:500',
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
        $originalGallery = is_array($story->images) ? $story->images : [];

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

        $data['images'] = $galleryUrls;
        $data['is_featured'] = $request->boolean('is_featured');
        if (empty($data['featured_image'])) {
            $data['featured_image'] = $this->resolveFeaturedImage($story->featured_image, (string) $data['title'], $data['featured_image'] ?? null, $galleryUrls, $sections, $media, 'stories/thumbnails');
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

        $story->update($data);

        return redirect()->route('contributor.stories')->with('success', 'Story updated.');
    }

    public function storiesDestroy(Story $story)
    {
        $this->authorizeStory($story);
        $this->purgeContentMedia(
            $story->featured_image,
            $story->video_url,
            is_array($story->images) ? $story->images : [],
            (array) $story->content_sections,
            app(MediaService::class)
        );
        $story->delete();
        return redirect()->route('contributor.stories')->with('success', 'Story deleted.');
    }

    /* ─── BULK IMPORT ─── */
    public function bulkImport()
    {
        $categories = Category::orderBy('name')->get();
        return view('contributor.bulk-import', compact('categories'));
    }

    public function bulkImportStore(Request $request)
    {
        $request->validate([
            'documents'   => 'required|array|min:1|max:300',
            'documents.*' => 'required|file|mimes:doc,docx,pdf,txt,md|max:20480',
            'category_id' => 'required|exists:categories,id',
            'status'      => 'required|in:draft,review',
        ]);

        $importer = app(DocumentImportService::class);
        $classifier = app(ArticleCategoryClassifier::class);
        $media = app(MediaService::class);
        $categoriesBySlug = Category::pluck('id', 'slug');
        $categoryNames = Category::pluck('name', 'id');
        $results  = [];
        $success  = 0;
        $failed   = 0;
        $authorId = auth()->id();

        foreach ($request->file('documents') as $file) {
            $name = $file->getClientOriginalName();
            try {
                $parsed = $importer->parse($file);
                if (!$parsed || empty($parsed['title'])) {
                    $results[] = ['file' => $name, 'ok' => false, 'msg' => 'Could not parse — skipped.'];
                    $failed++;
                    continue;
                }

                $slug = Str::slug($parsed['title']) . '-' . Str::random(4);

                if (Article::whereRaw('LOWER(title) = ?', [strtolower($parsed['title'])])->exists()) {
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

                Article::create([
                    'title'            => $parsed['title'],
                    'slug'             => $slug,
                    'summary'          => $parsed['summary'] ?? mb_substr(strip_tags($content), 0, 200),
                    'content'          => $content,
                    'content_sections' => !empty($sections) ? $sections : null,
                    'category_id'      => $effectiveCategoryId,
                    'author_id'        => $authorId,
                    'status'           => $request->status,
                    'read_time'        => $readTime,
                    'views'            => 0,
                    'quick_facts'      => !empty($parsed['quickFacts']) ? $parsed['quickFacts'] : null,
                    'images'           => [],
                    'meta'             => !empty($meta) ? $meta : null,
                ]);

                $categoryName = (string) ($categoryNames[$effectiveCategoryId] ?? 'Uncategorized');
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

    private function authorizeArticle(Article $article): void
    {
        if ((int) $article->author_id !== (int) auth()->id() && auth()->user()->role !== 'admin') {
            abort(403);
        }
    }

    private function authorizeStory(Story $story): void
    {
        if ((int) $story->author_id !== (int) auth()->id() && auth()->user()->role !== 'admin') {
            abort(403);
        }
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

    private function extractSectionsFromContent(string $content): array
    {
        $content = trim($content);
        if ($content === '') {
            return [];
        }

        preg_match_all('/(?:^|\R)##\s+(.+?)\R([\s\S]*?)(?=(?:\R##\s+)|\z)/u', $content, $matches, PREG_SET_ORDER);

        if (empty($matches)) {
            return [[
                'title' => 'Overview',
                'body' => $content,
                'images' => [],
                'video_url' => null,
            ]];
        }

        $sections = [];
        foreach ($matches as $row) {
            $title = trim((string) ($row[1] ?? ''));
            $body = trim((string) ($row[2] ?? ''));

            if ($title === '' && $body === '') {
                continue;
            }

            $sections[] = [
                'title' => $title !== '' ? $title : 'Section',
                'body' => $body,
                'images' => [],
                'video_url' => null,
            ];
        }

        return $sections;
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
}
