<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-500">Detalle diario</p>
                <h2 class="mt-1 text-2xl font-semibold text-slate-950">{{ $selectedDate->locale('es')->translatedFormat('d F Y') }}</h2>
            </div>
            <a href="{{ route('admin.finances.calendar', ['month' => $selectedDate->format('Y-m')]) }}" class="inline-flex items-center justify-center rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">
                Volver al calendario
            </a>
        </div>
    </x-slot>

    @php
        $money = fn ($value) => '$'.number_format((float) $value, 2);
    @endphp

    <div class="py-10">
        <div class="mx-auto max-w-7xl space-y-8 px-4 sm:px-6 lg:px-8">
            @if (session('finance_status'))
                <div class="rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">
                    {{ session('finance_status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="rounded-md border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                    Revisa los campos marcados. Los montos deben ser numericos y mayores o iguales a 0.
                </div>
            @endif

            <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-500">Nueva entrada</p>
                        <h3 class="mt-1 text-xl font-semibold text-slate-950">Registrar movimiento</h3>
                    </div>
                </div>

                <form action="{{ route('admin.finances.entries.store', $selectedDate->format('Y-m-d')) }}" method="POST" class="mt-5 grid gap-4 lg:grid-cols-12">
                    @csrf
                    <div class="lg:col-span-3">
                        <label for="name" class="text-sm font-semibold text-slate-700">Nombre</label>
                        <input id="name" name="name" type="text" value="{{ old('name') }}" required class="mt-1 w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-slate-900 focus:ring-slate-900">
                    </div>
                    <div class="lg:col-span-3">
                        <label for="description" class="text-sm font-semibold text-slate-700">Descripcion</label>
                        <input id="description" name="description" type="text" value="{{ old('description') }}" class="mt-1 w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-slate-900 focus:ring-slate-900">
                    </div>
                    <div class="lg:col-span-2">
                        <label for="amount" class="text-sm font-semibold text-slate-700">Monto</label>
                        <input id="amount" name="amount" type="number" min="0" step="0.01" value="{{ old('amount', '0.00') }}" required class="mt-1 w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-slate-900 focus:ring-slate-900">
                    </div>
                    <div class="lg:col-span-2">
                        <label for="type" class="text-sm font-semibold text-slate-700">Tipo</label>
                        <select id="type" name="type" required class="mt-1 w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-slate-900 focus:ring-slate-900">
                            @foreach ($typeLabels as $type => $label)
                                <option value="{{ $type }}" @selected(old('type') === $type)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <label class="flex items-center gap-2 text-sm font-semibold text-slate-700 lg:col-span-1 lg:pt-7">
                        <input name="is_active" type="checkbox" value="1" @checked(old('is_active', true)) class="rounded border-slate-300 text-slate-950 focus:ring-slate-900">
                        Activo
                    </label>
                    <div class="lg:col-span-1 lg:pt-6">
                        <button type="submit" class="w-full rounded-md bg-slate-950 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-slate-800">
                            Agregar
                        </button>
                    </div>
                </form>
            </section>

            <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.28em] text-rose-500">Pasivos</p>
                        <h3 class="mt-1 text-xl font-semibold text-slate-950">Gastos / pasivos</h3>
                    </div>
                    <div class="grid gap-2 text-sm sm:grid-cols-3">
                        <span class="rounded-md bg-rose-50 px-3 py-2 text-rose-700">Fijos: <strong>{{ $money($totals['fixed_expenses']) }}</strong></span>
                        <span class="rounded-md bg-rose-50 px-3 py-2 text-rose-700">Del dia: <strong>{{ $money($totals['day_expenses']) }}</strong></span>
                        <span class="rounded-md bg-slate-950 px-3 py-2 text-white">Total: <strong>{{ $money($totals['expenses']) }}</strong></span>
                    </div>
                </div>

                @include('admin.finances.partials.entry-table', [
                    'groups' => [
                        'Gastos fijos' => $fixedExpenses,
                        'Gastos del dia' => $dayExpenses,
                    ],
                    'selectedDate' => $selectedDate,
                    'typeLabels' => $typeLabels,
                ])
            </section>

            <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.28em] text-emerald-600">Activos</p>
                        <h3 class="mt-1 text-xl font-semibold text-slate-950">Activos / ingresos</h3>
                    </div>
                    <div class="grid gap-2 text-sm sm:grid-cols-3">
                        <span class="rounded-md bg-emerald-50 px-3 py-2 text-emerald-700">Fijos: <strong>{{ $money($totals['fixed_assets']) }}</strong></span>
                        <span class="rounded-md bg-emerald-50 px-3 py-2 text-emerald-700">Del dia: <strong>{{ $money($totals['day_incomes']) }}</strong></span>
                        <span class="rounded-md bg-slate-950 px-3 py-2 text-white">Total: <strong>{{ $money($totals['assets']) }}</strong></span>
                    </div>
                </div>

                @include('admin.finances.partials.entry-table', [
                    'groups' => [
                        'Activos fijos' => $fixedAssets,
                        'Ingresos del dia' => $dayIncomes,
                    ],
                    'selectedDate' => $selectedDate,
                    'typeLabels' => $typeLabels,
                ])
            </section>

            <section class="rounded-lg border border-slate-900 bg-slate-950 p-6 text-white shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-[0.28em] text-slate-300">Balance del dia</p>
                <div class="mt-3 flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                    <p class="text-lg text-slate-200">
                        {{ $money($totals['assets']) }} - {{ $money($totals['expenses']) }}
                    </p>
                    <strong class="text-4xl font-semibold {{ $totals['balance'] >= 0 ? 'text-emerald-300' : 'text-rose-300' }}">
                        {{ $money($totals['balance']) }}
                    </strong>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
