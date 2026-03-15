<?php

namespace App\Console\Commands;

use App\Models\Article;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class DedupeArticlesCommand extends Command
{
    protected $signature = 'articles:dedupe
        {--status=published : Filter by article status (published|draft|review|all)}
        {--dry-run : Preview duplicate actions without writing changes}';

    protected $description = 'Unpublish near-duplicate articles by normalized title key while keeping one canonical entry.';

    public function handle(): int
    {
        $status = (string) $this->option('status');
        $dryRun = (bool) $this->option('dry-run');

        if (!in_array($status, ['published', 'draft', 'review', 'all'], true)) {
            $this->error('Invalid --status. Use published, draft, review, or all.');
            return self::FAILURE;
        }

        $query = Article::query()->whereNotNull('title');
        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $articles = $query->get([
            'id',
            'title',
            'slug',
            'status',
            'views',
            'meta',
            'published_at',
            'created_at',
        ]);

        if ($articles->isEmpty()) {
            $this->warn('No articles found for the selected status filter.');
            return self::SUCCESS;
        }

        $groups = [];
        foreach ($articles as $article) {
            $key = $this->normalizeTitleKey((string) $article->title);
            if ($key === '') {
                continue;
            }

            $groups[$key] ??= [];
            $groups[$key][] = $article;
        }

        $groupCount = 0;
        $duplicateCount = 0;
        $updatedCount = 0;

        foreach ($groups as $titleKey => $group) {
            if (count($group) < 2) {
                continue;
            }

            usort($group, function (Article $a, Article $b): int {
                if ((int) $a->views !== (int) $b->views) {
                    return (int) $b->views <=> (int) $a->views;
                }

                $aTime = optional($a->published_at)->getTimestamp() ?? optional($a->created_at)->getTimestamp() ?? 0;
                $bTime = optional($b->published_at)->getTimestamp() ?? optional($b->created_at)->getTimestamp() ?? 0;

                if ($aTime !== $bTime) {
                    return $bTime <=> $aTime;
                }

                return (int) $b->id <=> (int) $a->id;
            });

            /** @var Article $keeper */
            $keeper = $group[0];
            $dupes = array_slice($group, 1);

            $groupCount++;
            $duplicateCount += count($dupes);

            $this->line('');
            $this->info("KEEP [{$keeper->id}] {$keeper->title} ({$keeper->slug})");
            $this->line("  key: {$titleKey}");

            foreach ($dupes as $dup) {
                $this->warn("DROP [{$dup->id}] {$dup->title} ({$dup->slug})");

                if ($dryRun) {
                    continue;
                }

                $meta = is_array($dup->meta) ? $dup->meta : [];
                $meta['deduped_to_slug'] = $keeper->slug;
                $meta['deduped_at'] = now()->toIso8601String();

                $dup->forceFill([
                    'status' => 'draft',
                    'published_at' => null,
                    'meta' => $meta,
                ])->save();

                $updatedCount++;
            }
        }

        $this->newLine();
        $this->info("Duplicate groups: {$groupCount}");
        $this->info("Duplicate entries: {$duplicateCount}");

        if ($dryRun) {
            $this->comment('Dry-run only. No database rows were modified.');
        } else {
            $this->info("Unpublished duplicates: {$updatedCount}");
        }

        return self::SUCCESS;
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
