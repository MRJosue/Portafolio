<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use RuntimeException;
use Smalot\PdfParser\Parser as PdfParser;
use ZipArchive;

class CvImportService
{
    public function import(UploadedFile $file): array
    {
        $text = $this->extractText($file);
        $parsed = $this->parseText($text);
        $path = $file->store('cv-documents', 'public');

        return [
            'document' => [
                'original_name' => $file->getClientOriginalName(),
                'path' => $path,
                'mime_type' => $file->getClientMimeType(),
                'size' => $file->getSize() ?: 0,
                'extracted_text' => $text,
                'parsed_data' => $parsed,
            ],
            'parsed' => $parsed,
        ];
    }

    public function extractText(UploadedFile $file): string
    {
        $extension = strtolower($file->getClientOriginalExtension());

        return match ($extension) {
            'txt' => $this->normalizeText((string) file_get_contents($file->getRealPath())),
            'docx' => $this->extractDocxText($file->getRealPath()),
            'pdf' => $this->extractPdfText($file->getRealPath()),
            default => throw new RuntimeException('Formato de CV no soportado.'),
        };
    }

    public function parseText(string $text): array
    {
        $cleanText = $this->normalizeText($text);
        $lines = collect(preg_split('/\R/u', $cleanText) ?: [])
            ->map(fn ($line) => trim($line))
            ->filter()
            ->values();

        $sections = $this->sections($lines->all());
        $contactBlock = $lines->take(12)->implode("\n");
        $summary = Arr::first([
            $sections['summary'] ?? null,
            $sections['profile'] ?? null,
            $sections['objective'] ?? null,
        ]);

        return [
            'full_name' => $this->guessName($lines->all()),
            'email' => $this->firstMatch('/[A-Z0-9._%+\-]+@[A-Z0-9.\-]+\.[A-Z]{2,}/iu', $cleanText),
            'phone' => $this->firstMatch('/(?:\+?\d[\d\s().-]{7,}\d)/u', $contactBlock),
            'location' => null,
            'headline' => $lines->get(1),
            'summary' => $summary,
            'skills' => $this->itemsFromSection($sections['skills'] ?? ''),
            'languages' => $this->itemsFromSection($sections['languages'] ?? ''),
            'links' => $this->links($cleanText),
            'experiences' => $this->entriesFromSection($sections['experience'] ?? ''),
            'educations' => $this->entriesFromSection($sections['education'] ?? ''),
            'raw_text' => Str::limit($cleanText, 12000, ''),
        ];
    }

    private function extractDocxText(string $path): string
    {
        $zip = new ZipArchive;

        if ($zip->open($path) !== true) {
            throw new RuntimeException('No se pudo abrir el DOCX.');
        }

        $document = $zip->getFromName('word/document.xml');
        $zip->close();

        if (! $document) {
            throw new RuntimeException('El DOCX no contiene texto legible.');
        }

        $document = preg_replace('/<\/w:p>/u', "\n", $document) ?? $document;
        $document = preg_replace('/<\/w:tr>/u', "\n", $document) ?? $document;
        $text = html_entity_decode(strip_tags($document), ENT_QUOTES | ENT_XML1, 'UTF-8');

        return $this->normalizeText($text);
    }

    private function extractPdfText(string $path): string
    {
        $parser = new PdfParser;
        $pdf = $parser->parseFile($path);
        $text = $this->normalizeText($pdf->getText());

        if (mb_strlen($text) < 40) {
            throw new RuntimeException('El PDF no tiene texto suficiente. Puede ser escaneado o estar como imagen.');
        }

        return $text;
    }

    private function normalizeText(string $text): string
    {
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        $text = preg_replace('/[ \t]+/u', ' ', $text) ?? $text;
        $text = preg_replace("/\n{3,}/u", "\n\n", $text) ?? $text;

        return trim($text);
    }

