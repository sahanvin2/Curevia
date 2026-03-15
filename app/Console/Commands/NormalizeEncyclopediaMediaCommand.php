<?php

namespace App\Console\Commands;

use App\Models\Article;
use Illuminate\Console\Command;

class NormalizeEncyclopediaMediaCommand extends Command
{
    protected $signature = 'encyclopedia:normalize-media
        {--status=published : Limit normalization to a specific article status}
        {--dry-run : Preview changes without writing}';

    protected $description = 'Normalize encyclopedia article media so display stays topic-consistent across all topics.';

    public function handle(): int
    {
        $status = trim((string) $this->option('status'));
        $dryRun = (bool) $this->option('dry-run');

        $query = Article::query()->orderBy('id');
        if ($status !== '') {
            $query->where('status', $status);
        }

        $checked = 0;
        $updated = 0;

        $query->chunkById(200, function ($articles) use (&$checked, &$updated, $dryRun): void {
            foreach ($articles as $article) {
                if (!$article instanceof Article) {
                    continue;
                }

                $checked++;
                $dirty = false;

                $images = is_array($article->images) ? $article->images : [];
                if (!empty($images)) {
                    $article->images = [];
                    $dirty = true;
                }

                $sections = is_array($article->content_sections) ? $article->content_sections : [];
                if (!empty($sections)) {
                    $normalizedSections = [];
                    foreach ($sections as $section) {
                        if (!is_array($section)) {
                            continue;
                        }

                        $sectionImages = is_array($section['images'] ?? null) ? $section['images'] : [];
                        if (!empty($sectionImages)) {
                            $section['images'] = [];
                            $dirty = true;
                        }

                        $normalizedSections[] = $section;
                    }

                    if ($dirty) {
                        $article->content_sections = $normalizedSections;
                    }
                }

                $featured = trim((string) ($article->getRawOriginal('featured_image') ?? ''));
                if ($featured !== '' && preg_match('~^(https?:)?//(?:images|source)\.unsplash\.com/~i', $featured) === 1) {
                    $article->featured_image = null;
                    $dirty = true;
                }

                if (!$dirty) {
                    continue;
                }

                if (!$dryRun) {
                    $article->save();
                }

                $updated++;
            }
        });

        $suffix = $dryRun ? ' [dry-run]' : '';
        $this->info("Checked: {$checked}");
        $this->info("Updated: {$updated}{$suffix}");

        return self::SUCCESS;
    }
}
