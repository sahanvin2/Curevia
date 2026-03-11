<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class DocxArticleSeeder extends Seeder
{
    /**
     * Parse a .docx file into structured article data.
     * Uses PHP ZipArchive + DOMDocument/XPath — no external libraries needed.
     */
    private function parseDocx(string $path): array
    {
        $zip = new \ZipArchive();
        if ($zip->open($path) !== true) return [];

        $xml = $zip->getFromName('word/document.xml');
        $zip->close();
        if (!$xml) return [];

        libxml_use_internal_errors(true);
        $dom = new \DOMDocument();
        $dom->loadXML($xml);
        libxml_clear_errors();

        $xpath = new \DOMXPath($dom);
        $xpath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');

        // ── Helper: get font size from a paragraph node ──
        $getSz = function (\DOMNode $p) use ($xpath): int {
            $sz = 0;
            $runs = $xpath->query('.//w:r', $p);
            foreach ($runs as $run) {
                $n = $xpath->query('w:rPr/w:sz/@w:val', $run);
                if ($n->length > 0) { $sz = (int)$n->item(0)->nodeValue; break; }
            }
            if ($sz === 0) {
                $n = $xpath->query('w:pPr/w:rPr/w:sz/@w:val', $p);
                if ($n->length > 0) $sz = (int)$n->item(0)->nodeValue;
            }
            return $sz;
        };

        // ── Helper: get full text of a node ──
        $getText = function (\DOMNode $node) use ($xpath): string {
            $tNodes = $xpath->query('.//w:t', $node);
            $text = '';
            foreach ($tNodes as $t) { $text .= $t->nodeValue; }
            return trim($text);
        };

        // ── Title (sz=64) & subtitle (sz=28) ──
        $title = $subtitle = '';
        $paragraphs = $xpath->query('//w:body/w:p');
        foreach ($paragraphs as $p) {
            $sz   = $getSz($p);
            $text = $getText($p);
            if ($sz === 64 && $title    === '' && $text !== '') $title    = $text;
            if ($sz === 28 && $subtitle === '' && $text !== '') $subtitle = $text;
            if ($title !== '' && $subtitle !== '') break;
        }

        // ── Quick facts: from <w:tbl> rows (key cell | value cell) ──
        $quickFacts = [];
        $tableRows = $xpath->query('//w:body/w:tbl/w:tr');
        foreach ($tableRows as $tr) {
            $cells = $xpath->query('w:tc', $tr);
            if ($cells->length >= 2) {
                $key = $getText($cells->item(0));
                $val = $getText($cells->item(1));
                if ($key !== '' && $val !== '') {
                    $quickFacts[$key] = $val;
                }
            }
        }

        // ── Content sections from body paragraphs ──
        // sz=30 or 26 = section heading, sz=24 = body paragraph
        // skip: sz=22 (labels like [ INSERT ... ]), sz=18 (captions/video hints), sz=20 (footer)
        $sections  = [];
        $curTitle  = null;
        $curBody   = [];
        $pastTitle = false; // start parsing after the first sz=30 heading
        foreach ($paragraphs as $p) {
            $sz   = $getSz($p);
            $text = $getText($p);
            if ($text === '' || $sz === 0) continue;

            if (!$pastTitle && $sz === 30) { $pastTitle = true; }
            if (!$pastTitle)               { continue; }

            // skip decorative / meta lines
            if (in_array($sz, [22, 18])) { continue; }
            if ($sz === 20 && (str_starts_with($text, '────') || str_contains($text, 'Encyclopedia | Article:'))) { continue; }

            if ($sz === 30 || $sz === 26) {
                if ($curTitle !== null) {
                    $sections[] = ['title' => $curTitle, 'body' => implode("\n\n", $curBody)];
                }
                $curTitle = $text;
                $curBody  = [];
            } elseif ($sz === 24 && $curTitle !== null) {
                $curBody[] = $text;
            }
        }
        if ($curTitle !== null) {
            $sections[] = ['title' => $curTitle, 'body' => implode("\n\n", $curBody)];
        }

        // ── Summary from Overview section (or first section) ──
        $summary = '';
        foreach ($sections as $sec) {
            if (strtolower($sec['title']) === 'overview') {
                // First paragraph of overview, capped at one sentence set
                $summary = strtok($sec['body'], "\n") ?: '';
                break;
            }
        }
        if ($summary === '' && count($sections) > 0) {
            $summary = strtok($sections[0]['body'], "\n") ?: '';
        }

        return [
            'title'      => $title,
            'subtitle'   => $subtitle,
            'summary'    => $summary,
            'quickFacts' => $quickFacts,
            'sections'   => $sections,
        ];
    }

    public function run(): void
    {
        $assetsBase = base_path('Assests');

        // ── Fetch existing category IDs ──
        $catIds = DB::table('categories')->pluck('id', 'slug');

        // ── Fetch contributor IDs (order as seeded) ──
        $contributors = DB::table('users')
            ->where('role', 'contributor')
            ->orderBy('id')
            ->pluck('id')
            ->toArray();

        if (empty($contributors)) {
            $this->command->error('No contributor users found. Run DatabaseSeeder first.');
            return;
        }

        // Author index per category (maps to contributor expertise)
        // 0=Dr.Elena(Astro), 1=Prof.James(History), 2=Dr.Maya(Marine),
        // 3=Dr.Amir(Neuro), 4=Dr.Sarah(Zoo), 5=Prof.David(Geology)
        $authorByCat = [
            'earth'   => [5, 2, 3, 0, 4, 1, 5, 2, 3, 4, 5, 0, 1, 2, 3, 4, 5],
            'space'   => [0, 0, 0, 0, 0, 1, 1, 0, 2, 3, 4, 5, 1, 0, 3, 2, 4],
            'science' => [3, 2, 3, 2, 0, 4, 3, 2, 2, 3, 0, 1, 4, 0, 0, 3, 4],
        ];

        $categories = [
            'Earth'   => 'earth',
            'Space'   => 'space',
            'Science' => 'science',
        ];

        $seeded = 0;
        $skipped = 0;

        foreach ($categories as $dirName => $categorySlug) {
            $catId = $catIds[$categorySlug] ?? null;
            if (!$catId) {
                $this->command->warn("Category not found: {$categorySlug}");
                continue;
            }

            $catDir = $assetsBase . DIRECTORY_SEPARATOR . $dirName;
            $files  = glob($catDir . DIRECTORY_SEPARATOR . '*.docx');
            sort($files);

            foreach ($files as $fileIdx => $filePath) {
                $data = $this->parseDocx($filePath);
                if (empty($data['title'])) continue;

                $slug = Str::slug($data['title']);

                // Skip duplicates
                if (DB::table('articles')->where('slug', $slug)->exists()) {
                    $skipped++;
                    continue;
                }

                $sections  = $data['sections'];
                $content   = collect($sections)
                    ->map(fn($s) => "## {$s['title']}\n\n{$s['body']}")
                    ->implode("\n\n");

                // Read time (~200 wpm)
                $wordCount = str_word_count(strip_tags($content));
                $readTime  = max(4, (int) round($wordCount / 200));

                // Author
                $pool      = $authorByCat[$categorySlug] ?? [];
                $authorIdx = $pool[$fileIdx] ?? ($fileIdx % count($contributors));
                $authorId  = $contributors[$authorIdx] ?? $contributors[0];

                // Featured image
                $image = $this->getImage($slug, $categorySlug);

                DB::table('articles')->insert([
                    'title'            => $data['title'],
                    'slug'             => $slug,
                    'summary'          => $data['summary'],
                    'content'          => $content,
                    'featured_image'   => $image,
                    'category_id'      => $catId,
                    'author_id'        => $authorId,
                    'status'           => 'published',
                    'read_time'        => $readTime,
                    'views'            => rand(12000, 780000),
                    'quick_facts'      => json_encode($data['quickFacts']),
                    'images'           => json_encode([]),
                    'content_sections' => json_encode($sections),
                    'video_url'        => null,
                    'meta'             => null,
                    'meta_title'       => $data['title'] . ' | Curevia Encyclopedia',
                    'meta_description' => mb_substr($data['summary'], 0, 160),
                    'published_at'     => Carbon::now()->subDays(rand(3, 240)),
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ]);

                $seeded++;
                $this->command->line("  ✓ [{$categorySlug}] {$data['title']}");
            }
        }

        $this->command->info("DocxArticleSeeder complete: {$seeded} articles seeded, {$skipped} skipped.");
    }

    /** Curated Unsplash images per article slug or category. */
    private function getImage(string $slug, string $category): string
    {
        $map = [
            // ── Earth ──
            'structure-of-the-earth'          => 'https://images.unsplash.com/photo-1614730321146-b6fa6a46bcb4?w=1200&q=80',
            'plate-tectonics'                 => 'https://images.unsplash.com/photo-1553949345-eb786bb3f7ba?w=1200&q=80',
            'earths-magnetic-field'           => 'https://images.unsplash.com/photo-1531366936337-7c912a4589a7?w=1200&q=80',
            'earthquakes-and-seismic-waves'   => 'https://images.unsplash.com/photo-1601134467661-3d775b999c18?w=1200&q=80',
            'volcano-formation'               => 'https://images.unsplash.com/photo-1554995207-c18c203602cb?w=1200&q=80',
            'the-water-cycle'                 => 'https://images.unsplash.com/photo-1501504905252-473c47e087f8?w=1200&q=80',
            'climate-systems-of-earth'        => 'https://images.unsplash.com/photo-1504608524841-42584120d26c?w=1200&q=80',
            'the-atmosphere-layers'           => 'https://images.unsplash.com/photo-1446776811953-b23d57bd21aa?w=1200&q=80',
            'ocean-currents'                  => 'https://images.unsplash.com/photo-1505142468610-359e7d316be0?w=1200&q=80',
            'deserts-of-the-world'            => 'https://images.unsplash.com/photo-1509316785289-025f5b846b35?w=1200&q=80',
            'rainforests-ecosystem'           => 'https://images.unsplash.com/photo-1516026672322-bc52d61a55d5?w=1200&q=80',
            'polar-regions'                   => 'https://images.unsplash.com/photo-1470071459604-3b5ec3a7fe05?w=1200&q=80',
            'earths-geological-time-scale'    => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=1200&q=80',
            'soil-formation'                  => 'https://images.unsplash.com/photo-1464226184884-fa280b87c399?w=1200&q=80',
            'river-systems'                   => 'https://images.unsplash.com/photo-1501854140801-50d01698950b?w=1200&q=80',
            'glaciers-and-ice-sheets'         => 'https://images.unsplash.com/photo-1510784722466-f2aa240c0b3b?w=1200&q=80',
            'earths-natural-resources'        => 'https://images.unsplash.com/photo-1473773508845-188df298d2d1?w=1200&q=80',
            // ── Space ──
            'the-solar-system'                => 'https://images.unsplash.com/photo-1545156521-77bd85671d30?w=1200&q=80',
            'the-milky-way-galaxy'            => 'https://images.unsplash.com/photo-1444703686981-a3abbc4d4fe3?w=1200&q=80',
            'black-holes'                     => 'https://images.unsplash.com/photo-1451187580459-43490279c0fa?w=1200&q=80',
            'neutron-stars'                   => 'https://images.unsplash.com/photo-1462331940025-496dfbfc7564?w=1200&q=80',
            'supernova-explosions'            => 'https://images.unsplash.com/photo-1543722530-d2c3201371e7?w=1200&q=80',
            'dark-matter'                     => 'https://images.unsplash.com/photo-1446776858070-70c3d5ed6758?w=1200&q=80',
            'dark-energy'                     => 'https://images.unsplash.com/photo-1470252649378-9c29740c9fa8?w=1200&q=80',
            'life-cycle-of-stars'             => 'https://images.unsplash.com/photo-1419242902214-272b3f66ee7a?w=1200&q=80',
            'exoplanets'                      => 'https://images.unsplash.com/photo-1614728894747-a83421e2b9c9?w=1200&q=80',
            'asteroid-belt'                   => 'https://images.unsplash.com/photo-1566345984367-fa7b27e67d66?w=1200&q=80',
            'comets-and-their-composition'    => 'https://images.unsplash.com/photo-1532968961962-8a0cb3a2d4f0?w=1200&q=80',
            'space-telescopes'                => 'https://images.unsplash.com/photo-1465101162946-4377e57745c3?w=1200&q=80',
            'space-exploration-history'       => 'https://images.unsplash.com/photo-1508739773434-c26b3d09e071?w=1200&q=80',
            'planetary-rings'                 => 'https://images.unsplash.com/photo-1614313913007-2b4ae8ce32d6?w=1200&q=80',
            'the-kuiper-belt'                 => 'https://images.unsplash.com/photo-1502134249126-9f3755a50d78?w=1200&q=80',
            'cosmic-microwave-background'     => 'https://images.unsplash.com/photo-1454789548928-9efd52dc4031?w=1200&q=80',
            'search-for-extraterrestrial-life'=> 'https://images.unsplash.com/photo-1446776709462-d6d4b2c5b8ec?w=1200&q=80',
            // ── Science ──
            'scientific-method'               => 'https://images.unsplash.com/photo-1532187863486-abf9dbad1b69?w=1200&q=80',
            'laws-of-motion'                  => 'https://images.unsplash.com/photo-1635070041078-e363dbe005cb?w=1200&q=80',
            'thermodynamics'                  => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=1200&q=80',
            'electromagnetism'                => 'https://images.unsplash.com/photo-1518770660439-4636190af475?w=1200&q=80',
            'quantum-mechanics'               => 'https://images.unsplash.com/photo-1507413245164-6160d8298b31?w=1200&q=80',
            'evolution-theory'                => 'https://images.unsplash.com/photo-1559757175-5700dde675bc?w=1200&q=80',
            'dna-and-genetics'                => 'https://images.unsplash.com/photo-1530026405186-ed1f139313f8?w=1200&q=80',
            'cell-structure'                  => 'https://images.unsplash.com/photo-1576086213369-97a306d36557?w=1200&q=80',
            'chemical-reactions'              => 'https://images.unsplash.com/photo-1603126857599-f6e157fa2fe6?w=1200&q=80',
            'periodic-table'                  => 'https://images.unsplash.com/photo-1628595351029-c2bf17511435?w=1200&q=80',
            'nanotechnology'                  => 'https://images.unsplash.com/photo-1518770660439-4636190af475?w=1200&q=80',
            'artificial-intelligence'         => 'https://images.unsplash.com/photo-1677442136019-21780ecad979?w=1200&q=80',
            'biotechnology'                   => 'https://images.unsplash.com/photo-1576670159805-381ef8cd6b48?w=1200&q=80',
            'particle-physics'                => 'https://images.unsplash.com/photo-1636466497217-26a8cbeaf0aa?w=1200&q=80',
            'astronomy-vs-astrophysics'       => 'https://images.unsplash.com/photo-1608346128025-1896b97a6fa7?w=1200&q=80',
            'neuroscience'                    => 'https://images.unsplash.com/photo-1559757148-5c350d0d3c56?w=1200&q=80',
            'renewable-energy-technologies'   => 'https://images.unsplash.com/photo-1466611653911-95081537e5b7?w=1200&q=80',
        ];

        // Fallback images per category
        $fallbacks = [
            'earth'   => 'https://images.unsplash.com/photo-1446776811953-b23d57bd21aa?w=1200&q=80',
            'space'   => 'https://images.unsplash.com/photo-1462331940025-496dfbfc7564?w=1200&q=80',
            'science' => 'https://images.unsplash.com/photo-1507413245164-6160d8298b31?w=1200&q=80',
        ];

        return $map[$slug] ?? $fallbacks[$category] ?? 'https://images.unsplash.com/photo-1446776811953-b23d57bd21aa?w=1200&q=80';
    }
}
