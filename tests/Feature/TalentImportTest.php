<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TalentImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_import_and_save_a_talent_cv(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $cv = UploadedFile::fake()->createWithContent('josue-cardona.txt', implode("\n", [
            'Josue Cardona',
            'Software Engineer',
            'josue@example.com',
            '+52 555 123 4567',
            'Resumen',
            'Desarrollador Laravel con enfoque en productos internos.',
            'Experiencia',
            'Senior Developer',
            'Warm Studio',
            '2024 - presente',
            'Construccion de herramientas administrativas.',
            'Educacion',
            'Ingenieria en Sistemas',
            'Universidad Demo',
            '2018 - 2022',
            'Habilidades',
            'Laravel, PHP, SQLite',
            'Idiomas',
            'Espanol, Ingles',
        ]));

        $this->actingAs($user)
            ->post('/talents/import', ['cv_document' => $cv])
            ->assertRedirect('/talents')
            ->assertSessionHas('talent_cv_import');

        $this->actingAs($user)
            ->post('/talents', [
                'full_name' => 'Josue Cardona',
                'email' => 'josue@example.com',
                'phone' => '+52 555 123 4567',
                'location' => 'Mexico',
                'headline' => 'Software Engineer',
                'summary' => 'Desarrollador Laravel con enfoque en productos internos.',
                'skills_text' => "Laravel\nPHP\nSQLite",
                'languages_text' => "Espanol\nIngles",
                'links_text' => 'https://example.com',
                'experiences_text' => "Senior Developer\nWarm Studio\n2024 - presente\nConstruccion de herramientas administrativas.",
                'educations_text' => "Ingenieria en Sistemas\nUniversidad Demo\n2018 - 2022",
                'status' => 'active',
            ])
            ->assertRedirect('/talents')
            ->assertSessionMissing('talent_cv_import');

        $this->assertDatabaseHas('talents', [
            'full_name' => 'Josue Cardona',
            'email' => 'josue@example.com',
            'headline' => 'Software Engineer',
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('cv_experiences', [
            'role' => 'Senior Developer',
            'company' => 'Warm Studio',
        ]);

        $this->assertDatabaseHas('cv_educations', [
            'degree' => 'Ingenieria en Sistemas',
            'institution' => 'Universidad Demo',
        ]);

        $this->assertDatabaseHas('cv_documents', [
            'original_name' => 'josue-cardona.txt',
        ]);
    }

    public function test_talents_require_authentication(): void
    {
        $this->get('/talents')->assertRedirect('/login');
        $this->post('/talents', [])->assertRedirect('/login');
    }
}
