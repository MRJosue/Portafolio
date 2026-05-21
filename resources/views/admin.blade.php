@php
  $money = fn ($value) => '$'.number_format((float) $value, 2);
@endphp

<!doctype html>
<html lang="es">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin / {{ config('app.name') }}</title>
    <link rel="stylesheet" href="{{ asset('css/signal.css') }}?v=admin-sections-theme-1">
  </head>
  <body class="admin-body" data-admin-theme>
    <canvas id="ember-canvas" class="motion-lines-canvas" aria-hidden="true"></canvas>
    <div class="grain" aria-hidden="true"></div>

    <div class="admin-shell">
      <aside class="admin-aside" aria-label="Secciones del admin">
        <a class="brand admin-brand" href="{{ route('home') }}">
          <span class="brand-mark"></span>
          <span>Warm Bento Studio</span>
        </a>

        <nav class="admin-side-nav" aria-label="Paginas del admin">
          <a href="#overview" data-admin-link="overview">Resumen</a>
          <a href="{{ route('talents.index') }}">Talentos</a>
          <a href="#projects" data-admin-link="projects">Proyectos</a>
          <a href="#profile-card" data-admin-link="profile-card">Perfil</a>
          <a href="{{ route('admin.finances.calendar') }}">Finanzas</a>
          <a href="#posts" data-admin-link="posts">Posts</a>
          <a href="#chats" data-admin-link="chats">Chats</a>
          <a href="#subscribers" data-admin-link="subscribers">Suscriptores</a>
        </nav>

        <div class="admin-aside-actions">
          <button class="theme-toggle" type="button" data-theme-toggle aria-label="Cambiar modo claro u oscuro">
            <span data-theme-label>Modo claro</span>
          </button>
          <a href="{{ route('projects.index') }}">Vista publica</a>
          <a href="{{ route('home') }}">Home</a>
        </div>
      </aside>

      <main class="admin-main">
        <header class="admin-top">
          <div>
            <p class="eyebrow">DATABASE CONTROL</p>
            <h1>Panel local</h1>
            <p>Administra proyectos del portafolio, entradas para el blog/newsletter, chats y suscriptores guardados en SQLite.</p>
          </div>

          @if (session('admin_status'))
            <p class="status">{{ session('admin_status') }}</p>
          @endif
        </header>

        <section class="admin-page is-active" id="overview" data-admin-page="overview">
          <div class="admin-section-header">
            <p class="eyebrow">RESUMEN</p>
            <h2>Actividad general</h2>
          </div>

          <div class="admin-stats">
            <article>
              <span>Proyectos</span>
              <strong>{{ $projects->count() }}</strong>
            </article>
            <article>
              <span>Posts</span>
              <strong>{{ $posts->count() }}</strong>
            </article>
            <article>
              <span>Chats</span>
              <strong>{{ $chatSessions->count() }}</strong>
            </article>
            <article>
              <span>Suscriptores</span>
              <strong>{{ $subscribers->count() }}</strong>
            </article>
          </div>

          <div class="admin-panel">
            <p class="eyebrow">ATAJOS</p>
            <div class="admin-quick-grid">
              <a href="#projects" data-admin-link="projects">Crear proyecto</a>
              <a href="{{ route('talents.index') }}">Crear talento</a>
              <a href="#profile-card" data-admin-link="profile-card">Actualizar fotos</a>
              <a href="{{ route('admin.finances.calendar') }}">Registrar finanzas</a>
              <a href="#posts" data-admin-link="posts">Publicar post</a>
              <a href="#chats" data-admin-link="chats">Revisar chats</a>
            </div>
          </div>

          <div class="admin-panel admin-finance-dashboard">
            <div class="admin-section-header finance-section-header">
              <div>
                <p class="eyebrow">FINANZAS</p>
                <h2>Dashboard financiero</h2>
              </div>
              <a class="admin-link-button" href="{{ route('admin.finances.calendar') }}">Abrir calendario</a>
            </div>

            <div class="admin-finance-metrics">
              <article>
                <span>{{ $financeDashboard['fortnight_label'] }}</span>
                <strong class="{{ $financeDashboard['fortnight']['balance'] >= 0 ? 'is-positive' : 'is-negative' }}">{{ $money($financeDashboard['fortnight']['balance']) }}</strong>
                <small>{{ $money($financeDashboard['fortnight']['assets']) }} ingresos / {{ $money($financeDashboard['fortnight']['expenses']) }} gastos</small>
              </article>
              <article>
                <span>{{ $financeDashboard['month_label'] }}</span>
                <strong class="{{ $financeDashboard['monthly']['balance'] >= 0 ? 'is-positive' : 'is-negative' }}">{{ $money($financeDashboard['monthly']['balance']) }}</strong>
                <small>{{ $money($financeDashboard['monthly']['assets']) }} ingresos / {{ $money($financeDashboard['monthly']['expenses']) }} gastos</small>
              </article>
            </div>

            <div class="admin-finance-columns">
              <section>
                <p class="eyebrow">MOVIMIENTOS DEL MES</p>
                <div class="admin-finance-list">
                  @foreach ($financeDashboard['movement_totals'] as $movement)
                    <article>
                      <span>{{ $movement['label'] }}</span>
                      <strong>{{ $money($movement['amount']) }}</strong>
                    </article>
                  @endforeach
                </div>
              </section>

              <section>
                <p class="eyebrow">GASTOS POR TIPO</p>
                <div class="admin-finance-category-list">
                  @forelse ($financeDashboard['expense_categories'] as $category)
                    <article>
                      <div>
                        <strong>{{ $category['name'] }}</strong>
                        <span>Fijo {{ $money($category['fixed_amount']) }} / Variable {{ $money($category['variable_amount']) }}</span>
                      </div>
                      <b>{{ $money($category['total']) }}</b>
                    </article>
                  @empty
                    <article>
                      <div>
                        <strong>Sin gastos activos</strong>
                        <span>Los gastos capturados apareceran aqui por tipo.</span>
                      </div>
                      <b>{{ $money(0) }}</b>
                    </article>
                  @endforelse
                </div>
              </section>
            </div>
          </div>
        </section>

        <section class="admin-page" id="projects" data-admin-page="projects" hidden>
          <div class="admin-section-header">
            <div>
              <p class="eyebrow">PROYECTOS</p>
              <h2>Portafolio</h2>
            </div>
            <div class="admin-tabs" role="tablist" aria-label="Proyectos">
              <button type="button" data-admin-tab="projects-create" class="is-active">Nuevo</button>
              <button type="button" data-admin-tab="projects-list">Guardados</button>
            </div>
          </div>

          <div class="admin-tab-panel is-active" data-admin-tab-panel="projects-create">
            <section class="admin-panel">
              <p class="eyebrow">NUEVO PROYECTO</p>
              <form action="{{ route('admin.projects.store') }}" method="POST" enctype="multipart/form-data" class="admin-form">
                @csrf
                <label for="project_title">Titulo</label>
                <input id="project_title" name="title" type="text" value="{{ old('title') }}" required>
                @error('title') <p class="status error">{{ $message }}</p> @enderror

                <div class="admin-fields">
                  <div>
                    <label for="client">Cliente</label>
                    <input id="client" name="client" type="text" value="{{ old('client', 'Lorem Studio') }}">
                  </div>
                  <div>
                    <label for="service">Servicio</label>
                    <input id="service" name="service" type="text" value="{{ old('service', 'Web / Identidad') }}" required>
                  </div>
                </div>

                <div class="admin-fields">
                  <div>
                    <label for="year">Anio</label>
                    <input id="year" name="year" type="number" min="2000" max="2100" value="{{ old('year', date('Y')) }}">
                  </div>
                  <div>
                    <label for="role">Rol</label>
                    <input id="role" name="role" type="text" value="{{ old('role', 'Diseno y desarrollo') }}">
                  </div>
                </div>

                <label for="summary">Resumen corto</label>
                <textarea id="summary" name="summary" rows="3" required>{{ old('summary', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed una pieza editorial clara para presentar alcance, enfoque y resultado.') }}</textarea>
                @error('summary') <p class="status error">{{ $message }}</p> @enderror

                <label for="description">Descripcion general</label>
                <textarea id="description" name="description" rows="5" required>{{ old('description', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Praesent commodo, massa at facilisis consequat, ligula arcu viverra neque, vitae dignissim justo lectus at nibh. Integer una arquitectura visual sobria para ordenar el contenido y convertir la lectura en una experiencia de confianza.') }}</textarea>
                @error('description') <p class="status error">{{ $message }}</p> @enderror

                <label for="challenge">Reto</label>
                <textarea id="challenge" name="challenge" rows="3">{{ old('challenge', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. El contenido necesitaba jerarquia, ritmo y una lectura mas clara entre servicios, prueba social y conversion.') }}</textarea>

                <label for="solution">Solucion</label>
                <textarea id="solution" name="solution" rows="3">{{ old('solution', 'Se diseno un sistema editorial con bloques modulares, contraste controlado y rutas directas hacia los casos de estudio.') }}</textarea>

                <label for="results">Resultados</label>
                <textarea id="results" name="results" rows="3">{{ old('results', 'El resultado es una experiencia mas legible, preparada para crecer con nuevos proyectos y facil de administrar desde Laravel.') }}</textarea>

                <div class="admin-fields">
                  <div>
                    <label for="technologies">Tecnologias</label>
                    <input id="technologies" name="technologies" type="text" value="{{ old('technologies', 'Laravel, Blade, SQLite') }}">
                  </div>
                  <div>
                    <label for="image_theme">Visual</label>
                    <select id="image_theme" name="image_theme">
                      <option value="visual-one">Visual 01</option>
                      <option value="visual-two">Visual 02</option>
                      <option value="visual-three">Visual 03</option>
                    </select>
                  </div>
                </div>

                <label for="project_image">Foto del proyecto</label>
                <input id="project_image" name="image" type="file" accept="image/*">
                @error('image') <p class="status error">{{ $message }}</p> @enderror

                <div class="admin-fields compact">
                  <label class="check-row">
                    <input name="is_featured" type="checkbox" value="1" checked>
                    Destacado
                  </label>
                  <div>
                    <label for="status">Estado</label>
                    <select id="status" name="status">
                      <option value="published">Publicado</option>
                      <option value="draft">Borrador</option>
                    </select>
                  </div>
                </div>

                <button class="button primary" type="submit">Guardar proyecto</button>
              </form>
            </section>
          </div>

          <div class="admin-tab-panel" data-admin-tab-panel="projects-list" hidden>
            <section class="admin-panel">
              <p class="eyebrow">PROYECTOS GUARDADOS</p>
              <div class="admin-list">
                @forelse ($projects as $project)
                  <article>
                    @if ($project->image_url)
                      <img class="admin-project-thumb" src="{{ $project->image_url }}" alt="Foto de {{ $project->title }}">
                    @else
                      <span class="admin-project-thumb placeholder">Sin foto</span>
                    @endif
                    <strong>{{ $project->title }}</strong>
                    <span>{{ $project->service }} / {{ $project->status }} / {{ optional($project->published_at)->format('d-m-Y') ?? 'sin publicar' }}</span>
                    <form action="{{ route('admin.projects.image.update', $project) }}" method="POST" enctype="multipart/form-data" class="admin-inline-upload">
                      @csrf
                      <label for="project_image_{{ $project->id }}">Foto</label>
                      <input id="project_image_{{ $project->id }}" name="image" type="file" accept="image/*" required>
                      <button type="submit">Guardar foto</button>
                    </form>
                    <div class="admin-actions">
                      <a href="{{ route('projects.show', $project) }}">Ver</a>
                      <form action="{{ route('admin.projects.destroy', $project) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit">Eliminar</button>
                      </form>
                    </div>
                  </article>
                @empty
                  <article>
                    <strong>Sin proyectos aun.</strong>
                    <span>Usa el formulario para crear datos de ejemplo.</span>
                  </article>
                @endforelse
              </div>
            </section>
          </div>
        </section>

        <section class="admin-page" id="profile-card" data-admin-page="profile-card" hidden>
          <div class="admin-section-header">
            <div>
              <p class="eyebrow">PERFIL VISUAL</p>
              <h2>Fotos para la portada</h2>
            </div>
          </div>

          <section class="admin-panel">
            <p class="eyebrow">SUBIR 4 FOTOS</p>
            <form action="{{ route('admin.profile-card.update') }}" method="POST" enctype="multipart/form-data" class="admin-form">
              @csrf

              <label for="profile_quote">Frase inspiradora</label>
              <input id="profile_quote" name="profile_quote" type="text" maxlength="160" value="{{ old('profile_quote', $profileSettings['profile_quote']) }}">
              @error('profile_quote') <p class="status error">{{ $message }}</p> @enderror

              <div class="admin-fields">
                <div>
                  <label for="profile_light_a">Modo claro / Foto A</label>
                  <input id="profile_light_a" name="profile_light_a" type="file" accept="image/*">
                  @error('profile_light_a') <p class="status error">{{ $message }}</p> @enderror
                </div>
                <div>
                  <label for="profile_light_b">Modo claro / Foto B</label>
                  <input id="profile_light_b" name="profile_light_b" type="file" accept="image/*">
                  @error('profile_light_b') <p class="status error">{{ $message }}</p> @enderror
                </div>
              </div>

              <div class="admin-fields">
                <div>
                  <label for="profile_dark_a">Modo oscuro / Foto C</label>
                  <input id="profile_dark_a" name="profile_dark_a" type="file" accept="image/*">
                  @error('profile_dark_a') <p class="status error">{{ $message }}</p> @enderror
                </div>
                <div>
                  <label for="profile_dark_b">Modo oscuro / Foto D</label>
                  <input id="profile_dark_b" name="profile_dark_b" type="file" accept="image/*">
                  @error('profile_dark_b') <p class="status error">{{ $message }}</p> @enderror
                </div>
              </div>

              <button class="button primary" type="submit">Guardar fotos</button>
            </form>
          </section>

          <section class="admin-panel">
            <p class="eyebrow">FOTOS ACTUALES</p>
            <div class="admin-profile-preview-grid">
              @foreach ([
                'profile_light_a' => 'Claro A',
                'profile_light_b' => 'Claro B',
                'profile_dark_a' => 'Oscuro C',
                'profile_dark_b' => 'Oscuro D',
              ] as $settingKey => $label)
                <article>
                  <span>{{ $label }}</span>
                  @if ($profileSettings[$settingKey])
                    <img src="{{ $profileSettings[$settingKey] }}" alt="Foto {{ $label }}">
                  @else
                    <strong>Sin foto</strong>
                  @endif
                </article>
              @endforeach
            </div>
          </section>

          <section class="admin-panel">
            <p class="eyebrow">PALETA PARA IA</p>
            <div class="admin-palette-grid">
              <article><span style="--swatch:#f4efe6"></span><strong>#f4efe6</strong><small>surface claro</small></article>
              <article><span style="--swatch:#ebe1d1"></span><strong>#ebe1d1</strong><small>surface 2 claro</small></article>
              <article><span style="--swatch:#17130e"></span><strong>#17130e</strong><small>texto claro</small></article>
              <article><span style="--swatch:#9d3f31"></span><strong>#9d3f31</strong><small>rojo acento</small></article>
              <article><span style="--swatch:#bf5945"></span><strong>#bf5945</strong><small>rojo luz</small></article>
              <article><span style="--swatch:#050505"></span><strong>#050505</strong><small>surface oscuro</small></article>
              <article><span style="--swatch:#14120f"></span><strong>#14120f</strong><small>panel oscuro</small></article>
              <article><span style="--swatch:#f1eadf"></span><strong>#f1eadf</strong><small>texto oscuro</small></article>
            </div>
          </section>
        </section>

        <section class="admin-page" id="posts" data-admin-page="posts" hidden>
          <div class="admin-section-header">
            <div>
              <p class="eyebrow">POSTS</p>
              <h2>Signal log</h2>
            </div>
            <div class="admin-tabs" role="tablist" aria-label="Posts">
              <button type="button" data-admin-tab="posts-create" class="is-active">Nuevo</button>
              <button type="button" data-admin-tab="posts-list">Publicados</button>
            </div>
          </div>

          <div class="admin-tab-panel is-active" data-admin-tab-panel="posts-create">
            <section class="admin-panel">
              <p class="eyebrow">NUEVO POST</p>
              <form action="{{ route('admin.posts.store') }}" method="POST" class="admin-form">
                @csrf
                <label for="title">Titulo</label>
                <input id="title" name="title" type="text" value="{{ old('title') }}" required>
                @error('title') <p class="status error">{{ $message }}</p> @enderror

                <label for="category">Categoria</label>
                <input id="category" name="category" type="text" value="{{ old('category', 'Signal Log') }}" required>
                @error('category') <p class="status error">{{ $message }}</p> @enderror

                <label for="reading_time">Minutos de lectura</label>
                <input id="reading_time" name="reading_time" type="number" min="1" max="15" value="{{ old('reading_time', 3) }}" required>
                @error('reading_time') <p class="status error">{{ $message }}</p> @enderror

                <label for="excerpt">Resumen</label>
                <textarea id="excerpt" name="excerpt" rows="5" required>{{ old('excerpt') }}</textarea>
                @error('excerpt') <p class="status error">{{ $message }}</p> @enderror

                <button class="button primary" type="submit">Publicar post</button>
              </form>
            </section>
          </div>

          <div class="admin-tab-panel" data-admin-tab-panel="posts-list" hidden>
            <section class="admin-panel">
              <p class="eyebrow">POSTS PUBLICADOS</p>
              <div class="admin-list">
                @forelse ($posts as $post)
                  <article>
                    <strong>{{ $post->title }}</strong>
                    <span>{{ $post->category }} / {{ $post->created_at->format('d-m-Y') }}</span>
                  </article>
                @empty
                  <article>
                    <strong>Sin posts aun.</strong>
                    <span>Publica el primer registro desde la pestana Nuevo.</span>
                  </article>
                @endforelse
              </div>
            </section>
          </div>
        </section>

        <section class="admin-page" id="chats" data-admin-page="chats" hidden>
          <div class="admin-section-header">
            <p class="eyebrow">CHATS</p>
            <h2>Conversaciones de clientes</h2>
          </div>

          <section class="admin-panel admin-chat-panel">
            <div class="admin-chat-list">
              @forelse ($chatSessions as $chatSession)
                <article class="admin-chat-card">
                  <div class="admin-chat-meta">
                    <strong>{{ $chatSession->name ?? 'Visitante sin nombre' }}</strong>
                    <span>{{ $chatSession->email ?? 'Sin email' }} / {{ $chatSession->status }} / {{ optional($chatSession->last_message_at ?? $chatSession->created_at)->format('d-m-Y H:i') }}</span>
                    @if ($chatSession->phone || $chatSession->topic)
                      <span>{{ $chatSession->phone ?? 'sin telefono' }} / {{ $chatSession->topic ?? 'sin tema' }}</span>
                    @endif
                  </div>

                  <div class="admin-actions">
                    <form action="{{ route('admin.chat.destroy', $chatSession) }}" method="POST" onsubmit="return confirm('Eliminar este chat y todos sus mensajes?');">
                      @csrf
                      @method('DELETE')
                      <button type="submit">Eliminar chat</button>
                    </form>
                  </div>

                  <div class="admin-chat-thread">
                    @foreach ($chatSession->messages as $message)
                      <p class="admin-chat-message {{ $message->sender }}">
                        <span>{{ $message->sender }} / {{ $message->created_at->format('H:i') }}</span>
                        {{ $message->body }}
                      </p>
                    @endforeach
                  </div>

                  <form action="{{ route('admin.chat.reply', $chatSession) }}" method="POST" class="admin-form admin-chat-reply">
                    @csrf
                    <label for="reply_{{ $chatSession->id }}">Responder</label>
                    <textarea id="reply_{{ $chatSession->id }}" name="body" rows="3" required></textarea>
                    <button class="button primary" type="submit">Enviar respuesta</button>
                  </form>
                </article>
              @empty
                <article class="admin-chat-card">
                  <strong>Sin conversaciones aun.</strong>
                  <span>Cuando alguien use el chatbot de Contacto aparecera aqui.</span>
                </article>
              @endforelse
            </div>
          </section>
        </section>

        <section class="admin-page" id="subscribers" data-admin-page="subscribers" hidden>
          <div class="admin-section-header">
            <p class="eyebrow">SUBSCRIBERS</p>
            <h2>Lista de suscriptores</h2>
          </div>

          <section class="admin-panel">
            <div class="admin-list">
              @forelse ($subscribers as $subscriber)
                <article>
                  <strong>{{ $subscriber->email }}</strong>
                  <span>{{ optional($subscriber->subscribed_at)->format('d-m-Y H:i') }}</span>
                </article>
              @empty
                <article>
                  <strong>Sin suscriptores aun.</strong>
                  <span>Prueba el formulario de la home.</span>
                </article>
              @endforelse
            </div>
          </section>
        </section>
      </main>
    </div>

    <script src="{{ asset('js/signal.js') }}"></script>
    <script src="{{ asset('js/admin.js') }}?v=admin-sections-theme-1"></script>
  </body>
</html>
