<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-500">Finanzas</p>
                <h2 class="mt-1 text-2xl font-semibold text-slate-950">Calendario financiero</h2>
            </div>
            <a href="{{ route('admin.index') }}" class="inline-flex items-center justify-center rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">
                Volver al admin
            </a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm text-slate-500">Selecciona un dia para administrar gastos, ingresos y conceptos fijos.</p>
                        <h3 class="mt-1 text-xl font-semibold capitalize text-slate-950">{{ $month->locale('es')->translatedFormat('F Y') }}</h3>
                    </div>

                    <div class="flex items-center gap-2">
                        <a href="{{ route('admin.finances.calendar', ['month' => $previousMonth->format('Y-m')]) }}" class="rounded-md border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                            Anterior
                        </a>
                        <a href="{{ route('admin.finances.calendar') }}" class="rounded-md border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                            Hoy
                        </a>
                        <a href="{{ route('admin.finances.calendar', ['month' => $nextMonth->format('Y-m')]) }}" class="rounded-md border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                            Siguiente
                        </a>
                    </div>
                </div>

                <div class="mt-6 grid grid-cols-7 border-y border-slate-200 bg-slate-50 text-center text-xs font-bold uppercase tracking-wide text-slate-500">
                    @foreach (['Lun', 'Mar', 'Mie', 'Jue', 'Vie', 'Sab', 'Dom'] as $weekday)
                        <div class="px-2 py-3">{{ $weekday }}</div>
                    @endforeach
                </div>

                <div class="grid grid-cols-1 gap-3 pt-4 sm:grid-cols-7 sm:gap-0 sm:pt-0">
                    @foreach ($calendarDays as $day)
                        <a href="{{ route('admin.finances.day', $day['date']->format('Y-m-d')) }}"
                           class="group min-h-32 border border-slate-200 bg-white p-3 hover:z-10 hover:border-slate-900 hover:shadow-lg sm:-ml-px sm:-mt-px {{ $day['is_current_month'] ? '' : 'opacity-45' }}">
                            <div class="flex items-center justify-between">
                                <span class="flex size-8 items-center justify-center rounded-full text-sm font-bold {{ $day['date']->isToday() ? 'bg-slate-950 text-white' : 'text-slate-900' }}">
                                    {{ $day['date']->day }}
                                </span>
                                <span class="text-[11px] font-semibold uppercase tracking-wide text-slate-400 group-hover:text-slate-700">Abrir</span>
                            </div>

                            <div class="mt-4 space-y-2 text-xs">
                                <div class="flex items-center justify-between rounded-md bg-rose-50 px-2 py-1 text-rose-700">
                                    <span>Gastos</span>
                                    <strong>${{ number_format((float) $day['expense_total'], 2) }}</strong>
                                </div>
                                <div class="flex items-center justify-between rounded-md bg-emerald-50 px-2 py-1 text-emerald-700">
                                    <span>Ingresos</span>
                                    <strong>${{ number_format((float) $day['income_total'], 2) }}</strong>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
