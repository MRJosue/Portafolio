<!doctype html>
<html lang="es">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Journal simple con notas publicadas desde Laravel.">
    <title>Journal / {{ config('app.name') }}</title>
    <script>
      (() => {
        const storedTheme = localStorage.getItem('portfolio-editorial-theme');
        const theme = storedTheme || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
        document.documentElement.dataset.editorialTheme = theme;
      })();
    </script>
    <link rel="stylesheet" href="{{ asset('css/editorial-black.css') }}?v=project-images-4">
  </head>
  <body class="journal-page">
    <canvas id="ember-canvas" class="motion-lines-canvas" aria-hidden="true"></canvas>
    <div class="paper-noise" aria-hidden="true"></div>
    <div class="theme-wash" aria-hidden="true"></div>

    <header class="archive-header">
      <a class="archive-brand" href="{{ route('themes.editorial-black') }}" aria-label="Atelier Digital">
        <span>Atelier Digital</span>
        <small>Journal / notas</small>
      </a>
      <nav aria-label="Principal">
        <a href="{{ route('themes.editorial-black') }}">Inicio</a>
        <a href="{{ route('themes.editorial-black') }}#proyectos">Proyectos</a>
        <a href="{{ route('admin.index') }}">Admin</a>
        <button class="theme-toggle" type="button" data-theme-toggle aria-label="Cambiar entre tema claro y oscuro">
          <span data-theme-label>Modo oscuro</span>
        </button>
      </nav>
    </header>

    <main>
      <section class="simple-journal-hero" aria-labelledby="journal-title">
        <p class="kicker">Archive notes</p>
        <h1 id="journal-title">Journal</h1>
        <p>Notas cortas de proceso, criterio visual y decisiones de construccion publicadas desde la base de datos.</p>
      </section>

      <section class="simple-journal-list" aria-label="Notas del journal">
        @forelse ($posts as $post)
          <article class="simple-journal-item">
            <time datetime="{{ optional($post->published_at)->toDateString() }}">
              {{ optional($post->published_at)->translatedFormat('d M Y') ?? 'Draft' }}
            </time>
            <div>
              <p class="kicker">{{ $post->category }} / {{ $post->reading_time }} min</p>
              <h2>{{ $post->title }}</h2>
              <p>{{ $post->excerpt }}</p>
            </div>
          </article>
        @empty
          <article class="simple-journal-item">
            <div>
              <h2>Aun no hay notas.</h2>
              <p>Entra al panel Admin y crea el primer registro.</p>
            </div>
          </article>
        @endforelse
      </section>
    </main>

    <script src="{{ asset('js/signal.js') }}"></script>
    <script src="{{ asset('js/editorial-theme.js') }}?v=theme-labels-1"></script>
  </body>
</html>
