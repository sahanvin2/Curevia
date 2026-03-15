<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Models\Story;
use App\Models\User;
use App\Services\DocumentImportService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

class SyncTopicContentCommand extends Command
{
    protected $signature = 'content:sync-topics
        {--path=Assests : Source folder for encyclopedia documents}
        {--status=published : Article/story status (draft|review|published)}
        {--author-id=1 : Author user ID for generated stories}
        {--import-limit=0 : Max documents for import (0 = all)}
        {--stories-limit=0 : Max generated stories (0 = all)}
        {--refresh-stories : Update existing generated stories in quick-read format}
        {--skip-import : Skip article import phase}
        {--skip-overviews : Skip overview backfill phase}
        {--skip-stories : Skip story generation phase}
        {--dry-run : Preview only, no database writes}';

    protected $description = 'Sync encyclopedia topics from Assets docs, backfill overviews, and generate simple stories from encyclopedia articles.';

    public function __construct(private DocumentImportService $importer)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $status = (string) $this->option('status');
        $authorId = $this->resolveAuthorId((int) $this->option('author-id'));

        if ($authorId === null) {
            $this->error('No valid author found. Create a user account first.');
            return self::FAILURE;
        }

        if (!in_array($status, ['draft', 'review', 'published'], true)) {
            $this->error('Invalid --status. Use draft, review, or published.');
            return self::FAILURE;
        }

        if (!(bool) $this->option('skip-import')) {
            $this->runImportPhase($dryRun);
        }

        if (!(bool) $this->option('skip-overviews')) {
            $this->runOverviewBackfillPhase($dryRun);
        }

        if (!(bool) $this->option('skip-stories')) {
            $this->runStoryGenerationPhase($status, $authorId, $dryRun);
        }

        $this->newLine();
        $this->info('Topic sync complete.');

