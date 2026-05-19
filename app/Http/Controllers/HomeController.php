<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Project;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function editorialBlack(): View
    {
        $projects = $this->featuredProjects();
        $profileCard = $this->profileCardSettings();

        return view('themes.editorial-black', compact('projects', 'profileCard'));
    }

    public function journal(): View
    {
        $posts = $this->publishedPosts();

        return view('journal.index', compact('posts'));
    }

    public function neoIndustrial(): View
    {
        $posts = $this->publishedPosts();

        return view('themes.neo-industrial', compact('posts'));
    }

    private function publishedPosts()
    {
        return Post::query()
            ->where('status', 'published')
            ->latest('published_at')
            ->take(4)
            ->get();
    }

    private function featuredProjects()
    {
        return Project::query()
            ->where('status', 'published')
            ->orderByDesc('is_featured')
            ->latest('published_at')
            ->take(3)
            ->get();
    }

    private function profileCardSettings(): array
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
            'light_a' => $this->publicStorageUrl($settings->get('profile_light_a')),
            'light_b' => $this->publicStorageUrl($settings->get('profile_light_b')),
            'dark_a' => $this->publicStorageUrl($settings->get('profile_dark_a')),
            'dark_b' => $this->publicStorageUrl($settings->get('profile_dark_b')),
            'quote' => $settings->get('profile_quote') ?: 'Construir bien tambien es una forma de pensar con calma.',
        ];
    }

    private function publicStorageUrl(?string $path): ?string
    {
        if ($path && Str::startsWith($path, 'uploads/profile-card/')) {
            return asset($path);
        }

        return $path ? Storage::disk('public')->url($path) : null;
    }
}
