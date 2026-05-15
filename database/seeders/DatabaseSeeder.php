<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $adminEmail = 'ingjosue.cardona@gmail.com';

        User::query()
            ->where('email', '!=', $adminEmail)
            ->delete();

        User::updateOrCreate(
            ['email' => $adminEmail],
            [
                'name' => 'Josue Cardona',
                'password' => Hash::make(env('ADMIN_PASSWORD', 'password123')),
                'email_verified_at' => now(),
            ],
        );

        $posts = [
            [
                'title' => 'Bento columns para un portafolio editorial',
                'slug' => 'bento-columns-portafolio-editorial',
                'category' => 'Design System',
                'excerpt' => 'Una estructura por columnas ayuda a mezclar identidad, casos, bitacora y conversion sin hacer una landing tradicional.',
                'reading_time' => 4,
                'status' => 'published',
                'published_at' => now()->subDays(2),
            ],
            [
                'title' => 'Cafe, beige y luz ladrillo sin perder contraste',
                'slug' => 'cafe-beige-luz-ladrillo-contraste',
                'category' => 'Visual Notes',
                'excerpt' => 'La paleta terrosa funciona mejor cuando el brillo rojo aparece como senal de interfaz, no como fondo permanente.',
                'reading_time' => 3,
                'status' => 'published',
                'published_at' => now()->subDays(6),
            ],
            [
                'title' => 'Laravel como motor para blog y newsletter',
                'slug' => 'laravel-motor-blog-newsletter',
                'category' => 'Stack',
                'excerpt' => 'SQLite permite prototipar localmente con migraciones, seeds y datos reales antes de conectar MySQL o panel de administracion completo.',
                'reading_time' => 5,
                'status' => 'published',
                'published_at' => now()->subDays(10),
            ],
        ];

        foreach ($posts as $post) {
            Post::updateOrCreate(['slug' => $post['slug']], $post);
        }

        $projects = [
            [
                'title' => 'Editorial Black Portfolio System',
                'slug' => 'editorial-black-portfolio-system',
                'client' => 'Lorem Studio',
                'service' => 'Web / Portafolio',
                'year' => 2026,
                'role' => 'Direccion visual y desarrollo Laravel',
                'summary' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Un sistema editorial para organizar proyectos, servicios y notas con una presencia sobria.',
                'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Curabitur sed ligula a justo porta faucibus. Integer vitae urna non justo cursus feugiat. El proyecto estructura un portafolio como archivo editorial, con jerarquias claras y contenido administrable desde base de datos.',
                'challenge' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. La informacion estaba dispersa y necesitaba una narrativa mas clara para explicar valor, alcance y resultados.',
                'solution' => 'Se creo un sistema de fichas, detalles y vistas generales con Blade, Laravel y SQLite para publicar trabajos sin editar codigo.',
                'results' => 'La experiencia queda lista para crecer con nuevos casos, mantener coherencia visual y presentar el contenido con mas autoridad.',
                'technologies' => ['Laravel', 'Blade', 'SQLite', 'CSS'],
                'image_theme' => 'visual-one',
                'is_featured' => true,
                'status' => 'published',
                'published_at' => now()->subDay(),
            ],
            [
                'title' => 'Signal Commerce Refresh',
                'slug' => 'signal-commerce-refresh',
                'client' => 'Acme Market',
                'service' => 'Ecommerce / UX',
                'year' => 2025,
                'role' => 'UX, interfaz y front-end',
                'summary' => 'Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium para una tienda mas legible.',
                'description' => 'Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos.',
                'challenge' => 'La tienda tenia categorias extensas, decisiones lentas y poca diferencia visual entre productos prioritarios y secundarios.',
                'solution' => 'Se reorganizo la lectura de catalogo, filtros y fichas de producto con una interfaz mas directa y enfocada en comparacion.',
                'results' => 'El contenido quedo mas escaneable y preparado para campanas con landing pages especificas.',
                'technologies' => ['Laravel', 'Tailwind', 'Checkout UX'],
                'image_theme' => 'visual-two',
                'is_featured' => true,
                'status' => 'published',
                'published_at' => now()->subDays(4),
            ],
            [
                'title' => 'Northline Services Dossier',
                'slug' => 'northline-services-dossier',
                'client' => 'Northline',
                'service' => 'Identidad / Landing',
                'year' => 2024,
                'role' => 'Estrategia, copy y desarrollo',
                'summary' => 'Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam para una marca de servicios B2B.',
                'description' => 'Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.',
                'challenge' => 'La marca necesitaba sonar mas confiable, explicar sus servicios sin saturar y guiar a prospectos hacia contacto.',
                'solution' => 'Se construyo una pagina editorial con bloques de evidencia, servicios claros y llamados de accion sobrios.',
                'results' => 'La comunicacion quedo mas compacta, profesional y facil de adaptar para nuevas lineas de servicio.',
                'technologies' => ['Blade', 'CSS', 'Content Strategy'],
                'image_theme' => 'visual-three',
                'is_featured' => false,
                'status' => 'published',
                'published_at' => now()->subDays(9),
            ],
        ];

        foreach ($projects as $project) {
            Project::updateOrCreate(['slug' => $project['slug']], $project);
        }
    }
}
