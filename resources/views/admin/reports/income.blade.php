@extends('layouts.admin')

@section('header')
<div class="flex items-center justify-between">
  <h2 class="font-semibold text-xl text-gray-800">Reporte de ingresos</h2>
</div>
@endsection

@section('content')
<div class="py-6">
  <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
    <div class="bg-white shadow-sm sm:rounded-lg">
      <div class="p-6 space-y-6">
        <form method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
          <div>
            <label class="block text-sm text-gray-700">Desde</label>
            <input type="date" name="from" value="{{ $from }}" class="mt-1 w-full rounded border-gray-300" />
          </div>
          <div>
            <label class="block text-sm text-gray-700">Hasta</label>
            <input type="date" name="to" value="{{ $to }}" class="mt-1 w-full rounded border-gray-300" />
          </div>
          <div>
            <label class="block text-sm text-gray-700">Tipo de intención</label>
            <select name="type" class="mt-1 w-full rounded border-gray-300">
              <option value="">Todos</option>
              @foreach(['rosario'=>'Rosario','rezada'=>'Misa rezada','cantada'=>'Misa cantada'] as $val=>$label)
                <option value="{{ $val }}" @selected($type===$val)>{{ $label }}</option>
              @endforeach
            </select>
          </div>
          <div>
            <label class="block text-sm text-gray-700">Medio de pago</label>
            <select name="payment_method" class="mt-1 w-full rounded border-gray-300">
              <option value="">Todos</option>
              @foreach(['cash'=>'Efectivo','transfer'=>'Transferencia','card'=>'Tarjeta','recurrente'=>'Recurrente'] as $val=>$label)
                <option value="{{ $val }}" @selected($payment===$val)>{{ $label }}</option>
              @endforeach
            </select>
          </div>
          <div class="sm:col-span-2 lg:col-span-4 flex items-center gap-3">
            <label class="inline-flex items-center gap-2 text-sm">
              <input type="checkbox" name="include_prepaid" value="1" @checked($includePrepaid) class="rounded border-gray-300" />
              Incluir intenciones ingresadas como YA PAGADAS
            </label>
            <button class="ml-auto px-4 py-2 rounded bg-indigo-600 text-white">Aplicar filtros</button>
          </div>
        </form>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <div>
            <h3 class="font-semibold mb-2">Intenciones (por tipo)</h3>
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead>
                  <tr class="bg-gray-50">
                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                    <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Cantidad</th>
                    <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Total (Q)</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                  @forelse($byType as $row)
                    <tr>
                      <td class="px-3 py-2">{{ ucfirst($row->type) }}</td>
                      <td class="px-3 py-2 text-right">{{ number_format($row->cnt) }}</td>
                      <td class="px-3 py-2 text-right">{{ number_format($row->total, 2) }}</td>
                    </tr>
                  @empty
                    <tr><td colspan="3" class="px-3 py-4 text-gray-500">Sin datos en el rango.</td></tr>
                  @endforelse
                </tbody>
                <tfoot>
                  <tr class="bg-gray-50 font-medium">
                    <td class="px-3 py-2">Total</td>
                    <td class="px-3 py-2 text-right"></td>
                    <td class="px-3 py-2 text-right">{{ number_format($sumIntentions, 2) }}</td>
                  </tr>
                </tfoot>
              </table>
            </div>
          </div>

          <div>
            <h3 class="font-semibold mb-2">Misas especiales — montos de reserva (por categoría)</h3>
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead>
                  <tr class="bg-gray-50">
                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Categoría</th>
                    <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Cantidad</th>
                    <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Total (Q)</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                  @forelse($byCategory as $row)
                    <tr>
                      <td class="px-3 py-2">{{ str_replace('_',' ', ucfirst($row->special_category)) }}</td>
                      <td class="px-3 py-2 text-right">{{ number_format($row->cnt) }}</td>
                      <td class="px-3 py-2 text-right">{{ number_format($row->total, 2) }}</td>
                    </tr>
                  @empty
                    <tr><td colspan="3" class="px-3 py-4 text-gray-500">Sin datos en el rango.</td></tr>
                  @endforelse
                </tbody>
                <tfoot>
                  <tr class="bg-gray-50 font-medium">
                    <td class="px-3 py-2">Total</td>
                    <td class="px-3 py-2 text-right"></td>
                    <td class="px-3 py-2 text-right">{{ number_format($sumSpecials, 2) }}</td>
                  </tr>
                </tfoot>
              </table>
            </div>
          </div>
        </div>

        <div class="border-t pt-4">
          <h3 class="font-semibold mb-2">Totales generales</h3>
          <div class="grid sm:grid-cols-2 gap-3">
            <div class="bg-gray-50 p-4 rounded">
              <div class="text-sm text-gray-500">Intenciones</div>
              <div class="text-2xl font-semibold">Q {{ number_format($sumIntentions, 2) }}</div>
            </div>
            <div class="bg-gray-50 p-4 rounded">
              <div class="text-sm text-gray-500">Misas especiales (reservas)</div>
              <div class="text-2xl font-semibold">Q {{ number_format($sumSpecials, 2) }}</div>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
</div>
@endsection