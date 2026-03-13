<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;

class DocumentImportService
{
    /**
     * Parse an uploaded document into title, summary, content, and sections.
     */
    public function parse(UploadedFile $file): ?array
    {
        $ext = strtolower($file->getClientOriginalExtension());

        return match ($ext) {
            'docx' => $this->parseDocx($file->getPathname()),
            'pdf'  => $this->parsePdf($file->getPathname()),
            'txt', 'md' => $this->parsePlainText($file->getPathname()),
            default => null,
        };
    }

    private function parseDocx(string $path): ?array
    {
        $zip = new \ZipArchive();
        if ($zip->open($path) !== true) {
            return null;
        }

        $xml = $zip->getFromName('word/document.xml');
        $zip->close();

        if (!$xml) {
            return null;
        }

        libxml_use_internal_errors(true);
        $dom = new \DOMDocument();
        $dom->loadXML($xml);
        libxml_clear_errors();

        $xpath = new \DOMXPath($dom);
        $xpath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');

        $paragraphNodes = $xpath->query('//w:body/w:p');
        $paragraphs = [];

        foreach ($paragraphNodes as $p) {
            $tNodes = $xpath->query('.//w:t', $p);
            $text = '';
            foreach ($tNodes as $t) {
                $text .= $t->nodeValue;
            }
            $text = trim($text);
            if ($text !== '') {
                $paragraphs[] = preg_replace('/\s+/u', ' ', $text);
            }
        }

        return $this->buildStructuredOutput($paragraphs);
    }

    private function parsePdf(string $path): ?array
    {
        $bin = @file_get_contents($path);
        if (!$bin) {
            return null;
        }

        // Very lightweight PDF text extraction fallback (no external dependency).
        $bin = preg_replace('/\r/', "\n", $bin);
        preg_match_all('/\((.*?)\)\s*Tj/s', $bin, $matchesA);
        preg_match_all('/\[(.*?)\]\s*TJ/s', $bin, $matchesB);

        $chunks = [];
        foreach ($matchesA[1] ?? [] as $part) {
            $chunks[] = $this->decodePdfString($part);
        }

        foreach ($matchesB[1] ?? [] as $group) {
            if (preg_match_all('/\((.*?)\)/s', $group, $in)) {
                foreach ($in[1] as $part) {
                    $chunks[] = $this->decodePdfString($part);
                }
            }
        }

        $text = trim(preg_replace('/\s+/u', ' ', implode("\n", $chunks)));
        if ($text === '') {
            return null;
        }

        $paragraphs = array_values(array_filter(array_map('trim', preg_split('/\n{2,}/', $text))));
        if (empty($paragraphs)) {
            $paragraphs = [$text];
        }

        return $this->buildStructuredOutput($paragraphs);
    }

    private function parsePlainText(string $path): ?array
    {
        $text = @file_get_contents($path);
        if (!$text) {
            return null;
        }

        $paragraphs = array_values(array_filter(array_map('trim', preg_split('/\R{2,}/u', $text))));
        if (empty($paragraphs)) {
            return null;
        }

        return $this->buildStructuredOutput($paragraphs);
    }

    private function buildStructuredOutput(array $paragraphs): ?array
    {
        if (empty($paragraphs)) {
            return null;
        }

        $title = $paragraphs[0] ?? 'Untitled Article';
        $summary = $paragraphs[1] ?? ($paragraphs[0] ?? '');

        $bodyParagraphs = array_slice($paragraphs, 2);
        if (empty($bodyParagraphs)) {
            $bodyParagraphs = array_slice($paragraphs, 1);
        }

        $sections = [];
        $sectionTitle = 'Overview';
        $sectionBody = [];

        foreach ($bodyParagraphs as $paragraph) {
            if ($this->looksLikeHeading($paragraph)) {
                if (!empty($sectionBody)) {
                    $sections[] = [
                        'title' => $sectionTitle,
                        'body' => implode("\n\n", $sectionBody),
                        'images' => [],
                        'video_url' => null,
                    ];
                    $sectionBody = [];
                }
                $sectionTitle = trim($paragraph, " \t\n\r\0\x0B:#-");
            } else {
                $sectionBody[] = $paragraph;
            }
        }

        if (!empty($sectionBody) || empty($sections)) {
            $sections[] = [
                'title' => $sectionTitle,
                'body' => implode("\n\n", $sectionBody ?: $bodyParagraphs),
                'images' => [],
                'video_url' => null,
            ];
        }

        $content = collect($sections)
            ->map(fn ($sec) => '## ' . $sec['title'] . "\n\n" . $sec['body'])
            ->implode("\n\n");

        return [
            'title' => trim($title),
            'summary' => trim($summary),
            'content' => trim($content),
            'sections' => $sections,
        ];
    }

    private function looksLikeHeading(string $line): bool
    {
        $line = trim($line);
        if ($line === '' || mb_strlen($line) > 90) {
            return false;
        }

        if (preg_match('/^[A-Z][A-Za-z0-9 ,:&\-\(\)]+$/', $line) && mb_strlen($line) <= 60) {
            return true;
        }

        return preg_match('/^(Chapter|Section|Part)\s+\d+/i', $line) === 1;
    }

    private function decodePdfString(string $value): string
    {
        $value = str_replace(['\\n', '\\r', '\\t'], ["\n", ' ', ' '], $value);
        $value = str_replace(['\\(', '\\)', '\\\\'], ['(', ')', '\\'], $value);

        return trim($value);
    }
}
