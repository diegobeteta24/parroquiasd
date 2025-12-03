@extends('layouts.admin')

@section('header')
<div class="flex items-center justify-between">
  <h2 class="font-semibold text-xl text-gray-800">Intenciones — {{ sprintf('%04d-%02d', $year, $month) }}</h2>
  <div class="flex gap-2">
    <a href="?year={{ $year }}&month={{ $month }}&date_field={{ $dateField }}&only_ordinary={{ $onlyOrdinary?1:0 }}&format=csv" class="px-3 py-2 rounded bg-emerald-600 text-white">Exportar CSV</a>
    <a href="?year={{ $year }}&month={{ $month }}&date_field={{ $dateField }}&only_ordinary={{ $onlyOrdinary?1:0 }}&format=json" class="px-3 py-2 rounded bg-gray-200">JSON</a>
  </div>
</div>
@endsection

@section('content')
<div class="py-6">
  <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
    <div class="bg-white shadow-sm sm:rounded-lg">
      <div class="p-6 space-y-4">
        <form method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-4">
          <div>
            <label class="block text-sm text-gray-700">Año</label>
            <input type="number" name="year" value="{{ $year }}" class="mt-1 w-full rounded border-gray-300" />
          </div>
          <div>
            <label class="block text-sm text-gray-700">Mes</label>
            <input type="number" name="month" min="1" max="12" value="{{ $month }}" class="mt-1 w-full rounded border-gray-300" />
          </div>
          <div>
            <label class="block text-sm text-gray-700">Usar fecha</label>
            <select name="date_field" class="mt-1 w-full rounded border-gray-300">
              <option value="created" @selected($dateField==='created')>Creación</option>
              <option value="mass" @selected($dateField==='mass')>Fecha de misa</option>
            </select>
          </div>
          <div class="flex items-center gap-2">
            <label class="inline-flex items-center gap-2 text-sm mt-6"><input type="checkbox" name="only_ordinary" value="1" @checked($onlyOrdinary) class="rounded border-gray-300"> Solo ordinarias</label>
          </div>
          <div class="lg:col-span-2 flex items-end">
            <button class="ml-auto px-4 py-2 rounded bg-indigo-600 text-white">Aplicar</button>
          </div>
        </form>

        <div class="grid sm:grid-cols-3 gap-3">
          <div class="bg-gray-50 p-4 rounded"><div class="text-sm text-gray-500">Cantidad</div><div class="text-2xl font-semibold">{{ number_format($count) }}</div></div>
          <div class="bg-gray-50 p-4 rounded"><div class="text-sm text-gray-500">Total (Q)</div><div class="text-2xl font-semibold">Q {{ number_format($sum, 2) }}</div></div>
          <div class="bg-gray-50 p-4 rounded"><div class="text-sm text-gray-500">Rango</div><div class="text-lg">{{ $from->toDateString() }} — {{ $to->toDateString() }}</div></div>
        </div>

        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead>
              <tr class="bg-gray-50">
                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Creada</th>
                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Fecha misa</th>
                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Hora</th>
                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Donante</th>
                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Monto</th>
                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Método</th>
                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              @forelse($rows as $r)
                <tr>
                  <td class="px-3 py-2">{{ $r->id }}</td>
                  <td class="px-3 py-2">{{ optional($r->created_at)->timezone(config('app.timezone'))->format('Y-m-d H:i') }}</td>
                  <td class="px-3 py-2">{{ optional($r->mass?->starts_at)->format('Y-m-d') }}</td>
                  <td class="px-3 py-2">{{ optional($r->mass?->starts_at)->format('H:i') }}</td>
                  <td class="px-3 py-2">{{ ucfirst($r->type) }}</td>
                  <td class="px-3 py-2">{{ $r->donor_name }}</td>
                  <td class="px-3 py-2 text-right">{{ number_format((float)($r->stipend_amount_gtq ?? $r->amount), 2) }}</td>
                  <td class="px-3 py-2">{{ strtoupper($r->payment_method ?? '—') }}</td>
                  <td class="px-3 py-2">{{ ucfirst($r->status) }}</td>
                </tr>
              @empty
                <tr><td class="px-3 py-4 text-gray-500" colspan="9">Sin registros.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
