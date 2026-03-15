<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class EncyclopediaController extends Controller
{
    /**
     * @var array<string, mixed>|null
     */
    private static ?array $topicManifestCache = null;

    private function applyStableOrdering(Builder $query): Builder
    {
        return $query
            ->orderByDesc('views')
            ->orderByDesc('published_at')
            ->orderByDesc('id');
    }

    public function index(Request $request)
    {
        $query = Article::with('category')->where('status', 'published');

        if ($request->filled('q')) {
            $search = $request->input('q');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('summary', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category')) {
            $categorySlug = (string) $request->input('category');
            $category = Category::where('slug', $categorySlug)->first();
            if ($category) {
                $query->where('category_id', $category->id);
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        $articles = $this->applyStableOrdering($query)
            ->paginate(6)
            ->withQueryString();
        $this->assignTopicImagesForListing($articles->getCollection());
        $categories = Category::orderBy('sort_order')->get();

        if ($request->ajax()) {
            $html = '';
            foreach ($articles as $a) {
                $html .= view('encyclopedia._card', compact('a'))->render();
            }
            return response()->json(['html' => $html, 'next' => $articles->nextPageUrl()]);
        }

        return view('encyclopedia.index', compact('articles', 'categories'));
    }

    public function show(string $slug)
    {
        $article = Article::with(['category', 'author'])->where('slug', $slug)->where('status', 'published')->firstOrFail();
        $this->assignTopicImagesForListing(collect([$article]));
        $article->setAttribute('topic_gallery', $this->topicGalleryForArticle($article, 3));
        $contentSections = $this->buildDisplaySections($article);
        $comments = $article->comments()
            ->whereNull('parent_id')
            ->with(['user', 'replies.user'])
            ->latest()
            ->get();

        $related = Cache::remember(
            "article:{$article->id}:related.v1",
            now()->addMinutes(10),
            function () use ($article) {
                return Article::with('category')
                    ->where('category_id', $article->category_id)
                    ->where('id', '!=', $article->id)
                    ->where('status', 'published')
                    ->orderByDesc('views')
                    ->orderByDesc('published_at')
                    ->orderByDesc('id')
                    ->take(4)
                    ->get();
            }
        );
        $this->assignTopicImagesForListing($related);

        // Map article categories to relevant product categories
        $categoryProductMap = [
            'Space'        => ['Space', 'Astronomy', 'Books'],
            'Earth'        => ['Nature', 'Books'],
            'Science'      => ['Science', 'Books'],
            'History'      => ['History', 'Books'],
            'Animals'      => ['Nature', 'Books'],
            'Human Body'   => ['Science', 'Books'],
            'Countries'    => ['History', 'Books'],
            'Nature'       => ['Nature', 'Books'],
            'Mythology'    => ['History', 'Books'],
            'Zodiac'       => ['Space', 'Astronomy', 'Books'],
            'Civilizations'=> ['History', 'Books'],
            'Technology'   => ['Technology', 'Science', 'Books'],
        ];
        $articleCategoryName = $article->category->name ?? 'Science';
        $productCategories   = $categoryProductMap[$articleCategoryName] ?? ['Books'];

        $relatedProducts = Cache::remember(
            'article-category-products:' . md5(implode('|', $productCategories)) . '.v1',
            now()->addMinutes(10),
            function () use ($productCategories) {
                return Product::whereIn('category', $productCategories)
                    ->where('is_active', true)
                    ->orderByDesc('reviews_count')
                    ->take(4)
                    ->get();
            }
        );

        if ($relatedProducts->count() < 2) {
            $relatedProducts = Cache::remember(
                'article-products:fallback.v1',
                now()->addMinutes(10),
                function () {
                    return Product::where('is_active', true)
                        ->orderByDesc('rating')
                        ->take(4)
                        ->get();
                }
            );
        }

        return view('encyclopedia.show', compact('article', 'related', 'relatedProducts', 'contentSections', 'comments'));
    }

    private function assignTopicImagesForListing(Collection $articles): void
    {
        if ($articles->isEmpty()) {
            return;
        }

        foreach ($articles as $article) {
            if (!$article instanceof Article) {
                continue;
            }

            $resolved = $this->chooseTopicImageForArticle($article);
            if ($resolved !== null && $resolved !== '') {
                $article->setAttribute('featured_image', $resolved);
            }
        }
    }

    private function chooseTopicImageForArticle(Article $article): ?string
    {
        $best = $this->rankTopicImagesForArticle($article, 1);
        return $best[0] ?? null;
    }

    /**
     * @return array<int, string>
     */
    private function topicGalleryForArticle(Article $article, int $limit = 3): array
    {
        return $this->rankTopicImagesForArticle($article, max(1, $limit));
    }

    /**
     * @return array<int, string>
     */
    private function rankTopicImagesForArticle(Article $article, int $limit = 3): array
    {
        $pool = $this->topicImagePoolForArticle($article);
        if (empty($pool)) {
            return [];
        }

        $slugTokens = $this->tokenizeForImageMatching((string) ($article->slug ?? ''));
        $titleTokens = $this->tokenizeForImageMatching((string) ($article->title ?? ''));
        $docNameTokens = $this->tokenizeForImageMatching($this->sourceDocumentNameFromMeta($article));
        $needles = array_values(array_unique(array_merge($slugTokens, $titleTokens, $docNameTokens)));
        $docNameSlug = Str::slug($this->sourceDocumentNameFromMeta($article), '-');

        $scored = [];
        foreach ($pool as $url) {
            $key = $this->imageKeyFromUrl($url);
            $score = $this->scoreImageKeyAgainstArticle(
                $key,
                $needles,
                (string) ($article->slug ?? ''),
                (string) ($article->title ?? ''),
                $docNameSlug
            );
            $scored[] = ['url' => $url, 'score' => $score, 'key' => $key];
        }

        usort($scored, static function (array $a, array $b): int {
            $scoreCmp = $b['score'] <=> $a['score'];
            if ($scoreCmp !== 0) {
                return $scoreCmp;
            }

            return strcmp((string) $a['key'], (string) $b['key']);
        });

        $urls = array_map(static fn (array $row) => (string) $row['url'], $scored);
        return array_slice($urls, 0, max(1, $limit));
    }

    /**
     * @param array<int, string> $needles
     */
    private function scoreImageKeyAgainstArticle(
        string $imageKey,
        array $needles,
        string $articleSlug,
        string $articleTitle,
        string $sourceDocSlug
    ): int
    {
        $score = 0;
        $normalizedSlug = Str::slug($articleSlug, '-');
        $titleSlug = Str::slug($articleTitle, '-');

        if ($sourceDocSlug !== '' && $imageKey === $sourceDocSlug) {
            $score += 2000;
        }

        if ($sourceDocSlug !== '' && str_contains($imageKey, $sourceDocSlug)) {
            $score += 1300;
        }

        if ($titleSlug !== '' && $imageKey === $titleSlug) {
            $score += 1200;
        }

        if ($titleSlug !== '' && str_contains($imageKey, $titleSlug)) {
            $score += 850;
        }

        if ($normalizedSlug !== '' && $imageKey === $normalizedSlug) {
            $score += 1000;
        }

        if ($normalizedSlug !== '' && str_contains($imageKey, $normalizedSlug)) {
            $score += 700;
        }

        foreach ($needles as $token) {
            if ($token === '') {
                continue;
            }

            if ($imageKey === $token) {
                $score += 220;
                continue;
            }

            if (str_contains($imageKey, $token) || str_contains($token, $imageKey)) {
                $score += 90;
            }
        }

        if ($score === 0) {
            $score += $this->fuzzySimilarityScore($imageKey, $normalizedSlug);
            $score += (int) floor($this->fuzzySimilarityScore($imageKey, $titleSlug) * 0.7);
        }

        return $score;
    }

    private function fuzzySimilarityScore(string $left, string $right): int
    {
        $left = trim($left);
        $right = trim($right);

        if ($left === '' || $right === '') {
            return 0;
        }

        similar_text($left, $right, $percent);
        return (int) round($percent);
    }

    /**
     * @return array<int, string>
     */
    private function tokenizeForImageMatching(string $value): array
    {
        $value = Str::slug($value, '-');
        if ($value === '') {
            return [];
        }

        $parts = array_values(array_filter(explode('-', $value), static fn ($part) => strlen($part) >= 3));
        return $parts;
    }

    private function imageKeyFromUrl(string $url): string
    {
        $path = parse_url($url, PHP_URL_PATH);
        $filename = pathinfo((string) $path, PATHINFO_FILENAME);
        $filename = Str::slug((string) $filename, '-');

        // Remove trailing hash segments and numbered suffix variants generated during import.
        $filename = (string) preg_replace('/-(?:\d+-)?[a-f0-9]{8,}$/i', '', $filename);
        $filename = (string) preg_replace('/-\d+$/', '', $filename);

        return trim($filename, '-');
    }

    private function sourceDocumentNameFromMeta(Article $article): string
    {
        $meta = is_array($article->meta ?? null) ? $article->meta : [];
        $name = trim((string) ($meta['source_document_name'] ?? ''));
        if ($name === '') {
            return '';
        }

        return (string) preg_replace('/\.[a-z0-9]{2,5}$/i', '', $name);
    }

    /**
     * @return array<int, string>
     */
    private function topicImagePoolForArticle(Article $article): array
    {
        $manifest = $this->topicImageManifest();
        $topics = is_array($manifest['topics'] ?? null) ? $manifest['topics'] : [];
        if (empty($topics)) {
            return [];
        }

        $categorySlug = trim((string) ($article->category->slug ?? ''));
        $topicKey = $this->resolveTopicKeyForCategory($categorySlug, array_keys($topics));
        $pool = is_array($topics[$topicKey] ?? null) ? $topics[$topicKey] : [];

        if (empty($pool)) {
            $pool = is_array($manifest['all'] ?? null) ? $manifest['all'] : [];
        }

        return array_values(array_filter(array_map(static fn ($u) => trim((string) $u), $pool)));
    }

    /**
     * @param array<int, string> $availableTopicKeys
     */
    private function resolveTopicKeyForCategory(string $categorySlug, array $availableTopicKeys): string
    {
        $normalized = Str::slug($categorySlug, '-');
        if ($normalized === '') {
            return (string) ($availableTopicKeys[0] ?? '');
        }

        if (in_array($normalized, $availableTopicKeys, true)) {
            return $normalized;
        }

        $aliases = [
            'technology' => ['advanced-technology'],
            'science' => ['science-advanced-research'],
            'earth' => ['earth-environmental-systems'],
            'zodiac' => ['modern-zodiac-astrology'],
            'civilizations' => ['civilizations-cultural-systems'],
        ];

        foreach ($aliases[$normalized] ?? [] as $alias) {
            if (in_array($alias, $availableTopicKeys, true)) {
                return $alias;
            }
        }

        foreach ($availableTopicKeys as $key) {
            if (str_contains($key, $normalized) || str_contains($normalized, $key)) {
                return $key;
            }
        }

        return $normalized;
    }

    /**
     * @return array<string, mixed>
     */
    private function topicImageManifest(): array
    {
        if (self::$topicManifestCache !== null) {
            return self::$topicManifestCache;
        }

        $path = storage_path('app/topic-images.json');
        if (!is_file($path)) {
            self::$topicManifestCache = [];
            return self::$topicManifestCache;
        }

        $raw = file_get_contents($path);
        if ($raw === false || trim($raw) === '') {
            self::$topicManifestCache = [];
            return self::$topicManifestCache;
        }

        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            self::$topicManifestCache = [];
            return self::$topicManifestCache;
        }

        self::$topicManifestCache = $decoded;
        return self::$topicManifestCache;
    }

    private function buildDisplaySections(Article $article): array
    {
        $sections = is_array($article->content_sections) ? $article->content_sections : [];
        if (!empty($sections)) {
            return array_values($sections);
        }

        $content = trim((string) $article->content);
        if ($content === '') {
            return [];
        }

        $chunks = preg_split('/^##\s+/m', $content);
        if (!is_array($chunks) || count($chunks) <= 1) {
            return [[
                'title' => 'Overview',
                'body' => $content,
                'images' => [],
                'video_url' => null,
            ]];
        }

        $result = [];
        $intro = trim((string) ($chunks[0] ?? ''));
        if ($intro !== '') {
            $result[] = [
                'title' => 'Overview',
                'body' => $intro,
                'images' => [],
                'video_url' => null,
            ];
        }

        foreach (array_slice($chunks, 1) as $chunk) {
            $chunk = ltrim((string) $chunk);
            if ($chunk === '') {
                continue;
            }

            [$title, $body] = array_pad(preg_split('/\R/u', $chunk, 2) ?: [], 2, '');
            $title = trim((string) $title);
            $body = trim((string) $body);

            $result[] = [
                'title' => $title !== '' ? $title : 'Section',
                'body' => $body,
                'images' => [],
                'video_url' => null,
            ];
        }

        return $result;
    }

    public function download(Request $request, string $slug)
    {
        $article = Article::with(['category', 'author'])
            ->where('slug', $slug)
            ->where('status', 'published')
            ->firstOrFail();

        $filename = Str::slug($article->title ?: 'article') . '.doc';
        $summary = trim((string) $article->summary);
        $sections = $this->buildDisplaySections($article);

        $payload = $this->buildDocPayload($article->title, $summary, $sections, (string) $article->content);

        return response()->streamDownload(function () use ($payload) {
            echo $payload;
        }, $filename, [
            'Content-Type' => 'application/msword; charset=UTF-8',
        ]);
    }

    private function buildDocPayload(string $title, string $summary, array $sections, string $fallbackContent): string
    {
        $doc = '<!DOCTYPE html><html><head><meta charset="UTF-8"><style>body{font-family:Georgia,serif;line-height:1.6;color:#111;margin:28px;}h1{font-size:28px;margin:0 0 12px;}h2{font-size:19px;margin:22px 0 10px;}p{margin:0 0 12px;}hr{border:none;border-top:1px solid #ddd;margin:18px 0;}</style></head><body>';
        $doc .= '<h1>' . e($title) . '</h1>';

        if ($summary !== '') {
            $doc .= '<p><strong>Overview:</strong> ' . nl2br(e($summary)) . '</p><hr>';
        }

        if (!empty($sections)) {
            foreach ($sections as $section) {
                $sectionTitle = trim((string) ($section['title'] ?? 'Section'));
                $sectionBody = trim((string) ($section['body'] ?? ''));
                $doc .= '<h2>' . e($sectionTitle !== '' ? $sectionTitle : 'Section') . '</h2>';
                if ($sectionBody !== '') {
                    $doc .= '<p>' . nl2br(e($sectionBody)) . '</p>';
                }
            }
        } else {
            $doc .= '<p>' . nl2br(e(trim($fallbackContent))) . '</p>';
        }

        $doc .= '</body></html>';

        return $doc;
    }
}
