<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function index(): View
    {
        $projects = Project::query()
            ->where('status', 'published')
            ->latest('published_at')
            ->get();

        return view('projects.index', compact('projects'));
    }

    public function show(Project $project): View
    {
        abort_unless($project->status === 'published', 404);

        $relatedProjects = Project::query()
            ->where('status', 'published')
            ->whereKeyNot($project->getKey())
            ->latest('published_at')
            ->take(3)
            ->get();

        return view('projects.show', compact('project', 'relatedProjects'));
    }
}
