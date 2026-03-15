<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Models\Category;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ImportYoutubeEmbedsCommand extends Command
{
    protected $signature = 'media:import-topic-videos
                            {--file=Assests/youtube links/youtube_embed_site.html : Source HTML file containing topic cards}
                            {--categories=all : Comma-separated category slugs to import, or "all"}
                            {--topics= : Alias for --categories}
                            {--dry-run : Preview matches without writing to database}
                            {--force : Overwrite existing video URLs}
                            {--fill-category-default : Fill unmatched topics with category-level fallback video}';

    protected $description = 'Parse topic video links from HTML and assign YouTube embed URLs to matching articles';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $force = (bool) $this->option('force');
        $fillCategoryDefault = (bool) $this->option('fill-category-default');
        $filePath = base_path((string) $this->option('file'));

        if (!is_file($filePath)) {
            $this->error('File not found: ' . $filePath);
            return self::FAILURE;
        }

        $categoriesOption = (string) ($this->option('topics') ?: $this->option('categories'));
        $wantedCategories = $this->resolveCategories($categoriesOption);

        if ($wantedCategories->isEmpty()) {
            $this->error('No categories provided. Example: --categories=earth,space');
            return self::FAILURE;
        }

        $html = file_get_contents($filePath);
        if ($html === false) {
            $this->error('Unable to read file: ' . $filePath);
            return self::FAILURE;
        }

        $videosByCategory = $this->parseVideosByCategory($html);
        if (empty($videosByCategory)) {
            $this->error('No topic cards with videos were found in the provided file.');
            return self::FAILURE;
        }

        $updated = 0;
        $skippedExisting = 0;
        $fallbackFilled = 0;
        $missed = 0;

        foreach ($wantedCategories as $categorySlug) {
            $category = Category::query()->where('slug', $categorySlug)->first();
            if (!$category) {
                $this->warn("Category {$categorySlug} not found.");
                continue;
            }

            $categoryTitle = Str::title(str_replace('-', ' ', $categorySlug));
            $topicMap = $videosByCategory[$categoryTitle] ?? [];

            if (empty($topicMap)) {
                $this->warn("No parsed videos for category {$categoryTitle}.");
                continue;
            }

            $this->info("Importing {$categoryTitle} videos: " . count($topicMap) . ' topic entries');

            $matchedArticleIds = [];
            $fallbackEmbed = null;

            foreach ($topicMap as $topicLabel => $videoUrl) {
                $article = $this->findArticleForTopic($category->id, $topicLabel);

                if (!$article) {
                    $this->line("  Missed: {$topicLabel}");
                    $missed++;
                    continue;
                }

                $embedUrl = $this->toEmbedUrl($videoUrl);
                if (!$embedUrl) {
                    $this->line("  Skipped invalid URL for {$topicLabel}");
                    $missed++;
                    continue;
                }

                if ($fallbackEmbed === null) {
                    $fallbackEmbed = $embedUrl;
                }

                $matchedArticleIds[] = (int) $article->id;

                if (!$force && !empty($article->video_url)) {
                    $this->line("  Skipped existing video for {$article->slug}");
                    $skippedExisting++;
                    continue;
                }

                if ($dryRun) {
                    $this->line("  [dry-run] {$article->slug} -> {$embedUrl}");
                    $updated++;
                    continue;
                }

                $article->video_url = $embedUrl;
                $article->save();
                $this->line("  Updated {$article->slug}");
                $updated++;
            }

            if ($fillCategoryDefault && $fallbackEmbed) {
                $fallbackQuery = Article::query()
                    ->where('category_id', $category->id)
                    ->where('status', 'published');

                if (!empty($matchedArticleIds)) {
                    $fallbackQuery->whereNotIn('id', $matchedArticleIds);
                }

                if (!$force) {
                    $fallbackQuery->where(function ($q) {
                        $q->whereNull('video_url')->orWhere('video_url', '');
                    });
                }

                $fallbackArticles = $fallbackQuery->get();
                foreach ($fallbackArticles as $fallbackArticle) {
                    if (!$fallbackArticle instanceof Article) {
                        continue;
                    }

                    if ($dryRun) {
                        $this->line("  [dry-run fallback] {$fallbackArticle->slug} -> {$fallbackEmbed}");
                        $fallbackFilled++;
                        continue;
                    }

                    $fallbackArticle->video_url = $fallbackEmbed;
                    $fallbackArticle->save();
                    $this->line("  Filled fallback video for {$fallbackArticle->slug}");
                    $fallbackFilled++;
                }
            }
        }

        $this->newLine();
        $this->info('Video import complete. Updated (or planned): ' . $updated
            . ', Fallback filled: ' . $fallbackFilled
            . ', Skipped existing: ' . $skippedExisting
            . ', Missed: ' . $missed);

        return self::SUCCESS;
    }

    /**
     * @return Collection<int, string>
     */
    private function resolveCategories(string $raw): Collection
    {
        $value = trim(Str::lower($raw));
        if ($value === '' || $value === 'all' || $value === '*') {
            return Category::query()
                ->pluck('slug')
                ->map(fn ($slug) => trim(Str::lower((string) $slug)))
                ->filter()
                ->unique()
                ->values();
        }

        return collect(explode(',', $value))
            ->map(fn (string $v) => trim(Str::lower($v)))
            ->filter()
            ->unique()
            ->values();
    }

    private function findArticleForTopic(int $categoryId, string $topicLabel): ?Article
    {
        $topicSlug = Str::slug($topicLabel);
        $topicSlug = $this->normalizeTopicSlug($topicSlug);
        $normalizedTitle = Str::lower(trim($topicLabel));

        /** @var Article|null $article */
        $article = Article::query()
            ->where('category_id', $categoryId)
            ->where(function ($q) use ($topicSlug, $normalizedTitle) {
                $q->where('slug', $topicSlug)
                    ->orWhereRaw('LOWER(title) = ?', [$normalizedTitle])
                    ->orWhere('slug', 'like', '%' . $topicSlug . '%')
                    ->orWhereRaw('LOWER(title) LIKE ?', ['%' . $normalizedTitle . '%']);
            })
            ->orderByRaw('CASE WHEN slug = ? THEN 0 WHEN LOWER(title) = ? THEN 1 ELSE 2 END', [$topicSlug, $normalizedTitle])
            ->first();

        return $article;
    }

    private function normalizeTopicSlug(string $topicSlug): string
    {
        $aliases = [
            'rainforests-ecosystem' => 'rainforest-ecosystems',
            'evolution-theory' => 'theory-of-evolution',
            'astronomy-vs-astrophysics' => 'astronomy-vs-astrophysics',
            'artificial-intelligence' => 'artificial-intelligence',
            'astrobiology' => 'astrobiology',
        ];

        return $aliases[$topicSlug] ?? $topicSlug;
    }

    /**
     * @return array<string, array<string, string>>
     */
    private function parseVideosByCategory(string $html): array
    {
        $doc = new \DOMDocument();
        libxml_use_internal_errors(true);
        $doc->loadHTML($html);
        libxml_clear_errors();

        $xpath = new \DOMXPath($doc);
        $sections = $xpath->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' category-section ')]");

        $result = [];
        foreach ($sections ?: [] as $section) {
            $categoryNode = $xpath->query(".//*[contains(concat(' ', normalize-space(@class), ' '), ' category-header ')]//h2", $section)->item(0);
            if (!$categoryNode) {
                continue;
            }

            $categoryName = trim($categoryNode->textContent);
            if ($categoryName === '') {
                continue;
            }

            $cards = $xpath->query(".//*[contains(concat(' ', normalize-space(@class), ' '), ' topic-card ')]", $section);
            foreach ($cards ?: [] as $card) {
                $topicNode = $xpath->query(".//*[contains(concat(' ', normalize-space(@class), ' '), ' topic-label ')]", $card)->item(0);
                $iframe = $xpath->query('.//iframe', $card)->item(0);

                if (!$topicNode || !$iframe) {
                    continue;
                }

                if (!$iframe instanceof \DOMElement) {
                    continue;
                }

                $topicLabel = trim($topicNode->textContent);
                $src = trim((string) $iframe->getAttribute('src'));

                if ($topicLabel === '' || $src === '') {
                    continue;
                }

                $result[$categoryName][$topicLabel] = $src;
            }
        }

        return $result;
    }

    private function toEmbedUrl(string $url): ?string
    {
        $url = trim($url);
        if ($url === '') {
            return null;
        }

        if (preg_match('#youtube\.com/embed/([A-Za-z0-9_-]{6,})#', $url, $m)) {
            return 'https://www.youtube.com/embed/' . $m[1] . '?rel=0&modestbranding=1';
        }

        if (preg_match('#youtu\.be/([A-Za-z0-9_-]{6,})#', $url, $m)) {
            return 'https://www.youtube.com/embed/' . $m[1] . '?rel=0&modestbranding=1';
        }

        if (preg_match('#[?&]v=([A-Za-z0-9_-]{6,})#', $url, $m)) {
            return 'https://www.youtube.com/embed/' . $m[1] . '?rel=0&modestbranding=1';
        }

        if (preg_match('#youtube\.com/shorts/([A-Za-z0-9_-]{6,})#', $url, $m)) {
            return 'https://www.youtube.com/embed/' . $m[1] . '?rel=0&modestbranding=1';
        }

        return null;
    }
}
