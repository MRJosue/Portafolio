<?php

namespace App\Http\Controllers;

use App\Models\CvDocument;
use App\Models\Talent;
use App\Services\CvImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

class TalentController extends Controller
{
    public function index(Request $request): View
    {
        $import = $request->session()->get('talent_cv_import');

        return view('talents.index', [
            'talents' => Talent::query()
                ->with(['documents', 'experiences', 'educations'])
                ->latest()
                ->get(),
            'import' => $import,
        ]);
    }

    public function import(Request $request, CvImportService $cvImportService): RedirectResponse
    {
        $validated = $request->validate([
            'cv_document' => ['required', 'file', 'mimes:pdf,docx,txt', 'max:6144'],
        ]);

        try {
            $import = $cvImportService->import($validated['cv_document']);
        } catch (RuntimeException $exception) {
            return back()
                ->withErrors(['cv_document' => $exception->getMessage()])
                ->withInput();
        } catch (Throwable) {
            return back()
                ->withErrors(['cv_document' => 'No se pudo leer el documento. Prueba con un PDF con texto real, DOCX o TXT.'])
                ->withInput();
        }

        $request->session()->put('talent_cv_import', $import);

        return redirect()
            ->route('talents.index')
            ->with('talent_status', 'Documento leido. Revisa y ajusta los campos antes de guardar.');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:140'],
            'email' => ['nullable', 'email', 'max:160'],
            'phone' => ['nullable', 'string', 'max:60'],
            'location' => ['nullable', 'string', 'max:140'],
            'headline' => ['nullable', 'string', 'max:180'],
            'summary' => ['nullable', 'string', 'max:5000'],
            'skills_text' => ['nullable', 'string', 'max:3000'],
            'languages_text' => ['nullable', 'string', 'max:1200'],
            'links_text' => ['nullable', 'string', 'max:2000'],
            'experiences_text' => ['nullable', 'string', 'max:12000'],
            'educations_text' => ['nullable', 'string', 'max:8000'],
            'status' => ['required', 'string', 'in:draft,active,archived'],
        ]);

        $import = $request->session()->get('talent_cv_import');

        DB::transaction(function () use ($request, $validated, $import) {
            $talent = Talent::create([
                'user_id' => $request->user()?->id,
                'full_name' => $validated['full_name'],
                'email' => $validated['email'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'location' => $validated['location'] ?? null,
                'headline' => $validated['headline'] ?? null,
                'summary' => $validated['summary'] ?? null,
                'skills' => $this->lines($validated['skills_text'] ?? ''),
                'languages' => $this->lines($validated['languages_text'] ?? ''),
                'links' => $this->lines($validated['links_text'] ?? ''),
                'status' => $validated['status'],
            ]);

            foreach ($this->blocks($validated['experiences_text'] ?? '') as $index => $block) {
                $entry = $this->entryFromBlock($block);

                $talent->experiences()->create([
                    'role' => $entry['title'],
                    'company' => $entry['organization'],
                    'period' => $entry['period'],
                    'description' => $entry['description'],
                    'sort_order' => $index,
                ]);
            }

            foreach ($this->blocks($validated['educations_text'] ?? '') as $index => $block) {
                $entry = $this->entryFromBlock($block);

                $talent->educations()->create([
                    'degree' => $entry['title'],
                    'institution' => $entry['organization'],
                    'period' => $entry['period'],
                    'description' => $entry['description'],
                    'sort_order' => $index,
                ]);
            }

            if ($import) {
                CvDocument::create([
                    ...Arr::get($import, 'document', []),
                    'talent_id' => $talent->id,
                    'user_id' => $request->user()?->id,
                ]);
            }
        });

        $request->session()->forget('talent_cv_import');

        return redirect()
            ->route('talents.index')
            ->with('talent_status', 'Talento y CV guardados.');
    }

    public function clearImport(Request $request): RedirectResponse
    {
        $request->session()->forget('talent_cv_import');

        return redirect()
            ->route('talents.index')
            ->with('talent_status', 'Importacion descartada.');
    }

    /**
     * @return array<int, string>
     */
    private function lines(string $text): array
    {
        return collect(preg_split('/[\n,;]+/u', $text) ?: [])
            ->map(fn ($line) => trim($line))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @return array<int, string>
     */
    private function blocks(string $text): array
    {
        return collect(preg_split("/\n{2,}/u", trim($text)) ?: [])
            ->map(fn ($block) => trim($block))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @return array{title: ?string, organization: ?string, period: ?string, description: ?string}
     */
    private function entryFromBlock(string $block): array
    {
        $lines = collect(preg_split('/\R/u', $block) ?: [])
            ->map(fn ($line) => trim($line))
            ->filter()
            ->values();

        $title = $lines->shift();
        $organization = $lines->shift();
        $period = null;

        if ($organization && preg_match('/(?:19|20)\d{2}|actual|presente|present/i', $organization)) {
            $period = $organization;
            $organization = null;
        } elseif ($lines->isNotEmpty() && preg_match('/(?:19|20)\d{2}|actual|presente|present/i', $lines->first())) {
            $period = $lines->shift();
        }

        return [
            'title' => $title,
            'organization' => $organization,
            'period' => $period,
            'description' => $lines->implode("\n") ?: null,
        ];
    }
}
