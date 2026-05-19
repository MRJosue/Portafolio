<?php

namespace Tests\Feature;

use App\Mail\ChatLeadReceived;
use App\Models\ChatSession;
use App\Models\Project;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/editorial-black');
    }

    public function test_atelier_digital_theme_loads(): void
    {
        $response = $this->get('/editorial-black');

        $response->assertStatus(200);
        $response->assertSee('Atelier Digital');
        $response->assertSee('href="#proyectos"', false);
        $response->assertSee('id="proyectos"', false);
    }

    public function test_journal_landing_loads_posts(): void
    {
        $this->artisan('db:seed');

        $this->get('/editorial-black/journal')
            ->assertStatus(200)
            ->assertSee('Journal')
            ->assertSee('Bento columns para un portafolio editorial');
    }

    public function test_newsletter_subscription_is_stored(): void
    {
        $response = $this->post('/newsletter', [
            'email' => 'demo@example.com',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('subscribers', [
            'email' => 'demo@example.com',
            'source' => 'bento-home',
        ]);
    }

    public function test_admin_can_create_a_post(): void
    {
        $user = User::factory()->create([
            'email' => 'ingjosue.cardona@gmail.com',
        ]);

        $response = $this->actingAs($user)->post('/admin/posts', [
            'title' => 'Nuevo pulso editorial',
            'category' => 'Signal Log',
            'reading_time' => 4,
            'excerpt' => 'Una nota creada desde el panel local para validar la gestion de datos.',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('posts', [
            'title' => 'Nuevo pulso editorial',
            'slug' => 'nuevo-pulso-editorial',
        ]);
    }

    public function test_projects_index_and_detail_render_database_content(): void
    {
        $project = Project::create([
            'title' => 'Caso editorial de prueba',
            'slug' => 'caso-editorial-de-prueba',
            'client' => 'Cliente Demo',
            'service' => 'Web / Identidad',
            'year' => 2026,
            'role' => 'Diseno y desarrollo',
            'summary' => 'Resumen lorem para validar que el proyecto aparece en la vista general.',
            'description' => 'Descripcion lorem para validar la vista detallada del proyecto.',
            'image_theme' => 'visual-one',
            'is_featured' => true,
            'status' => 'published',
            'published_at' => now(),
        ]);

        $this->get('/editorial-black/proyectos')
            ->assertStatus(200)
            ->assertSee($project->title);

        $this->get("/editorial-black/proyectos/{$project->slug}")
            ->assertStatus(200)
            ->assertSee('Cliente Demo')
            ->assertSee('Descripcion lorem');
    }

    public function test_admin_can_create_a_project(): void
    {
        $user = User::factory()->create([
            'email' => 'ingjosue.cardona@gmail.com',
        ]);

        $response = $this->actingAs($user)->post('/admin/projects', [
            'title' => 'Proyecto administrado',
            'client' => 'Lorem Studio',
            'service' => 'Web / Portafolio',
            'year' => 2026,
            'role' => 'Diseno y desarrollo',
            'summary' => 'Resumen de prueba para crear un proyecto desde el panel.',
            'description' => 'Descripcion amplia para crear un proyecto desde el panel administrador.',
            'challenge' => 'Reto de prueba.',
            'solution' => 'Solucion de prueba.',
            'results' => 'Resultados de prueba.',
            'technologies' => 'Laravel, Blade, SQLite',
            'image_theme' => 'visual-two',
            'is_featured' => '1',
            'status' => 'published',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('projects', [
            'title' => 'Proyecto administrado',
            'slug' => 'proyecto-administrado',
            'status' => 'published',
        ]);
    }

    public function test_admin_can_update_profile_card_photos(): void
    {
        $user = User::factory()->create([
            'email' => 'ingjosue.cardona@gmail.com',
        ]);

        $response = $this->actingAs($user)->post('/admin/profile-card', [
            'profile_quote' => 'Codigo claro, operaciones tranquilas.',
            'profile_light_a' => UploadedFile::fake()->image('light-a.jpg', 900, 1125),
            'profile_light_b' => UploadedFile::fake()->image('light-b.jpg', 900, 1125),
            'profile_dark_a' => UploadedFile::fake()->image('dark-a.jpg', 900, 1125),
            'profile_dark_b' => UploadedFile::fake()->image('dark-b.jpg', 900, 1125),
        ]);

        $response->assertRedirect();

        $this->assertSame('Codigo claro, operaciones tranquilas.', SiteSetting::getValue('profile_quote'));

        foreach (['profile_light_a', 'profile_light_b', 'profile_dark_a', 'profile_dark_b'] as $key) {
            $path = SiteSetting::getValue($key);
            $this->assertNotNull($path);
            $this->assertStringStartsWith('uploads/profile-card/', $path);
            $this->assertTrue(File::exists(public_path($path)));

            File::delete(public_path($path));
        }
    }

    public function test_contact_chat_collects_lead_and_admin_can_reply(): void
    {
        Mail::fake();

        $start = $this->postJson('/chat/start');
        $start->assertOk()
            ->assertJsonPath('session.status', 'open')
            ->assertJsonPath('session.next_field', 'name');

        $session = $start->json('session');

        $this->postJson("/chat/{$session['id']}/messages", [
            'session_key' => $session['session_key'],
            'body' => 'Cliente Demo',
        ])->assertOk()
            ->assertJsonPath('session.next_field', 'email');

        $this->postJson("/chat/{$session['id']}/messages", [
            'session_key' => $session['session_key'],
            'body' => 'cliente@example.com',
        ])->assertOk()
            ->assertJsonPath('session.next_field', 'phone');

        $this->postJson("/chat/{$session['id']}/messages", [
            'session_key' => $session['session_key'],
            'body' => '5551234567',
        ])->assertOk()
            ->assertJsonPath('session.next_field', 'topic');

        $this->postJson("/chat/{$session['id']}/messages", [
            'session_key' => $session['session_key'],
            'body' => 'ERP interno',
        ])->assertOk()
            ->assertJsonPath('session.status', 'lead')
            ->assertJsonPath('session.next_field', null);

        $this->assertDatabaseHas('chat_sessions', [
            'name' => 'Cliente Demo',
            'email' => 'cliente@example.com',
            'topic' => 'ERP interno',
        ]);

        Mail::assertSent(ChatLeadReceived::class, function (ChatLeadReceived $mail) {
            return $mail->chatSession->email === 'cliente@example.com'
                && $mail->chatSession->topic === 'ERP interno';
        });

        $user = User::factory()->create();

        $this->actingAs($user)->post("/admin/chat/{$session['id']}/reply", [
            'body' => 'Claro, revisemos el alcance.',
        ])->assertRedirect();

        $this->assertDatabaseHas('chat_messages', [
            'sender' => 'admin',
            'body' => 'Claro, revisemos el alcance.',
        ]);
    }

    public function test_admin_can_delete_a_chat_session_with_its_messages(): void
    {
        $chatSession = ChatSession::create([
            'session_key' => 'delete-me',
            'name' => 'Cliente Demo',
            'email' => 'cliente@example.com',
            'status' => 'lead',
            'last_message_at' => now(),
        ]);

        $chatSession->messages()->create([
            'sender' => 'visitor',
            'body' => 'Quiero borrar esta conversacion.',
        ]);

        $user = User::factory()->create();

        $this->actingAs($user)
            ->delete("/admin/chat/{$chatSession->id}")
            ->assertRedirect()
            ->assertSessionHas('admin_status', 'Chat eliminado correctamente.');

        $this->assertDatabaseMissing('chat_sessions', [
            'id' => $chatSession->id,
        ]);

        $this->assertDatabaseMissing('chat_messages', [
            'chat_session_id' => $chatSession->id,
        ]);
    }

    public function test_admin_requires_authentication(): void
    {
        $this->get('/admin')->assertRedirect('/login');
        $this->post('/admin/posts', [])->assertRedirect('/login');
        $this->post('/admin/projects', [])->assertRedirect('/login');
    }

    public function test_registration_is_disabled(): void
    {
        $this->get('/register')->assertStatus(404);
        $this->post('/register', [])->assertStatus(404);
    }
}
