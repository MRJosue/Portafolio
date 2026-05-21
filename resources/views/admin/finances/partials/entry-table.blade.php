<div class="finance-table-wrap">
  <table class="finance-table">
    <thead>
      <tr>
        <th>Grupo</th>
        <th>Tipo</th>
        <th>Descripcion</th>
        <th>Monto</th>
        <th>Movimiento</th>
        <th>Fecha</th>
        <th>Estado</th>
        <th>Acciones</th>
      </tr>
    </thead>
    <tbody>
      @forelse ($groups as $groupName => $entries)
        @forelse ($entries as $entry)
          @php($formId = 'entry-update-'.$entry->id)
          <tr class="{{ $entry->is_active ? '' : 'is-disabled' }}">
            <td>{{ $groupName }}</td>
            <td>
              <form id="{{ $formId }}" action="{{ route('admin.finances.entries.update', [$selectedDate->format('Y-m-d'), $entry]) }}" method="POST">
                @csrf
                @method('PATCH')
                <select name="name" required data-entry-name-select>
                  @foreach ($entryNameOptions['expense'] as $nameOption)
                    <option value="{{ $nameOption }}" data-entry-kind="expense" @selected($entry->name === $nameOption)>{{ $nameOption }}</option>
                  @endforeach
                  @foreach ($entryNameOptions['income'] as $nameOption)
                    <option value="{{ $nameOption }}" data-entry-kind="income" @selected($entry->name === $nameOption)>{{ $nameOption }}</option>
                  @endforeach
                </select>
              </form>
            </td>
            <td>
              <input form="{{ $formId }}" name="description" type="text" value="{{ $entry->description }}">
            </td>
            <td>
              <input form="{{ $formId }}" name="amount" type="number" min="0" step="0.01" value="{{ $entry->amount }}" required>
            </td>
            <td>
              <select form="{{ $formId }}" name="type" required data-entry-type-select>
                @foreach ($typeLabels as $type => $label)
                  <option value="{{ $type }}" @selected($entry->type === $type)>{{ $label }}</option>
                @endforeach
              </select>
            </td>
            <td>{{ $entry->entry_date?->format('d-m-Y') ?? 'Global' }}</td>
            <td>
              <label class="check-row">
                <input form="{{ $formId }}" name="is_active" type="checkbox" value="1" @checked($entry->is_active)>
                Activo
              </label>
            </td>
            <td>
              <div class="finance-table-actions">
                <button form="{{ $formId }}" type="submit">Guardar</button>
                <form action="{{ route('admin.finances.entries.destroy', [$selectedDate->format('Y-m-d'), $entry]) }}" method="POST" onsubmit="return confirm('Eliminar esta entrada financiera?');">
                  @csrf
                  @method('DELETE')
                  <button type="submit">Eliminar</button>
                </form>
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="8">{{ $groupName }}: sin entradas.</td>
          </tr>
        @endforelse
      @empty
        <tr>
          <td colspan="8">Sin entradas registradas.</td>
        </tr>
      @endforelse
    </tbody>
  </table>
</div>
