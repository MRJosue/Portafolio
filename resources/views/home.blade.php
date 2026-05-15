<!doctype html>
<html lang="es">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Portafolio creativo serio con proyectos, archivo editorial y newsletter en Laravel.">
    <title>{{ config('app.name') }}</title>
    <script>
      (() => {
        const storedTheme = localStorage.getItem('portfolio-signal-theme');
        const preferredTheme = window.matchMedia('(prefers-color-scheme: light)').matches ? 'light' : 'dark';
        document.documentElement.dataset.signalTheme = storedTheme || preferredTheme;
      })();
    </script>
    <link rel="stylesheet" href="{{ asset('css/signal.css') }}?v=site-theme-1">
  </head>
  <body>
    <canvas id="ember-canvas" class="motion-lines-canvas" aria-hidden="true"></canvas>
    <div class="grain" aria-hidden="true"></div>

    <header class="site-header">
      <a class="brand" href="{{ route('home') }}" aria-label="Inicio">
        <span class="brand-mark"></span>
        <span>Atelier Digital</span>
      </a>
      <nav aria-label="Principal">
        <a href="#projects">Proyectos</a>
        <a href="#journal">Archivo</a>
        <a href="#contact">Contacto</a>
        <a href="{{ route('admin.index') }}">Admin</a>
        <button class="site-theme-toggle" type="button" data-site-theme-toggle aria-label="Cambiar modo claro u oscuro">
          <span data-site-theme-label>Modo claro</span>
        </button>
      </nav>
    </header>

    <main>
      <section class="hero" aria-labelledby="hero-title">
        <div class="hero-copy">
          <p class="eyebrow">Portfolio libre / direccion visual / web</p>
          <h1 id="hero-title">Un portafolio serio para una mente creativa.</h1>
          <p>
            Una presencia digital con espacio para mostrar proyectos, pensamiento y proceso.
            Menos plantilla, mas criterio. Menos barreras, mas obra.
          </p>
        </div>

        <aside class="hero-note" aria-label="Declaracion">
          <span>Manifiesto breve</span>
          <p>
            El sitio no debe sentirse como una vitrina generica. Debe sentirse como entrar
            a un estudio: ordenado, vivo, personal y listo para vender.
          </p>
        </aside>
      </section>

      <section class="intro-strip" aria-label="Enfoque">
        <p>Diseño con estructura.</p>
        <p>Contenido con voz.</p>
        <p>Proyectos con presencia.</p>
      </section>

      <section class="projects" id="projects" aria-labelledby="projects-title">
        <div class="section-heading">
          <p class="eyebrow">Selected work</p>
          <h2 id="projects-title">Proyectos como piezas, no como tarjetas.</h2>
        </div>

        <article class="project-row">
          <div class="project-visual visual-one" aria-hidden="true"><span class="card-border-motion"></span></div>
          <div class="project-copy">
            <span>01 / Web presence</span>
            <h3>Sitios con identidad propia</h3>
            <p>
              Portafolios, landing pages y experiencias web que se sienten sobrias,
              memorables y hechas para explicar valor sin perder atmosfera.
            </p>
          </div>
        </article>

        <article class="project-row reverse">
          <div class="project-visual visual-two" aria-hidden="true"><span class="card-border-motion"></span></div>
          <div class="project-copy">
            <span>02 / Visual systems</span>
            <h3>Lenguaje visual para marcas y productos</h3>
            <p>
              Paletas, composicion, modulos de interfaz y direccion grafica para
              construir una presencia consistente en pantallas, documentos y contenido.
            </p>
          </div>
        </article>

        <article class="project-row">
          <div class="project-visual visual-three" aria-hidden="true"><span class="card-border-motion"></span></div>
          <div class="project-copy">
            <span>03 / Editorial engine</span>
            <h3>Blog y newsletter como archivo vivo</h3>
            <p>
              Una base Laravel para publicar ideas, avances, notas de proceso y textos
              que ayuden a vender desde la confianza y el criterio.
            </p>
          </div>
        </article>
      </section>

      <section class="journal" id="journal" aria-labelledby="journal-title">
        <div class="section-heading">
          <p class="eyebrow">Notes / process / archive</p>
          <h2 id="journal-title">Pensamiento visible.</h2>
        </div>

        <div class="journal-list">
          @forelse ($posts as $post)
            <article class="journal-item">
              <time datetime="{{ optional($post->published_at)->toDateString() }}">
                {{ optional($post->published_at)->translatedFormat('d M Y') ?? 'Draft' }}
              </time>
              <div>
                <p>{{ $post->category }} / {{ $post->reading_time }} min</p>
                <h3>{{ $post->title }}</h3>
              </div>
              <p>{{ $post->excerpt }}</p>
            </article>
          @empty
            <article class="journal-item">
              <h3>Aun no hay notas.</h3>
              <p>Entra al panel Admin y crea el primer registro.</p>
            </article>
          @endforelse
        </div>
      </section>

      <section class="contact" id="contact" aria-labelledby="contact-title">
        <div>
          <p class="eyebrow">Open studio</p>
          <h2 id="contact-title">Si algo resuena, abrimos conversacion.</h2>
          <p>
            Guarda tu correo para recibir notas o iniciar una linea de contacto
            alrededor de proyectos, identidad visual y presencia web.
          </p>
        </div>

        <form action="{{ route('newsletter.store') }}" method="POST">
          @csrf
          <label for="email">Email</label>
          <div class="input-row">
            <input id="email" name="email" type="email" value="{{ old('email') }}" placeholder="tu@email.com" required>
            <button type="submit">Enviar</button>
          </div>
          @error('email')
            <p class="status error">{{ $message }}</p>
          @enderror
          @if (session('newsletter_status'))
            <p class="status">{{ session('newsletter_status') }}</p>
          @endif
        </form>
      </section>
    </main>

    <footer class="site-footer">
      <a href="{{ route('themes.editorial-black') }}">Editorial Black Archive</a>
      <a href="{{ route('themes.neo-industrial') }}">Neo-Industrial Portfolio</a>
    </footer>

    <script src="{{ asset('js/signal.js') }}"></script>
  </body>
</html>
