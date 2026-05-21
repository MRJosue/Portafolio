@php
  $money = fn ($value) => '$'.number_format((float) $value, 2);
@endphp

<!doctype html>
<html lang="es">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Detalle financiero / {{ config('app.name') }}</title>
    <link rel="stylesheet" href="{{ asset('css/signal.css') }}?v=finance-admin-2">
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
          <a href="{{ route('talents.index') }}">Talentos</a>
          <a class="is-active" href="{{ route('admin.finances.calendar', ['month' => $selectedDate->format('Y-m')]) }}">Finanzas</a>
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
            <p class="eyebrow">DETALLE DIARIO</p>
            <h1>{{ $selectedDate->locale('es')->translatedFormat('d F Y') }}</h1>
            <p>Captura movimientos puntuales y administra conceptos fijos que se reflejan en el resumen mensual.</p>
          </div>
          <a class="admin-link-button" href="{{ route('admin.finances.calendar', ['month' => $selectedDate->format('Y-m')]) }}">Volver al calendario</a>
        </header>

        <section class="admin-page finance-page">
          @if (session('finance_status'))
            <p class="status">{{ session('finance_status') }}</p>
          @endif

          @if ($errors->any())
            <p class="status error">Revisa los campos marcados. Los montos deben ser numericos y mayores o iguales a 0.</p>
          @endif

          <section class="admin-panel">
            <p class="eyebrow">NUEVA ENTRADA</p>
            <h2>Registrar movimiento</h2>

            <form action="{{ route('admin.finances.entries.store', $selectedDate->format('Y-m-d')) }}" method="POST" class="admin-form finance-entry-form">
              @csrf
              <div class="finance-entry-grid">
                <div>
                  <label for="name">Tipo</label>
                  <select id="name" name="name" required data-entry-name-select>
                    @foreach ($entryNameOptions['expense'] as $nameOption)
                      <option value="{{ $nameOption }}" data-entry-kind="expense" @selected(old('name') === $nameOption)>{{ $nameOption }}</option>
                    @endforeach
                    @foreach ($entryNameOptions['income'] as $nameOption)
                      <option value="{{ $nameOption }}" data-entry-kind="income" @selected(old('name') === $nameOption)>{{ $nameOption }}</option>
                    @endforeach
                  </select>
                </div>
                <div>
                  <label for="description">Descripcion</label>
                  <input id="description" name="description" type="text" value="{{ old('description') }}">
                </div>
                <div>
                  <label for="amount">Monto</label>
                  <input id="amount" name="amount" type="number" min="0" step="0.01" value="{{ old('amount', '0.00') }}" required>
                </div>
                <div>
                  <label for="type">Movimiento</label>
                  <select id="type" name="type" required data-entry-type-select>
                    @foreach ($typeLabels as $type => $label)
                      <option value="{{ $type }}" @selected(old('type') === $type)>{{ $label }}</option>
                    @endforeach
                  </select>
                </div>
                <label class="check-row">
                  <input name="is_active" type="checkbox" value="1" @checked(old('is_active', true))>
                  Activo
                </label>
              </div>
              <button class="button primary" type="submit">Agregar</button>
            </form>
          </section>

          <section class="admin-panel">
            <div class="admin-section-header finance-section-header">
              <div>
                <p class="eyebrow">PASIVOS</p>
                <h2>Gastos</h2>
              </div>
              <div class="finance-chip-row">
                <span>Fijos: <strong>{{ $money($totals['fixed_expenses']) }}</strong></span>
                <span>Del dia: <strong>{{ $money($totals['day_expenses']) }}</strong></span>
                <span>Total: <strong>{{ $money($totals['expenses']) }}</strong></span>
              </div>
            </div>

            @include('admin.finances.partials.entry-table', [
              'groups' => [
                'Gastos fijos' => $fixedExpenses,
                'Gastos del dia' => $dayExpenses,
              ],
              'selectedDate' => $selectedDate,
              'typeLabels' => $typeLabels,
              'entryNameOptions' => $entryNameOptions,
            ])
          </section>

          <section class="admin-panel">
            <div class="admin-section-header finance-section-header">
              <div>
                <p class="eyebrow">ACTIVOS</p>
                <h2>Ingresos</h2>
              </div>
              <div class="finance-chip-row">
                <span>Fijos: <strong>{{ $money($totals['fixed_assets']) }}</strong></span>
                <span>Del dia: <strong>{{ $money($totals['day_incomes']) }}</strong></span>
                <span>Total: <strong>{{ $money($totals['assets']) }}</strong></span>
              </div>
            </div>

            @include('admin.finances.partials.entry-table', [
              'groups' => [
                'Activos fijos' => $fixedAssets,
                'Ingresos del dia' => $dayIncomes,
              ],
              'selectedDate' => $selectedDate,
              'typeLabels' => $typeLabels,
              'entryNameOptions' => $entryNameOptions,
            ])
          </section>

          <section class="admin-panel finance-summary-panel">
            <div class="admin-section-header finance-section-header">
              <div>
                <p class="eyebrow">BALANCE</p>
                <h2>Quincenal y mensual</h2>
              </div>
            </div>

            <div class="finance-summary-head">
              <div>
                <p class="eyebrow">BALANCE QUINCENAL</p>
                <h2>{{ $periodBalances['fortnight']['label'] }}</h2>
                <p>{{ $periodBalances['fortnight']['range'] }}</p>
                <div class="finance-chip-row">
                  <span>Ingresos: <strong>{{ $money($periodBalances['fortnight']['assets']) }}</strong></span>
                  <span>Gastos: <strong>{{ $money($periodBalances['fortnight']['expenses']) }}</strong></span>
                </div>
              </div>
              <div class="finance-balance">
                <span>Resultado</span>
                <strong class="{{ $periodBalances['fortnight']['balance'] >= 0 ? 'is-positive' : 'is-negative' }}">{{ $money($periodBalances['fortnight']['balance']) }}</strong>
              </div>
            </div>

            <div class="finance-summary-head">
              <div>
                <p class="eyebrow">BALANCE MENSUAL</p>
                <h2>{{ ucfirst($periodBalances['monthly']['label']) }}</h2>
                <p>{{ $periodBalances['monthly']['range'] }}</p>
                <div class="finance-chip-row">
                  <span>Ingresos: <strong>{{ $money($periodBalances['monthly']['assets']) }}</strong></span>
                  <span>Gastos: <strong>{{ $money($periodBalances['monthly']['expenses']) }}</strong></span>
                </div>
              </div>
              <div class="finance-balance">
                <span>Resultado</span>
                <strong class="{{ $periodBalances['monthly']['balance'] >= 0 ? 'is-positive' : 'is-negative' }}">{{ $money($periodBalances['monthly']['balance']) }}</strong>
              </div>
            </div>
          </section>
        </section>
      </main>
    </div>

    <script src="{{ asset('js/signal.js') }}"></script>
    <script src="{{ asset('js/admin.js') }}?v=admin-sections-theme-1"></script>
    <script>
      document.querySelectorAll('[data-entry-type-select]').forEach((typeSelect) => {
        const form = typeSelect.form || typeSelect.closest('form') || document;
        const nameSelect = form.querySelector('[data-entry-name-select]');

        if (! nameSelect) {
          return;
        }

        const syncNameOptions = () => {
          const kind = ['fixed_expense', 'expense'].includes(typeSelect.value) ? 'expense' : 'income';
          const visibleOptions = [];

          nameSelect.querySelectorAll('option').forEach((option) => {
            const isVisible = option.dataset.entryKind === kind;
            option.hidden = ! isVisible;
            option.disabled = ! isVisible;

            if (isVisible) {
              visibleOptions.push(option);
            }
          });

          if (! visibleOptions.includes(nameSelect.selectedOptions[0])) {
            nameSelect.value = visibleOptions[0]?.value || '';
          }
        };

        typeSelect.addEventListener('change', syncNameOptions);
        syncNameOptions();
      });
    </script>
  </body>
</html>