    /**
     * @param  array<int, string>  $lines
     * @return array<string, string>
     */
    private function sections(array $lines): array
    {
        $aliases = [
            'summary' => ['resumen', 'extracto', 'sobre mi', 'acerca de mi', 'perfil profesional', 'professional summary', 'summary'],
            'profile' => ['perfil', 'profile'],
            'objective' => ['objetivo', 'objective'],
            'experience' => ['experiencia', 'experiencia laboral', 'experiencia profesional', 'work experience', 'professional experience', 'employment'],
            'education' => ['educacion', 'educación', 'formacion', 'formación', 'formacion academica', 'academic background', 'education'],
            'skills' => ['habilidades', 'competencias', 'skills', 'technical skills', 'tecnologias', 'tecnologías'],
            'languages' => ['idiomas', 'languages'],
        ];

        $lookup = [];
        foreach ($aliases as $section => $headings) {
            foreach ($headings as $heading) {
                $lookup[$this->headingKey($heading)] = $section;
            }
        }

        $current = null;
        $sections = [];

        foreach ($lines as $line) {
            $key = $this->headingKey($line);

            if (isset($lookup[$key])) {
                $current = $lookup[$key];
                $sections[$current] ??= [];

                continue;
            }

            if ($current) {
                $sections[$current][] = $line;
            }
        }

        return collect($sections)
            ->map(fn ($sectionLines) => trim(implode("\n", $sectionLines)))
            ->all();
    }

    private function headingKey(string $value): string
    {
        $value = Str::ascii(Str::lower(trim($value)));
        $value = preg_replace('/[^a-z ]/u', '', $value) ?? $value;

        return trim($value);
    }

    /**
     * @param  array<int, string>  $lines
     */
    private function guessName(array $lines): ?string
    {
        foreach (array_slice($lines, 0, 8) as $line) {
            if (str_contains($line, '@') || preg_match('/\d{4,}/', $line)) {
                continue;
            }

            if (mb_strlen($line) >= 4 && mb_strlen($line) <= 90) {
                return $line;
            }
        }

        return null;
    }

    private function firstMatch(string $pattern, string $text): ?string
    {
        if (preg_match($pattern, $text, $matches)) {
            return trim($matches[0]);
        }

        return null;
    }

    /**
     * @return array<int, string>
     */
    private function links(string $text): array
    {
        preg_match_all('/https?:\/\/[^\s]+|(?:linkedin|github)\.com\/[^\s]+/iu', $text, $matches);

        return collect($matches[0] ?? [])
            ->map(fn ($item) => trim($item, " \t\n\r\0\x0B.,;"))
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @return array<int, string>
     */
    private function itemsFromSection(string $section): array
    {
        $parts = preg_split('/[\n,;|]+/u', $section) ?: [];

        return collect($parts)
            ->map(fn ($item) => trim(preg_replace('/^[\-*•]\s*/u', '', $item) ?? $item))
            ->filter(fn ($item) => mb_strlen($item) >= 2)
            ->values()
            ->all();
    }

    /**
     * @return array<int, array<string, string|null>>
     */
    private function entriesFromSection(string $section): array
    {
        $blocks = preg_split("/\n{2,}|(?=\n[A-ZÁÉÍÓÚÑ][^\n]{8,90}\n)/u", $section) ?: [];

        return collect($blocks)
            ->map(fn ($block) => trim($block))
            ->filter()
            ->map(function ($block) {
                $lines = collect(preg_split('/\R/u', $block) ?: [])
                    ->map(fn ($line) => trim($line))
                    ->filter()
                    ->values();

                $title = $lines->shift();
                $second = $lines->first();
                $period = $this->firstMatch('/(?:19|20)\d{2}\s*(?:-|–|a|al|to)?\s*(?:actual|presente|present|(?:19|20)\d{2})?/iu', $block);

                return [
                    'title' => $title,
                    'organization' => $second && $second !== $period ? $second : null,
                    'period' => $period,
                    'description' => trim($lines->implode("\n")),
                ];
            })
            ->values()
            ->all();
    }
}
