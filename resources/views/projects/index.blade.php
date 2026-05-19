<!doctype html>
<html lang="es">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Vista general de proyectos y trabajos seleccionados.">
    <title>Proyectos / {{ config('app.name') }}</title>
    <script>
      (() => {
        const storedTheme = localStorage.getItem('portfolio-editorial-theme');
        const theme = storedTheme || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
        document.documentElement.dataset.editorialTheme = theme;
      })();
    </script>
    <link rel="stylesheet" href="{{ asset('css/editorial-black.css') }}?v=project-images-11">
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
      <section class="page-hero" aria-labelledby="projects-title">
        <p class="kicker">Selected work</p>
        <h1 id="projects-title">Archivo general de proyectos.</h1>
        <p>
          Una vista completa de trabajos, casos y piezas de portafolio guardadas desde la base de datos.
        </p>
      </section>

      <section class="project-index" aria-label="Todos los proyectos">
        @forelse ($projects as $project)
          <article class="project-card">
            @if ($project->image_url)
              <a
                href="{{ route('projects.show', $project) }}"
                class="project-image-frame"
                style="background-image: url('{{ $project->image_url }}'); background-position: center; background-repeat: no-repeat; background-size: contain; display: block; min-height: clamp(240px, 30vw, 430px); width: 100%;"
                role="img"
                aria-label="Foto de {{ $project->title }}"
              ></a>
            @else
              <a href="{{ route('projects.show', $project) }}" class="project-thumb {{ $project->image_theme }}" aria-label="Ver {{ $project->title }}">
                <span class="card-border-motion"></span>
              </a>
            @endif
            <div class="project-card-copy">
              <p class="kicker">{{ $project->service }} / {{ $project->year ?? 'En curso' }}</p>
              <h2><a href="{{ route('projects.show', $project) }}">{{ $project->title }}</a></h2>
              <p>{{ $project->summary }}</p>
              <a class="text-link" href="{{ route('projects.show', $project) }}">Ver detalle</a>
            </div>
          </article>
        @empty
          <article class="archive-card">
            <h2>Aun no hay proyectos.</h2>
            <p>Entra al panel Admin y crea el primer proyecto del portafolio.</p>
          </article>
        @endforelse
      </section>
    </main>

    <script src="{{ asset('js/signal.js') }}"></script>
    <script src="{{ asset('js/editorial-theme.js') }}?v=theme-labels-1"></script>
  </body>
</html>
