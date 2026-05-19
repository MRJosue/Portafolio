<!doctype html>
<html lang="es">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Portafolio profesional de Josue Daniel Cardona, Backend Developer especializado en Laravel, ERP y sistemas administrativos.">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }}</title>
    <script>
      (() => {
        const storedTheme = localStorage.getItem('portfolio-editorial-theme');
        const theme = storedTheme || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
        document.documentElement.dataset.editorialTheme = theme;
      })();
    </script>
    <link rel="stylesheet" href="{{ asset('css/editorial-black.css') }}?v=project-images-3">
  </head>
  <body>
    <canvas id="ember-canvas" class="motion-lines-canvas" aria-hidden="true"></canvas>
    <div class="paper-noise" aria-hidden="true"></div>
    <div class="theme-wash" aria-hidden="true"></div>

    <header class="archive-header">
      <a class="archive-brand" href="{{ route('themes.editorial-black') }}" aria-label="Josue Daniel Cardona">
        <span>Josue Daniel Cardona</span>
        <small>Backend Developer / Laravel ERP</small>
      </a>
      <nav aria-label="Principal">
        <a href="#perfil">Perfil</a>
        <a href="#proyectos">Proyectos</a>
        <button class="theme-toggle" type="button" data-theme-toggle aria-label="Cambiar entre tema claro y oscuro">
          <span data-theme-label>Modo oscuro</span>
        </button>
      </nav>
    </header>

    <main>
      <section class="archive-hero" aria-labelledby="archive-title">
        <div class="hero-index">
          <p>Portfolio 2026</p>
          <p>Atizapan de Zaragoza</p>
          <p>Laravel / ERP Systems</p>
        </div>

        <article class="hero-statement">
          <p class="kicker">Backend Developer / Full Stack ERP</p>
          <h1 id="archive-title">Construyo sistemas administrativos claros, estables y utiles.</h1>
          <p>
            Desarrollador Laravel enfocado en ERP, automatizacion de procesos,
            bases de datos relacionales, reportes y herramientas internas que
            ayudan a operar mejor.
          </p>
          <div class="archive-actions">
            <a class="button primary" href="#proyectos">Ver proyectos</a>
            <a class="button" href="mailto:ingjosue.cardona@gmail.com">Contactar</a>
          </div>
        </article>

        @php
          $hasLightPair = $profileCard['light_a'] && $profileCard['light_b'];
          $hasDarkPair = $profileCard['dark_a'] && $profileCard['dark_b'];
        @endphp
        <aside class="archive-card profile-card" aria-label="Foto de perfil">
          <div class="profile-photo-frame {{ $hasLightPair ? 'has-light-pair' : '' }} {{ $hasDarkPair ? 'has-dark-pair' : '' }}">
            @if ($profileCard['light_a'])
              <img class="profile-photo profile-photo-light profile-photo-a" src="{{ $profileCard['light_a'] }}" alt="Foto de perfil en modo claro A">
            @endif
            @if ($profileCard['light_b'])
              <img class="profile-photo profile-photo-light profile-photo-b" src="{{ $profileCard['light_b'] }}" alt="Foto de perfil en modo claro B">
            @endif
            @if ($profileCard['dark_a'])
              <img class="profile-photo profile-photo-dark profile-photo-a" src="{{ $profileCard['dark_a'] }}" alt="Foto de perfil en modo oscuro A">
            @endif
            @if ($profileCard['dark_b'])
              <img class="profile-photo profile-photo-dark profile-photo-b" src="{{ $profileCard['dark_b'] }}" alt="Foto de perfil en modo oscuro B">
            @endif
            <div class="profile-photo-placeholder" aria-hidden="true">
              <span>JDC</span>
            </div>
          </div>
          <p>{{ $profileCard['quote'] }}</p>
        </aside>
      </section>

      <section class="archive-grid" id="perfil" aria-label="Perfil profesional">
        <article class="archive-card wide">
          <p class="kicker">Perfil profesional</p>
          <h2>Backend con criterio de producto y operacion.</h2>
          <p>
            Convierto requerimientos operativos en modulos claros: permisos,
            reportes, exportaciones y sincronizacion de informacion.
          </p>
        </article>

        <article class="archive-card">
          <span>01</span>
          <h3>ERP interno</h3>
          <p>Pedidos, produccion, clientes, inventario y administracion.</p>
        </article>

        <article class="archive-card stack-trigger-card">
          <span>02</span>
          <h3>Laravel stack</h3>
          <p>Laravel, Livewire, MySQL, APIs y despliegues.</p>
          <button class="text-link card-link" type="button" data-modal-open="stack-modal">Ver mas</button>
        </article>

        <article class="archive-card inverted" id="contact">
          <p class="kicker">Contacto</p>
          <h2>Conversemos sobre tu sistema o vacante backend.</h2>
          <p>
            Abre el asistente y responde las preguntas guiadas para continuar la conversacion.
          </p>
          <button class="button primary contact-chat-button" type="button" data-chat-open>Iniciar chat</button>
        </article>
      </section>

      <section class="portfolio-section" id="proyectos" aria-labelledby="portfolio-title">
        <div class="journal-heading">
          <p class="kicker">Portfolio database</p>
          <h2 id="portfolio-title">Proyectos y casos publicados</h2>
        </div>

        <div class="portfolio-list">
          @forelse ($projects as $project)
            <article class="portfolio-item">
              <a href="{{ route('projects.show', $project) }}" class="project-thumb {{ $project->image_theme }} {{ $project->image_url ? 'has-image' : '' }}" aria-label="Ver {{ $project->title }}">
                @if ($project->image_url)
                  <img src="{{ $project->image_url }}" alt="Foto de {{ $project->title }}">
                @endif
                <span class="card-border-motion"></span>
              </a>
              <div>
                <p class="kicker">{{ $project->service }} / {{ $project->year ?? 'En curso' }}</p>
                <h3><a href="{{ route('projects.show', $project) }}">{{ $project->title }}</a></h3>
                <p>{{ $project->summary }}</p>
                <a class="text-link" href="{{ route('projects.show', $project) }}">Abrir caso</a>
              </div>
            </article>
          @empty
            <article class="archive-card">
              <h3>Aun no hay proyectos.</h3>
              <p>Pronto se agregaran casos con problema, enfoque tecnico y resultado operativo.</p>
            </article>
          @endforelse
        </div>

        <a class="button" href="{{ route('projects.index') }}">Ver todos los proyectos</a>
      </section>
    </main>

    <div class="modal-overlay" data-modal="stack-modal" aria-hidden="true">
      <section class="stack-modal" role="dialog" aria-modal="true" aria-labelledby="stack-modal-title" tabindex="-1">
        <div class="modal-head">
          <div>
            <p class="kicker">Herramientas y forma de trabajo</p>
            <h2 id="stack-modal-title">Tecnico, ordenado y orientado a resultados.</h2>
          </div>
          <button class="modal-close" type="button" data-modal-close aria-label="Cerrar modal">Cerrar</button>
        </div>

        <div class="stack-grid">
          <article class="archive-card">
            <span>Backend</span>
            <p>PHP, Laravel, MySQL, REST APIs y optimizacion SQL.</p>
          </article>
          <article class="archive-card">
            <span>Frontend operativo</span>
            <p>Livewire, Alpine.js, JavaScript, Tailwind CSS y Vite.</p>
          </article>
          <article class="archive-card">
            <span>Entrega</span>
            <p>Git, GitHub, Linux, despliegues y soporte.</p>
          </article>
          <article class="archive-card">
            <span>Colaboracion</span>
            <p>Requerimientos, comunicacion, clientes e ingles conversacional.</p>
          </article>
        </div>
      </section>
    </div>

    <section
      class="chat-dock"
      data-chat
      data-start-url="{{ route('chat.start') }}"
      data-messages-url-template="{{ url('/chat/__SESSION__/messages') }}"
      data-contact-url-template="{{ url('/chat/__SESSION__/contact') }}"
      aria-live="polite"
      aria-hidden="true"
    >
      <div class="chat-panel" role="dialog" aria-modal="false" aria-labelledby="chat-title">
        <header class="chat-head">
          <div>
            <p class="kicker">Contacto directo</p>
            <h2 id="chat-title">Asistente de proyecto</h2>
          </div>
          <button class="chat-close" type="button" data-chat-close aria-label="Cerrar chat">Cerrar</button>
        </header>

        <div class="chat-messages" data-chat-messages></div>

        <form class="chat-reply-form" data-chat-reply-form>
          <label for="chat_body">Mensaje</label>
          <div class="input-row">
            <input id="chat_body" name="body" type="text" placeholder="Escribe tu nombre" autocomplete="off" required>
            <button type="submit">Enviar</button>
          </div>
        </form>
      </div>
    </section>

    <script src="{{ asset('js/signal.js') }}"></script>
    <script src="{{ asset('js/editorial-theme.js') }}?v=theme-labels-1"></script>
    <script src="{{ asset('js/contact-chat.js') }}?v=question-chat-1"></script>
  </body>
</html>
