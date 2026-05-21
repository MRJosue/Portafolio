@php
  $parsed = $import['parsed'] ?? [];
  $formatEntries = function (array $entries): string {
      return collect($entries)->map(function ($entry) {
          return collect([
              $entry['title'] ?? null,
              $entry['organization'] ?? null,
              $entry['period'] ?? null,
              $entry['description'] ?? null,
          ])->filter()->implode("\n");
      })->filter()->implode("\n\n");
  };
@endphp

<!doctype html>
<html lang="es">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Talentos / {{ config('app.name') }}</title>
    <link rel="stylesheet" href="{{ asset('css/signal.css') }}?v=talents-import-1">
  </head>
  <body class="admin-body" data-admin-theme>
    <canvas id="ember-canvas" class="motion-lines-canvas" aria-hidden="true"></canvas>
    <div class="grain" aria-hidden="true"></div>

    <div class="admin-shell">
      <aside class="admin-aside" aria-label="Secciones">
        <a class="brand admin-brand" href="{{ route('home') }}">
          <span class="brand-mark"></span>
          <span>Warm Bento Studio</span>
        </a>

        <nav class="admin-side-nav" aria-label="Paginas internas">
          <a href="{{ route('admin.index') }}">Admin</a>
          <a class="is-active" href="{{ route('talents.index') }}">Talentos</a>
          <a href="{{ route('admin.finances.calendar') }}">Finanzas</a>
          <a href="{{ route('projects.index') }}">Vista publica</a>
        </nav>

        <div class="admin-aside-actions">
          <button class="theme-toggle" type="button" data-theme-toggle aria-label="Cambiar modo claro u oscuro">
            <span data-theme-label>Modo claro</span>
          </button>
          <a href="{{ route('home') }}">Home</a>
        </div>
      </aside>

      <main class="admin-main">
        <header class="admin-top">
          <div>
            <p class="eyebrow">TALENTOS</p>
            <h1>CV asistido</h1>
            <p>Sube un CV, revisa los datos detectados y guarda el talento con sus elementos principales.</p>
          </div>

          @if (session('talent_status'))
            <p class="status">{{ session('talent_status') }}</p>
          @endif
        </header>

        <section class="admin-page">
          <div class="talent-grid">
            <section class="admin-panel">
              <p class="eyebrow">IMPORTAR DOCUMENTO</p>
              <form action="{{ route('talents.import') }}" method="POST" enctype="multipart/form-data" class="admin-form">
                @csrf
                <label for="cv_document">CV en PDF, DOCX o TXT</label>
                <input id="cv_document" name="cv_document" type="file" accept=".pdf,.docx,.txt,application/pdf,application/vnd.openxmlformats-officedocument.wordprocessingml.document,text/plain" required>
                @error('cv_document') <p class="status error">{{ $message }}</p> @enderror

                <button class="button primary" type="submit">Leer CV</button>
              </form>

              @if ($import)
                <form action="{{ route('talents.import.clear') }}" method="POST" class="talent-clear-form">
                  @csrf
                  @method('DELETE')
                  <button type="submit">Descartar importacion</button>
                </form>
              @endif
            </section>

            <section class="admin-panel">
              <p class="eyebrow">GUARDAR TALENTO</p>
              <form action="{{ route('talents.store') }}" method="POST" class="admin-form">
                @csrf

                <label for="full_name">Nombre completo</label>
                <input id="full_name" name="full_name" type="text" value="{{ old('full_name', $parsed['full_name'] ?? '') }}" required>
                @error('full_name') <p class="status error">{{ $message }}</p> @enderror

                <div class="admin-fields">
                  <div>
                    <label for="email">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email', $parsed['email'] ?? '') }}">
                    @error('email') <p class="status error">{{ $message }}</p> @enderror
                  </div>
                  <div>
                    <label for="phone">Telefono</label>
                    <input id="phone" name="phone" type="text" value="{{ old('phone', $parsed['phone'] ?? '') }}">
                  </div>
                </div>

                <div class="admin-fields">
                  <div>
                    <label for="location">Ubicacion</label>
                    <input id="location" name="location" type="text" value="{{ old('location', $parsed['location'] ?? '') }}">
                  </div>
                  <div>
                    <label for="status">Estado</label>
                    <select id="status" name="status">
                      @foreach (['draft' => 'Borrador', 'active' => 'Activo', 'archived' => 'Archivado'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('status', 'active') === $value)>{{ $label }}</option>
                      @endforeach
                    </select>
                  </div>
                </div>

                <label for="headline">Titulo profesional</label>
                <input id="headline" name="headline" type="text" value="{{ old('headline', $parsed['headline'] ?? '') }}">

                <label for="summary">Resumen</label>
                <textarea id="summary" name="summary" rows="5">{{ old('summary', $parsed['summary'] ?? '') }}</textarea>

                <div class="admin-fields">
                  <div>
                    <label for="skills_text">Habilidades</label>
                    <textarea id="skills_text" name="skills_text" rows="7">{{ old('skills_text', implode("\n", $parsed['skills'] ?? [])) }}</textarea>
                  </div>
                  <div>
                    <label for="languages_text">Idiomas</label>
                    <textarea id="languages_text" name="languages_text" rows="7">{{ old('languages_text', implode("\n", $parsed['languages'] ?? [])) }}</textarea>
                  </div>
                </div>

                <label for="links_text">Links</label>
                <textarea id="links_text" name="links_text" rows="3">{{ old('links_text', implode("\n", $parsed['links'] ?? [])) }}</textarea>

                <label for="experiences_text">Experiencia</label>
                <textarea id="experiences_text" name="experiences_text" rows="9">{{ old('experiences_text', $formatEntries($parsed['experiences'] ?? [])) }}</textarea>

                <label for="educations_text">Educacion</label>
                <textarea id="educations_text" name="educations_text" rows="7">{{ old('educations_text', $formatEntries($parsed['educations'] ?? [])) }}</textarea>

                <button class="button primary" type="submit">Guardar talento y CV</button>
              </form>
            </section>
          </div>

          @if ($import)
            <section class="admin-panel">
              <p class="eyebrow">TEXTO EXTRAIDO</p>
              <pre class="talent-raw-text">{{ $parsed['raw_text'] ?? '' }}</pre>
            </section>
          @endif

          <section class="admin-panel">
            <div class="admin-section-header">
              <div>
                <p class="eyebrow">BASE DE TALENTOS</p>
                <h2>Guardados</h2>
              </div>
            </div>

            <div class="admin-list talent-list">
              @forelse ($talents as $talent)
                <article>
                  <strong>{{ $talent->full_name }}</strong>
                  <span>{{ $talent->headline ?: 'Sin titulo' }} / {{ $talent->status }}</span>
                  <span>{{ $talent->email ?: 'sin email' }} / {{ $talent->phone ?: 'sin telefono' }}</span>

                  @if ($talent->skills)
                    <p>{{ implode(', ', array_slice($talent->skills, 0, 8)) }}</p>
                  @endif

                  <div class="talent-mini-grid">
                    <span>{{ $talent->experiences->count() }} experiencias</span>
                    <span>{{ $talent->educations->count() }} estudios</span>
                    <span>{{ $talent->documents->count() }} documentos</span>
                  </div>
                </article>
              @empty
                <article>
                  <strong>Sin talentos aun.</strong>
                  <span>Importa un CV o llena el formulario manualmente para crear el primer registro.</span>
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
