<?php

namespace App\Http\Controllers;

use App\Models\ChatSession;
use App\Models\Post;
use App\Models\Project;
use App\Models\SiteSetting;
use App\Models\Subscriber;
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
            'profileSettings' => $this->profileSettings(),
            'chatSessions' => ChatSession::query()
                ->with(['messages' => fn ($query) => $query->oldest()])
                ->latest('last_message_at')
                ->latest()
                ->get(),
        ]);
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
