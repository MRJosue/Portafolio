<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class AdminProjectImageTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        File::deleteDirectory(public_path('uploads/projects'));

        parent::tearDown();
    }

    public function test_admin_can_create_project_with_image(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/admin/projects', [
            'title' => 'Sistema de inventario',
            'client' => 'Acme',
            'service' => 'ERP',
            'year' => 2026,
            'role' => 'Backend',
            'summary' => 'Proyecto administrativo para operaciones internas.',
            'description' => 'Sistema Laravel para centralizar pedidos, inventario y reportes.',
            'technologies' => 'Laravel, MySQL',
            'image_theme' => 'visual-one',
            'image' => UploadedFile::fake()->image('inventario.jpg', 1200, 800),
            'is_featured' => '1',
            'status' => 'published',
        ])->assertRedirect();

        $project = Project::query()->firstOrFail();

        $this->assertNotNull($project->image_path);
        $this->assertFileExists(public_path($project->image_path));
    }

    public function test_admin_can_replace_project_image_and_delete_project_file(): void
    {
        $user = User::factory()->create();
        $project = Project::create([
            'title' => 'Portal clientes',
            'slug' => 'portal-clientes',
            'service' => 'Web',
            'summary' => 'Portal privado para clientes.',
            'description' => 'Una descripcion suficiente para el caso.',
            'image_theme' => 'visual-two',
            'status' => 'published',
            'published_at' => now(),
        ]);

        $this->actingAs($user)->patch("/admin/projects/{$project->slug}/image", [
            'image' => UploadedFile::fake()->image('portal.jpg', 1000, 700),
        ])->assertRedirect();

        $project->refresh();
        $firstPath = $project->image_path;

        $this->assertFileExists(public_path($firstPath));

        $this->actingAs($user)->patch("/admin/projects/{$project->slug}/image", [
            'image' => UploadedFile::fake()->image('portal-nueva.jpg', 1000, 700),
        ])->assertRedirect();

        $project->refresh();

        $this->assertFileDoesNotExist(public_path($firstPath));
        $this->assertFileExists(public_path($project->image_path));

        $lastPath = $project->image_path;

        $this->actingAs($user)->delete("/admin/projects/{$project->slug}")
            ->assertRedirect();

        $this->assertFileDoesNotExist(public_path($lastPath));
    }
}
