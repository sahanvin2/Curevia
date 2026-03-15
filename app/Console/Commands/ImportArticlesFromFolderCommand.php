<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Models\Category;
use App\Services\ArticleCategoryClassifier;
use App\Services\DocumentImportService;
use App\Services\MediaService;
use Illuminate\Console\Command;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class ImportArticlesFromFolderCommand extends Command
{
    /**
     * @var array<string, string>
     */
    private array $folderCategoryAliases = [
        'advanced-technology' => 'technology',
        'artificial-intelligence-generative-ai' => 'technology',
        'civilizations-cultural-systems' => 'civilizations',
        'earth-environmental-systems' => 'earth',
        'healthcare-medicine' => 'human-body',
        'modern-zodiac-astrology' => 'zodiac',
        'quantum-computing-encyclopedia' => 'technology',
        'science-advanced-research' => 'science',
        'skincare-dermatology' => 'human-body',
        'space-telescopes' => 'space',
    ];

    protected $signature = 'articles:import-folder
        {--path=Assests/new : Source directory with category subfolders}
        {--status=published : Article status (draft|review|published)}
        {--author-id=1 : Author user ID for imported records}
        {--limit=0 : Max files to process}
        {--dry-run : Preview only, no writes}';

    protected $description = 'Bulk import documents from folder tree into articles with automatic category accuracy.';

    /**
     * @var array<string, bool>
     */
    private array $existingTitleKeys = [];

    /**
     * @var array<string, bool>
     */
    private array $existingSourceNames = [];

    public function handle(
        DocumentImportService $importer,
        ArticleCategoryClassifier $classifier,
        MediaService $media
    ): int {
        $sourcePath = base_path((string) $this->option('path'));
        $status = (string) $this->option('status');
        $authorId = (int) $this->option('author-id');
        $limit = max(0, (int) $this->option('limit'));
        $dryRun = (bool) $this->option('dry-run');

        if (!in_array($status, ['draft', 'review', 'published'], true)) {
            $this->error('Invalid --status. Use draft, review, or published.');
            return self::FAILURE;
        }

        if (!is_dir($sourcePath)) {
            $this->error('Source path not found: ' . $sourcePath);
            return self::FAILURE;
        }

        $categoryBySlug = Category::query()->get()->keyBy('slug');
        if ($categoryBySlug->isEmpty()) {
            $this->error('No categories found. Seed categories first.');
            return self::FAILURE;
        }

        $allowedExt = ['doc', 'docx', 'pdf', 'txt', 'md'];
        $files = [];

        $this->hydrateExistingIndexes();

        foreach (glob($sourcePath . DIRECTORY_SEPARATOR . '*', GLOB_ONLYDIR) ?: [] as $dir) {
            $folderName = basename($dir);
            $candidateSlug = $this->resolveFolderCategorySlug($folderName);

            foreach (glob($dir . DIRECTORY_SEPARATOR . '*') ?: [] as $path) {
                if (!is_file($path)) {
                    continue;
                }

                $ext = strtolower((string) pathinfo($path, PATHINFO_EXTENSION));
                if (!in_array($ext, $allowedExt, true)) {
                    continue;
                }

                $files[] = [
                    'path' => $path,
                    'name' => basename($path),
                    'folder_slug' => $candidateSlug,
                    'folder_name' => $folderName,
                ];
            }
        }

        if (empty($files)) {
            $this->warn('No importable files found.');
            return self::SUCCESS;
        }

        usort($files, fn (array $a, array $b) => strcmp($a['path'], $b['path']));
        if ($limit > 0) {
            $files = array_slice($files, 0, $limit);
        }

        $processed = 0;
        $imported = 0;
        $skipped = 0;
        $failed = 0;

        foreach ($files as $entry) {
            $processed++;

            try {
                $uploaded = new UploadedFile($entry['path'], $entry['name'], null, null, true);
                $parsed = $importer->parse($uploaded);
                if (!$parsed || empty($parsed['title'])) {
                    $this->warn("SKIP parse: {$entry['name']}");
                    $skipped++;
                    continue;
                }

                $title = trim((string) $parsed['title']);
                $content = trim((string) ($parsed['content'] ?? ''));
                $summary = trim((string) ($parsed['summary'] ?? ''));
                $sections = is_array($parsed['sections'] ?? null) ? $parsed['sections'] : [];

                if ($title === '' || $content === '') {
                    $this->warn("SKIP empty content: {$entry['name']}");
                    $skipped++;
                    continue;
                }

                $slug = Str::slug($title);
                if (Article::query()->where('slug', $slug)->exists()) {
                    $this->line("SKIP duplicate slug: {$slug}");
                    $skipped++;
                    continue;
                }

                $titleKey = $this->normalizeTitleKey($title);
                if ($titleKey !== '' && isset($this->existingTitleKeys[$titleKey])) {
                    $this->line("SKIP duplicate title-key: {$title}");
                    $skipped++;
                    continue;
                }

                $sourceNameKey = $this->normalizeSourceName($entry['name']);
                if ($sourceNameKey !== '' && isset($this->existingSourceNames[$sourceNameKey])) {
                    $this->line("SKIP duplicate source document: {$entry['name']}");
                    $skipped++;
                    continue;
                }

                $targetCategory = $categoryBySlug->get($entry['folder_slug']);

                if (!$targetCategory) {
                    $classification = $classifier->classify($title, $summary, $content);
                    $classifiedSlug = (string) ($classification['slug'] ?? '');
                    $classifiedConfidence = (float) ($classification['confidence'] ?? 0.0);

                    if ($classifiedSlug !== '' && $classifiedConfidence >= 0.56) {
                        $targetCategory = $categoryBySlug->get($classifiedSlug);
                    }
                }

                if (!$targetCategory) {
                    $this->warn("SKIP unknown category folder: {$entry['folder_name']}");
                    $skipped++;
                    continue;
                }

                $wordCount = str_word_count(strip_tags($content));
                $readTime = max(3, (int) round($wordCount / 200));

                if (!$dryRun) {
                    $sourceUrl = $media->uploadFile($uploaded, 'articles/documents');

                    Article::query()->create([
                        'title' => $title,
                        'slug' => $slug,
                        'summary' => $summary !== '' ? $summary : Str::limit(strip_tags($content), 240, ''),
                        'content' => $content,
                        'content_sections' => !empty($sections) ? $sections : null,
                        'category_id' => (int) $targetCategory->id,
                        'author_id' => $authorId,
                        'status' => $status,
                        'read_time' => $readTime,
                        'views' => 0,
                        'images' => [],
                        'meta' => $sourceUrl ? [
                            'source_document_url' => $sourceUrl,
                            'source_document_name' => $entry['name'],
                            'source_document_ext' => strtolower((string) pathinfo($entry['name'], PATHINFO_EXTENSION)),
                            'source_document_uploaded_at' => now()->toIso8601String(),
                        ] : null,
                        'published_at' => $status === 'published' ? now() : null,
                    ]);
                }

                $imported++;
                if ($titleKey !== '') {
                    $this->existingTitleKeys[$titleKey] = true;
                }
                if ($sourceNameKey !== '') {
                    $this->existingSourceNames[$sourceNameKey] = true;
                }
                $catName = (string) ($targetCategory->name ?? $entry['folder_name']);
                $suffix = $dryRun ? ' [dry-run]' : '';
                $this->info("OK {$entry['name']} -> {$slug} ({$catName}){$suffix}");
            } catch (\Throwable $e) {
                $failed++;
                $this->error("ERR {$entry['name']}: {$e->getMessage()}");
            }
        }

        $this->newLine();
        $this->info("Processed: {$processed}");
        $this->info("Imported: {$imported}");
        $this->info("Skipped: {$skipped}");
        $this->info("Failed: {$failed}");

        return self::SUCCESS;
    }

    private function resolveFolderCategorySlug(string $folderName): string
    {
        $folderSlug = Str::slug($folderName);

        if (isset($this->folderCategoryAliases[$folderSlug])) {
            return $this->folderCategoryAliases[$folderSlug];
        }

        if (str_contains($folderSlug, 'zodiac') || str_contains($folderSlug, 'astrology')) {
            return 'zodiac';
        }

        if (str_contains($folderSlug, 'civilization') || str_contains($folderSlug, 'civilisation')) {
            return 'civilizations';
        }

        if (str_contains($folderSlug, 'space') || str_contains($folderSlug, 'telescope')) {
            return 'space';
        }

        if (str_contains($folderSlug, 'technology') || str_contains($folderSlug, 'comput')) {
            return 'technology';
        }

        if (str_contains($folderSlug, 'science') || str_contains($folderSlug, 'research')) {
            return 'science';
        }

        if (str_contains($folderSlug, 'health') || str_contains($folderSlug, 'medical') || str_contains($folderSlug, 'dermatolog') || str_contains($folderSlug, 'skin')) {
            return 'human-body';
        }

        if (str_contains($folderSlug, 'environment') || str_contains($folderSlug, 'earth')) {
            return 'earth';
        }

        return $folderSlug === 'human-body' ? 'human-body' : $folderSlug;
    }

    private function hydrateExistingIndexes(): void
    {
        $this->existingTitleKeys = [];
        $this->existingSourceNames = [];

        Article::query()->select(['id', 'title', 'meta'])->chunkById(500, function ($rows): void {
            foreach ($rows as $row) {
                $titleKey = $this->normalizeTitleKey((string) $row->title);
                if ($titleKey !== '') {
                    $this->existingTitleKeys[$titleKey] = true;
                }

                $sourceName = $this->extractSourceDocumentName($row->meta);
                $sourceNameKey = $this->normalizeSourceName($sourceName);
                if ($sourceNameKey !== '') {
                    $this->existingSourceNames[$sourceNameKey] = true;
                }
            }
        });
    }

    private function extractSourceDocumentName(mixed $meta): string
    {
        if (!is_array($meta)) {
            return '';
        }

        $name = (string) ($meta['source_document_name'] ?? '');
        return trim($name);
    }

    private function normalizeSourceName(string $name): string
    {
        if ($name === '') {
            return '';
        }

        return Str::of($name)
            ->lower()
            ->replaceMatches('/\.[a-z0-9]{2,5}$/i', '')
            ->replaceMatches('/[^a-z0-9]+/', ' ')
            ->squish()
            ->value();
    }

    private function normalizeTitleKey(string $title): string
    {
        $clean = Str::of($title)
            ->lower()
            ->replaceMatches('/[^a-z0-9\s]+/', ' ')
            ->squish()
            ->value();

        if ($clean === '') {
            return '';
        }

        $replace = [
            'civilisations' => 'civilization',
            'civilisation' => 'civilization',
            'civilizations' => 'civilization',
            'egyptian' => 'egypt',
            'greek' => 'greece',
            'roman' => 'rome',
        ];

        $stopwords = [
            'a', 'an', 'and', 'are', 'as', 'at', 'by', 'for', 'from', 'in', 'into', 'is',
            'of', 'on', 'or', 'the', 'to', 'with', 'without', 'about', 'guide', 'introduction',
        ];

        $tokens = preg_split('/\s+/', $clean) ?: [];
        $normalized = [];

        foreach ($tokens as $token) {
            if ($token === '') {
                continue;
            }

            $token = $replace[$token] ?? $token;

            if (strlen($token) > 6 && str_ends_with($token, 'ian')) {
                $token = substr($token, 0, -3);
            }

            if (strlen($token) > 5 && str_ends_with($token, 'ing')) {
                $token = substr($token, 0, -3);
            } elseif (strlen($token) > 4 && str_ends_with($token, 'ed')) {
                $token = substr($token, 0, -2);
            } elseif (strlen($token) > 4 && str_ends_with($token, 'es')) {
                $token = substr($token, 0, -2);
            } elseif (strlen($token) > 3 && str_ends_with($token, 's')) {
                $token = substr($token, 0, -1);
            }

            if ($token === '' || in_array($token, $stopwords, true)) {
                continue;
            }

            $normalized[$token] = true;
        }

        if (empty($normalized)) {
            return '';
        }

        $parts = array_keys($normalized);
        sort($parts, SORT_STRING);

        return implode(' ', $parts);
    }
}
