<div class="mt-5 overflow-x-auto">
    <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
        <thead class="bg-slate-50 text-xs font-bold uppercase tracking-wide text-slate-500">
            <tr>
                <th class="px-3 py-3">Grupo</th>
                <th class="px-3 py-3">Nombre</th>
                <th class="px-3 py-3">Descripcion</th>
                <th class="px-3 py-3">Monto</th>
                <th class="px-3 py-3">Tipo</th>
                <th class="px-3 py-3">Fecha</th>
                <th class="px-3 py-3">Estado</th>
                <th class="px-3 py-3 text-right">Acciones</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse ($groups as $groupName => $entries)
                @forelse ($entries as $entry)
                    @php($formId = 'entry-update-'.$entry->id)
                    <tr class="{{ $entry->is_active ? '' : 'bg-slate-50 text-slate-500' }}">
                        <td class="whitespace-nowrap px-3 py-3 font-semibold text-slate-700">{{ $groupName }}</td>
                        <td class="min-w-48 px-3 py-3">
                            <form id="{{ $formId }}" action="{{ route('admin.finances.entries.update', [$selectedDate->format('Y-m-d'), $entry]) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <input name="name" type="text" value="{{ $entry->name }}" required class="w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-slate-900 focus:ring-slate-900">
                            </form>
                        </td>
                        <td class="min-w-56 px-3 py-3">
                            <input form="{{ $formId }}" name="description" type="text" value="{{ $entry->description }}" class="w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-slate-900 focus:ring-slate-900">
                        </td>
                        <td class="min-w-32 px-3 py-3">
                            <input form="{{ $formId }}" name="amount" type="number" min="0" step="0.01" value="{{ $entry->amount }}" required class="w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-slate-900 focus:ring-slate-900">
                        </td>
                        <td class="min-w-44 px-3 py-3">
                            <select form="{{ $formId }}" name="type" required class="w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-slate-900 focus:ring-slate-900">
                                @foreach ($typeLabels as $type => $label)
                                    <option value="{{ $type }}" @selected($entry->type === $type)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td class="whitespace-nowrap px-3 py-3 text-slate-600">
                            {{ $entry->entry_date?->format('d-m-Y') ?? 'Global' }}
                        </td>
                        <td class="px-3 py-3">
                            <label class="inline-flex items-center gap-2 font-semibold text-slate-700">
                                <input form="{{ $formId }}" name="is_active" type="checkbox" value="1" @checked($entry->is_active) class="rounded border-slate-300 text-slate-950 focus:ring-slate-900">
                                Activo
                            </label>
                        </td>
                        <td class="whitespace-nowrap px-3 py-3 text-right">
                            <button form="{{ $formId }}" type="submit" class="rounded-md bg-slate-950 px-3 py-2 text-xs font-semibold text-white hover:bg-slate-800">
                                Guardar
                            </button>
                            <form action="{{ route('admin.finances.entries.destroy', [$selectedDate->format('Y-m-d'), $entry]) }}" method="POST" class="inline" onsubmit="return confirm('Eliminar esta entrada financiera?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="rounded-md border border-rose-200 px-3 py-2 text-xs font-semibold text-rose-700 hover:bg-rose-50">
                                    Eliminar
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-3 py-4 text-sm text-slate-500">{{ $groupName }}: sin entradas.</td>
                    </tr>
                @endforelse
            @empty
                <tr>
                    <td colspan="8" class="px-3 py-4 text-sm text-slate-500">Sin entradas registradas.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