        return self::SUCCESS;
    }

    private function runImportPhase(bool $dryRun): void
    {
        $path = (string) $this->option('path');
        $status = (string) $this->option('status');
        $authorId = (int) $this->option('author-id');
        $importLimit = max(0, (int) $this->option('import-limit'));

        $this->newLine();
        $this->line('== Importing encyclopedia docs from Assets ==');

        $args = [
            '--path' => $path,
            '--status' => $status,
            '--author-id' => $authorId,
            '--limit' => $importLimit,
        ];

        if ($dryRun) {
            $args['--dry-run'] = true;
        }

        Artisan::call('articles:import-folder', $args);
        $this->line(Artisan::output());
    }

    private function runOverviewBackfillPhase(bool $dryRun): void
    {
        $this->newLine();
        $this->line('== Backfilling encyclopedia overviews ==');

        $checked = 0;
        $updated = 0;

        Article::query()
            ->whereIn('status', ['published', 'review', 'draft'])
            ->orderBy('id')
            ->chunkById(200, function ($articles) use ($dryRun, &$checked, &$updated): void {
                foreach ($articles as $article) {
                    if (!$article instanceof Article) {
                        continue;
                    }

                    $checked++;
                    $current = trim((string) $article->summary);
                    if (mb_strlen($current) >= 40) {
                        continue;
                    }

                    $candidate = $this->buildOverviewFromArticle($article);
                    if ($candidate === '') {
                        continue;
                    }

                    if (!$dryRun) {
                        $article->summary = $candidate;
                        if (empty($article->meta_description)) {
                            $article->meta_description = Str::limit($candidate, 160, '');
                        }
                        $article->save();
                    }

                    $updated++;
                }
            });

        $suffix = $dryRun ? ' [dry-run]' : '';
        $this->info("Checked articles: {$checked}");
        $this->info("Overviews updated: {$updated}{$suffix}");
    }

    private function buildOverviewFromArticle(Article $article): string
    {
        $sections = is_array($article->content_sections) ? $article->content_sections : [];
        foreach ($sections as $section) {
            $body = trim((string) ($section['body'] ?? ''));
            if ($body !== '') {
                return Str::limit($this->normalizeText($body), 320, '');
            }
        }

        $content = trim((string) $article->content);
        if ($content !== '') {
            $plain = preg_replace('/^##\s+.+$/m', '', $content) ?? '';
            $plain = $this->normalizeText($plain);
            if ($plain !== '') {
                return Str::limit($plain, 320, '');
            }
        }

        return '';
    }

    private function runStoryGenerationPhase(string $status, int $authorId, bool $dryRun): void
    {
        $this->newLine();
        $this->line('== Generating quick-read stories from encyclopedia ==');

        $limit = max(0, (int) $this->option('stories-limit'));
        $refreshStories = (bool) $this->option('refresh-stories');
        $created = 0;
        $updated = 0;
        $skipped = 0;
        $processed = 0;

        $query = Article::query()
            ->where('status', 'published')
            ->with('category')
            ->orderBy('id');

        foreach ($query->cursor() as $article) {
            if (!$article instanceof Article) {
                continue;
            }

            if ($limit > 0 && $created >= $limit) {
                break;
            }

            $processed++;
            $storySlug = Str::slug((string) $article->slug . '-story');
            if ($storySlug === '') {
                $skipped++;
                continue;
            }

            $existing = Story::query()->where('slug', $storySlug)->first();
            if ($existing !== null && !$refreshStories) {
                $skipped++;
                continue;
            }

            if ($existing !== null && !$this->isGeneratedStory($existing)) {
                $skipped++;
                continue;
            }

            $payload = $this->buildStoryFromArticle($article, $storySlug, $status, $authorId);
            if ($payload === null) {
                $skipped++;
                continue;
            }

            if (!$dryRun) {
                if ($existing !== null) {
                    $existing->fill($payload);
                    $existing->save();
                } else {
                    Story::query()->create($payload);
                }
            }

            if ($existing !== null) {
                $updated++;
            } else {
                $created++;
            }
        }

        $suffix = $dryRun ? ' [dry-run]' : '';
        $this->info("Processed articles: {$processed}");
        $this->info("Stories created: {$created}{$suffix}");
        $this->info("Stories updated: {$updated}{$suffix}");
        $this->info("Stories skipped: {$skipped}");
    }

    /**
     * @return array<string, mixed>|null
     */
    private function buildStoryFromArticle(Article $article, string $storySlug, string $status, int $authorId): ?array
    {
        $title = trim((string) $article->title);
        $summary = trim((string) $article->summary);
        if ($title === '' || $summary === '') {
            return null;
        }

        $sections = is_array($article->content_sections) ? $article->content_sections : [];
        $storySections = [];

        if (!empty($sections)) {
            $picked = array_slice($sections, 0, 2);
            foreach ($picked as $index => $section) {
                $sectionTitle = trim((string) ($section['title'] ?? ''));
                $sectionBody = trim((string) ($section['body'] ?? ''));
                if ($sectionBody === '') {
                    continue;
                }

                $storySections[] = [
                    'title' => $sectionTitle !== '' ? $sectionTitle : ('Part ' . ($index + 1)),
                    'body' => Str::limit($this->normalizeText($sectionBody), 420, ''),
                    'images' => [],
                    'video_url' => null,
                ];
            }
        }

        if (empty($storySections)) {
            $storySections[] = [
                'title' => 'The Story',
                'body' => Str::limit($this->normalizeText((string) $article->content), 520, ''),
                'images' => [],
                'video_url' => null,
            ];
        }

        $storyContent = collect($storySections)
            ->map(fn (array $section) => '## ' . $section['title'] . "\n\n" . $section['body'])
            ->implode("\n\n");

        $images = is_array($article->images) ? array_values(array_filter(array_map('strval', $article->images))) : [];

        return [
            'title' => 'The Story of ' . $title,
            'slug' => $storySlug,
            'excerpt' => Str::limit($summary, 150, ''),
            'content' => $storyContent,
            'featured_image' => (string) $article->featured_image,
            'images' => array_slice($images, 0, 3),
            'content_sections' => $storySections,
            'category_id' => (int) $article->category_id,
            'author_id' => $authorId,
            'status' => $status,
            'read_time' => max(1, (int) round(str_word_count(strip_tags($storyContent)) / 220)),
            'views' => 0,
            'is_featured' => false,
            'published_at' => $status === 'published' ? now() : null,
        ];
    }

    private function isGeneratedStory(Story $story): bool
    {
        return str_ends_with((string) $story->slug, '-story')
            && str_starts_with((string) $story->title, 'The Story of ');
    }

    private function normalizeText(string $value): string
    {
        $value = str_replace(["\r\n", "\r"], "\n", $value);
        $value = preg_replace('/\n{3,}/', "\n\n", $value) ?? $value;
        $value = preg_replace('/\s+/u', ' ', $value) ?? $value;

        return trim($value);
    }

    private function resolveAuthorId(int $candidateId): ?int
    {
        if ($candidateId > 0 && User::query()->where('id', $candidateId)->exists()) {
            return $candidateId;
        }

        $fallback = User::query()->orderBy('id')->value('id');
        return $fallback ? (int) $fallback : null;
    }
}
