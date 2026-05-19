<!doctype html>
<html lang="es">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="{{ $project->summary }}">
    <title>{{ $project->title }} / {{ config('app.name') }}</title>
    <script>
      (() => {
        const storedTheme = localStorage.getItem('portfolio-editorial-theme');
        const theme = storedTheme || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
        document.documentElement.dataset.editorialTheme = theme;
      })();
    </script>
    <link rel="stylesheet" href="{{ asset('css/editorial-black.css') }}?v=project-image-frames-2">
  </head>
  <body>
    <canvas id="ember-canvas" class="motion-lines-canvas" aria-hidden="true"></canvas>
    <div class="paper-noise" aria-hidden="true"></div>
    <div class="theme-wash" aria-hidden="true"></div>

    <header class="archive-header">
      <a class="archive-brand" href="{{ route('themes.editorial-black') }}" aria-label="Atelier Digital">
        <span>Atelier Digital</span>
        <small>Portfolio dossier / 2026</small>
      </a>
      <nav aria-label="Principal">
        <a href="{{ route('themes.editorial-black') }}">Inicio</a>
        <a href="{{ route('projects.index') }}">Proyectos</a>
        <a href="{{ route('admin.index') }}">Admin</a>
        <button class="theme-toggle" type="button" data-theme-toggle aria-label="Cambiar entre tema claro y oscuro">
          <span data-theme-label>Modo oscuro</span>
        </button>
      </nav>
    </header>

    <main>
      <section class="case-hero">
        <div>
          <p class="kicker">{{ $project->service }} / {{ $project->year ?? 'En curso' }}</p>
          <h1>{{ $project->title }}</h1>
          <p>{{ $project->summary }}</p>
        </div>
        <aside class="case-meta">
          <span>Cliente</span>
          <strong>{{ $project->client ?? 'Proyecto interno' }}</strong>
          <span>Rol</span>
          <strong>{{ $project->role ?? 'Direccion creativa y desarrollo' }}</strong>
        </aside>
      </section>

      @if ($project->image_url)
        <section class="case-image-panel" aria-label="Imagen del proyecto">
          <figure class="case-image-frame">
            <img src="{{ $project->image_url }}" alt="Foto de {{ $project->title }}">
          </figure>
        </section>
      @else
        <div class="case-visual {{ $project->image_theme }}" aria-hidden="true">
          <span class="card-border-motion"></span>
        </div>
      @endif

      <section class="case-body">
        <article>
          <p class="kicker">Contexto</p>
          <p>{{ $project->description }}</p>
        </article>
        <article>
          <p class="kicker">Reto</p>
          <p>{{ $project->challenge ?: 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer pretium, nibh vel porta luctus, arcu sapien gravida nisl, at luctus arcu mi vitae lorem.' }}</p>
        </article>
        <article>
          <p class="kicker">Solucion</p>
          <p>{{ $project->solution ?: 'Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam.' }}</p>
        </article>
        <article>
          <p class="kicker">Resultados</p>
          <p>{{ $project->results ?: 'Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur.' }}</p>
        </article>
      </section>

      @if (! empty($project->technologies))
        <section class="case-tags" aria-label="Tecnologias">
          @foreach ($project->technologies as $technology)
            <span>{{ $technology }}</span>
          @endforeach
        </section>
      @endif

      @if ($relatedProjects->isNotEmpty())
        <section class="related-projects" aria-labelledby="related-title">
          <p class="kicker">Mas trabajos</p>
          <h2 id="related-title">Tambien en archivo.</h2>
          <div class="related-grid">
            @foreach ($relatedProjects as $relatedProject)
              <a class="archive-card" href="{{ route('projects.show', $relatedProject) }}">
                <span>{{ $relatedProject->service }}</span>
                <h3>{{ $relatedProject->title }}</h3>
                <p>{{ $relatedProject->summary }}</p>
              </a>
            @endforeach
          </div>
        </section>
      @endif
    </main>

    <script src="{{ asset('js/signal.js') }}"></script>
    <script src="{{ asset('js/editorial-theme.js') }}?v=theme-labels-1"></script>
  </body>
</html>
