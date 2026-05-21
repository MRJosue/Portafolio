<?php

namespace App\Http\Controllers;

use App\Models\ChatSession;
use App\Models\FinancialEntry;
use App\Models\Post;
use App\Models\Project;
use App\Models\SiteSetting;
use App\Models\Subscriber;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function index(): View
    {
        return view('admin', [
            'posts' => Post::query()->latest()->get(),
            'projects' => Project::query()->latest()->get(),
            'subscribers' => Subscriber::query()->latest()->get(),
            'financeDashboard' => $this->financeDashboard(),
            'profileSettings' => $this->profileSettings(),
            'chatSessions' => ChatSession::query()
                ->with(['messages' => fn ($query) => $query->oldest()])
                ->latest('last_message_at')
                ->latest()
                ->get(),
        ]);
    }

    private function financeDashboard(): array
    {
        $today = CarbonImmutable::now();
        $fixedEntries = FinancialEntry::query()
            ->active()
            ->whereIn('type', [FinancialEntry::TYPE_FIXED_EXPENSE, FinancialEntry::TYPE_FIXED_ASSET])
            ->get();
        $monthlyEntries = FinancialEntry::query()
            ->active()
            ->whereIn('type', [FinancialEntry::TYPE_EXPENSE, FinancialEntry::TYPE_INCOME])
            ->whereDate('entry_date', '>=', $today->startOfMonth()->toDateString())
            ->whereDate('entry_date', '<=', $today->endOfMonth()->toDateString())
            ->get();

        $fortnightStart = $today->day <= 15 ? $today->startOfMonth() : $today->startOfMonth()->setDay(16);
        $fortnightEnd = $today->day <= 15 ? $today->startOfMonth()->setDay(15) : $today->endOfMonth();
        $fortnightEntries = $monthlyEntries->filter(function (FinancialEntry $entry) use ($fortnightStart, $fortnightEnd) {
            return $entry->entry_date->betweenIncluded($fortnightStart, $fortnightEnd);
        });

        return [
            'month_label' => ucfirst($today->locale('es')->translatedFormat('F Y')),
            'fortnight_label' => $today->day <= 15 ? 'Primera quincena' : 'Segunda quincena',
            'monthly' => $this->financePeriodSummary($fixedEntries, $monthlyEntries, 1),
            'fortnight' => $this->financePeriodSummary($fixedEntries, $fortnightEntries, 2),
            'movement_totals' => $this->financeMovementTotals($fixedEntries, $monthlyEntries),
            'expense_categories' => $this->financeExpenseCategories($fixedEntries, $monthlyEntries),
        ];
    }

    private function financePeriodSummary($fixedEntries, $variableEntries, int $fixedDivider): array
    {
        $fixedExpenses = ((float) $fixedEntries->where('type', FinancialEntry::TYPE_FIXED_EXPENSE)->sum('amount')) / $fixedDivider;
        $fixedAssets = ((float) $fixedEntries->where('type', FinancialEntry::TYPE_FIXED_ASSET)->sum('amount')) / $fixedDivider;
        $variableExpenses = (float) $variableEntries->where('type', FinancialEntry::TYPE_EXPENSE)->sum('amount');
        $variableIncomes = (float) $variableEntries->where('type', FinancialEntry::TYPE_INCOME)->sum('amount');
        $expenses = $fixedExpenses + $variableExpenses;
        $assets = $fixedAssets + $variableIncomes;

        return [
            'expenses' => $expenses,
            'assets' => $assets,
            'balance' => $assets - $expenses,
        ];
    }

    private function financeMovementTotals($fixedEntries, $monthlyEntries): array
    {
        $entriesByType = [
            FinancialEntry::TYPE_FIXED_EXPENSE => $fixedEntries,
            FinancialEntry::TYPE_EXPENSE => $monthlyEntries,
            FinancialEntry::TYPE_FIXED_ASSET => $fixedEntries,
            FinancialEntry::TYPE_INCOME => $monthlyEntries,
        ];

        return collect(FinancialEntry::TYPE_LABELS)
            ->map(function (string $label, string $type) use ($entriesByType) {
                return [
                    'label' => $label,
                    'amount' => (float) $entriesByType[$type]->where('type', $type)->sum('amount'),
                ];
            })
            ->values()
            ->all();
    }

    private function financeExpenseCategories($fixedEntries, $monthlyEntries): array
    {
        return collect(FinancialEntry::EXPENSE_NAMES)
            ->map(function (string $name) use ($fixedEntries, $monthlyEntries) {
                $fixedAmount = (float) $fixedEntries
                    ->where('type', FinancialEntry::TYPE_FIXED_EXPENSE)
                    ->where('name', $name)
                    ->sum('amount');
                $variableAmount = (float) $monthlyEntries
                    ->where('type', FinancialEntry::TYPE_EXPENSE)
                    ->where('name', $name)
                    ->sum('amount');

                return [
                    'name' => $name,
                    'fixed_amount' => $fixedAmount,
                    'variable_amount' => $variableAmount,
                    'total' => $fixedAmount + $variableAmount,
                ];
            })
            ->filter(fn (array $category) => $category['total'] > 0)
            ->values()
            ->all();
    }

    public function storePost(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'category' => ['required', 'string', 'max:40'],
            'excerpt' => ['required', 'string', 'max:420'],
            'reading_time' => ['required', 'integer', 'min:1', 'max:15'],
        ]);

        $baseSlug = Str::slug($validated['title']);
        $slug = $baseSlug;
        $counter = 2;

        while (Post::query()->where('slug', $slug)->exists()) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        Post::create([
            ...$validated,
            'slug' => $slug,
            'status' => 'published',
            'published_at' => now(),
        ]);

        return back()->with('admin_status', 'Post creado y publicado.');
    }

    public function storeProject(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:140'],
            'client' => ['nullable', 'string', 'max:120'],
            'service' => ['required', 'string', 'max:80'],
            'year' => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'role' => ['nullable', 'string', 'max:120'],
            'summary' => ['required', 'string', 'max:520'],
            'description' => ['required', 'string'],
            'challenge' => ['nullable', 'string'],
            'solution' => ['nullable', 'string'],
            'results' => ['nullable', 'string'],
            'technologies' => ['nullable', 'string', 'max:300'],
            'image_theme' => ['required', 'string', 'in:visual-one,visual-two,visual-three'],
            'image' => ['nullable', 'image', 'max:6144'],
            'is_featured' => ['nullable', 'boolean'],
            'status' => ['required', 'string', 'in:draft,published'],
        ]);
        $projectImage = $request->file('image');
        unset($validated['image']);

        $baseSlug = Str::slug($validated['title']);
        $slug = $baseSlug;
        $counter = 2;

        while (Project::query()->where('slug', $slug)->exists()) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        Project::create([
            ...$validated,
            'slug' => $slug,
            'technologies' => collect(explode(',', $validated['technologies'] ?? ''))
                ->map(fn ($technology) => trim($technology))
                ->filter()
                ->values()
                ->all(),
            'image_path' => $projectImage ? $this->storeProjectImage($projectImage) : null,
            'is_featured' => (bool) ($validated['is_featured'] ?? false),
            'published_at' => $validated['status'] === 'published' ? now() : null,
        ]);

        return back()->with('admin_status', 'Proyecto creado en la base de datos.');
    }

    public function updateProjectImage(Request $request, Project $project): RedirectResponse
    {
        $request->validate([
            'image' => ['required', 'image', 'max:6144'],
        ]);

        if ($project->image_path) {
            $this->deleteProjectImage($project->image_path);
        }

        $project->update([
            'image_path' => $this->storeProjectImage($request->file('image')),
        ]);

        return redirect(route('admin.index').'#projects')->with('admin_status', 'Foto del proyecto actualizada.');
    }

    public function destroyProject(Project $project): RedirectResponse
    {
        if ($project->image_path) {
            $this->deleteProjectImage($project->image_path);
        }

        $project->delete();

        return back()->with('admin_status', 'Proyecto eliminado.');
    }

    public function updateProfileCard(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'profile_quote' => ['nullable', 'string', 'max:160'],
            'profile_light_a' => ['nullable', 'image', 'max:4096'],
            'profile_light_b' => ['nullable', 'image', 'max:4096'],
            'profile_dark_a' => ['nullable', 'image', 'max:4096'],
            'profile_dark_b' => ['nullable', 'image', 'max:4096'],
        ]);

        SiteSetting::setValue(
            'profile_quote',
            ($validated['profile_quote'] ?? null) ?: 'Construir bien tambien es una forma de pensar con calma.',
        );

        foreach (['profile_light_a', 'profile_light_b', 'profile_dark_a', 'profile_dark_b'] as $field) {
            if (! $request->hasFile($field)) {
                continue;
            }

            $currentPath = SiteSetting::getValue($field);
            if ($currentPath) {
                $this->deleteProfileImage($currentPath);
            }

            SiteSetting::setValue($field, $this->storeProfileImage($request->file($field)));
        }

        return back()->with('admin_status', 'Fotos de perfil actualizadas.');
    }

    private function storeProfileImage($image): string
    {
        $directory = public_path('uploads/profile-card');

        File::ensureDirectoryExists($directory);

        $filename = Str::uuid()->toString().'.'.$image->guessExtension();
        $image->move($directory, $filename);

        return 'uploads/profile-card/'.$filename;
    }

    private function storeProjectImage($image): string
    {
        $directory = public_path('uploads/projects');

        File::ensureDirectoryExists($directory);

        $filename = Str::uuid()->toString().'.'.$image->guessExtension();
        $image->move($directory, $filename);

        return 'uploads/projects/'.$filename;
    }

    private function deleteProjectImage(string $path): void
    {
        if (Str::startsWith($path, 'uploads/projects/')) {
            File::delete(public_path($path));

            return;
        }

        Storage::disk('public')->delete($path);
    }

    private function deleteProfileImage(string $path): void
    {
        if (Str::startsWith($path, 'uploads/profile-card/')) {
            File::delete(public_path($path));

            return;
        }

        Storage::disk('public')->delete($path);
    }

    private function profileSettings(): array
    {
        $settings = SiteSetting::query()
            ->whereIn('key', [
                'profile_light_a',
                'profile_light_b',
                'profile_dark_a',
                'profile_dark_b',
                'profile_quote',
            ])
            ->pluck('value', 'key');

        return [
            'profile_light_a' => $this->profileImageUrl($settings->get('profile_light_a')),
            'profile_light_b' => $this->profileImageUrl($settings->get('profile_light_b')),
            'profile_dark_a' => $this->profileImageUrl($settings->get('profile_dark_a')),
            'profile_dark_b' => $this->profileImageUrl($settings->get('profile_dark_b')),
            'profile_quote' => $settings->get('profile_quote') ?: 'Construir bien tambien es una forma de pensar con calma.',
        ];
    }

    private function profileImageUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        if (Str::startsWith($path, 'uploads/profile-card/')) {
            return asset($path);
        }

        return Storage::disk('public')->url($path);
    }
}
