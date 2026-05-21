@php
  $money = fn ($value) => '$'.number_format((float) $value, 2);
@endphp

<!doctype html>
<html lang="es">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Finanzas / {{ config('app.name') }}</title>
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
          <a class="is-active" href="{{ route('admin.finances.calendar') }}">Finanzas</a>
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
            <p class="eyebrow">FINANZAS</p>
            <h1>Control financiero</h1>
            <p>Revisa el balance mensual, compara cortes quincenales y entra al calendario solo para capturar o corregir movimientos.</p>
          </div>
        </header>

        <section class="admin-page finance-page">
          <section class="admin-panel finance-summary-panel">
            <div class="finance-summary-head">
              <div>
                <p class="eyebrow">RESUMEN MENSUAL</p>
                <h2>{{ $month->locale('es')->translatedFormat('F Y') }}</h2>
                <p>Vista rapida para entender fijos, movimientos del mes y saldo sin abrir cada dia.</p>
              </div>
              <div class="finance-balance">
                <span>Balance estimado</span>
                <strong class="{{ $monthlySummary['balance'] >= 0 ? 'is-positive' : 'is-negative' }}">
                  {{ $money($monthlySummary['balance']) }}
                </strong>
              </div>
            </div>

            <div class="finance-metric-grid">
              <article>
                <span>Gastos fijos</span>
                <strong>{{ $money($monthlySummary['fixed_expenses']) }}</strong>
              </article>
              <article>
                <span>Gastos del mes</span>
                <strong>{{ $money($monthlySummary['variable_expenses']) }}</strong>
              </article>
              <article>
                <span>Ingresos fijos</span>
                <strong>{{ $money($monthlySummary['fixed_assets']) }}</strong>
              </article>
              <article>
                <span>Ingresos del mes</span>
                <strong>{{ $money($monthlySummary['variable_incomes']) }}</strong>
              </article>
            </div>
          </section>

          <section class="admin-panel">
            <div class="admin-section-header finance-section-header">
              <div>
                <p class="eyebrow">CORTES</p>
                <h2>Balance por quincena</h2>
              </div>
              <p>Los cortes muestran movimientos con fecha; los fijos se resumen arriba.</p>
            </div>

            <div class="finance-period-grid">
              @foreach ($fortnightSummaries as $period)
                <article class="finance-period-card">
                  <div>
                    <h3>{{ $period['label'] }}</h3>
                    <span>{{ $period['start']->format('d/m') }} - {{ $period['end']->format('d/m') }}</span>
                  </div>
                  <strong class="{{ $period['balance'] >= 0 ? 'is-positive' : 'is-negative' }}">{{ $money($period['balance']) }}</strong>
                  <div class="finance-period-totals">
                    <span>Gastos: <strong>{{ $money($period['expenses']) }}</strong></span>
                    <span>Ingresos: <strong>{{ $money($period['incomes']) }}</strong></span>
                  </div>
                </article>
              @endforeach
            </div>
          </section>

          <section class="admin-panel">
            <div class="admin-section-header finance-section-header">
              <div>
                <p class="eyebrow">CALENDARIO</p>
                <h2>{{ $month->locale('es')->translatedFormat('F Y') }}</h2>
                <p>Selecciona un dia para capturar o corregir movimientos.</p>
              </div>

              <div class="finance-month-actions">
                <a href="{{ route('admin.finances.calendar', ['month' => $previousMonth->format('Y-m')]) }}">Anterior</a>
                <a href="{{ route('admin.finances.calendar') }}">Hoy</a>
                <a href="{{ route('admin.finances.calendar', ['month' => $nextMonth->format('Y-m')]) }}">Siguiente</a>
              </div>
            </div>

            <div class="finance-weekdays">
              @foreach (['Lun', 'Mar', 'Mie', 'Jue', 'Vie', 'Sab', 'Dom'] as $weekday)
                <span>{{ $weekday }}</span>
              @endforeach
            </div>

            <div class="finance-calendar-grid">
              @foreach ($calendarDays as $day)
                <a href="{{ route('admin.finances.day', $day['date']->format('Y-m-d')) }}" class="{{ $day['is_current_month'] ? '' : 'is-muted' }} {{ $day['date']->isToday() ? 'is-today' : '' }}">
                  <div class="finance-day-head">
                    <strong>{{ $day['date']->day }}</strong>
                    <span>Abrir</span>
                  </div>
                  <div class="finance-day-totals">
                    <span>Gastos <strong>{{ $money($day['expense_total']) }}</strong></span>
                    <span>Ingresos <strong>{{ $money($day['income_total']) }}</strong></span>
                  </div>
                </a>
              @endforeach
            </div>
          </section>
        </section>
      </main>
    </div>

    <script src="{{ asset('js/signal.js') }}"></script>
    <script src="{{ asset('js/admin.js') }}?v=admin-sections-theme-1"></script>
  </body>
</html>
