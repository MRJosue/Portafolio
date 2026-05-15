<!doctype html>
<html lang="es">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Variante Neo-Industrial Portfolio para portafolio con blog y newsletter en Laravel.">
    <title>Neo-Industrial Portfolio / {{ config('app.name') }}</title>
    <link rel="stylesheet" href="{{ asset('css/neo-industrial.css') }}?v=open-border-8">
  </head>
  <body>
    <canvas id="ember-canvas" class="motion-lines-canvas" aria-hidden="true"></canvas>
    <div class="grid-noise" aria-hidden="true"></div>

    <header class="industrial-header">
      <a class="brand" href="{{ route('themes.neo-industrial') }}" aria-label="Neo-Industrial Portfolio">
        <span class="brand-code">NIP</span>
        <span>Neo-Industrial Portfolio</span>
      </a>
      <nav aria-label="Principal">
        <a href="{{ route('home') }}">Warm</a>
        <a href="{{ route('themes.editorial-black') }}">Archive</a>
        <a href="#capabilities">Capabilities</a>
        <a href="{{ route('admin.index') }}">Admin</a>
      </nav>
    </header>

    <main>
      <section class="industrial-hero" aria-labelledby="industrial-title">
        <article class="command-panel">
          <div class="panel-meta">
            <span>Portfolio system</span>
            <span>Status / operational</span>
          </div>
          <p class="eyebrow">Industrial design / Web operations / Visual systems</p>
          <h1 id="industrial-title">Un portafolio construido como herramienta de impacto.</h1>
          <p>
            Una propuesta mas agresiva, modular y tecnica. Pensada para vender capacidad,
            precision y ejecucion en proyectos digitales con personalidad fuerte.
          </p>
          <div class="actions">
            <a class="button primary" href="#capabilities">Ver capacidades</a>
            <a class="button" href="#contact">Activar contacto</a>
          </div>
        </article>

        <aside class="status-stack" aria-label="Indicadores">
          <article>
            <span>Conversion path</span>
            <strong>03 STEPS</strong>
          </article>
          <article>
            <span>Visual tone</span>
            <strong>HARD / CLEAR</strong>
          </article>
          <article>
            <span>Content engine</span>
            <strong>Laravel DB</strong>
          </article>
        </aside>
      </section>

      <section class="capability-grid" id="capabilities" aria-labelledby="capabilities-title">
        <article class="capability lead">
          <p class="eyebrow">Commercial objective</p>
          <h2 id="capabilities-title">Que el visitante entienda rapido que sabes resolver.</h2>
          <p>
            Este estilo favorece servicios tecnicos, interfaces, automatizacion,
            branding digital y proyectos donde la ejecucion importa tanto como la estetica.
          </p>
        </article>

        <article class="capability">
          <span>01</span>
          <h3>Interface builds</h3>
          <p>Webs, dashboards, herramientas internas y prototipos con lenguaje funcional.</p>
        </article>

        <article class="capability">
          <span>02</span>
          <h3>Brand systems</h3>
          <p>Identidad aplicada a pantallas, componentes, documentos y flujos comerciales.</p>
        </article>

        <article class="capability">
          <span>03</span>
          <h3>Signal content</h3>
          <p>Blog y newsletter como prueba de criterio, proceso y constancia.</p>
        </article>

        <article class="capability contact" id="contact">
          <p class="eyebrow">Lead capture</p>
          <h2>Convierte interes en una entrada de sistema.</h2>
          <form action="{{ route('newsletter.store') }}" method="POST">
            @csrf
            <label for="email">Email</label>
            <div class="input-row">
              <input id="email" name="email" type="email" value="{{ old('email') }}" placeholder="tu@email.com" required>
              <button type="submit">Join</button>
            </div>
            @error('email')
              <p class="status error">{{ $message }}</p>
            @enderror
            @if (session('newsletter_status'))
              <p class="status">{{ session('newsletter_status') }}</p>
            @endif
          </form>
        </article>
      </section>

      <section class="log-table" aria-labelledby="log-title">
        <div class="table-head">
          <p class="eyebrow">Signal log</p>
          <h2 id="log-title">Registros desde la base</h2>
        </div>

        <div class="records">
          @forelse ($posts as $post)
            <article class="record">
              <time datetime="{{ optional($post->published_at)->toDateString() }}">
                {{ optional($post->published_at)->format('Y.m.d') ?? 'Draft' }}
              </time>
              <strong>{{ $post->category }}</strong>
              <h3>{{ $post->title }}</h3>
              <p>{{ $post->excerpt }}</p>
            </article>
          @empty
            <article class="record">
              <h3>Sin registros.</h3>
              <p>Entra al panel Admin y crea el primer post.</p>
            </article>
          @endforelse
        </div>
      </section>
    </main>
    <script src="{{ asset('js/signal.js') }}"></script>
  </body>
</html>
