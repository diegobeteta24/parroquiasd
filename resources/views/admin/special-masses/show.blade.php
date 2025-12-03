@extends('layouts.admin')

@section('header')
<div class="flex items-center justify-between">
    <h2 class="font-semibold text-xl text-gray-800">Misa especial — {{ $mass->starts_at->format('d/m/Y H:i') }}</h2>
    <div class="flex items-center gap-2">
      <a href="{{ route('admin.special-masses.edit',$mass) }}" class="px-3 py-2 rounded bg-indigo-600 text-white">Editar</a>
      <form method="POST" action="{{ route('admin.special-masses.destroy', $mass) }}" onsubmit="return confirm('¿Seguro que deseas eliminar esta misa especial?');" class="flex items-center gap-2">
        @csrf
        @method('DELETE')
        <input type="text" name="justification" required class="rounded border-gray-300 text-sm" placeholder="Justificación de eliminación" />
        <button type="submit" class="px-3 py-2 rounded bg-red-600 text-white">Eliminar</button>
      </form>
    </div>
</div>
@endsection

@section('content')
<div class="py-6">
  <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
    <div class="bg-white shadow-sm sm:rounded-lg">
      <div class="p-6 space-y-4">
        @if(session('status'))<div class="p-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded">{{ session('status') }}</div>@endif
        <dl class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div class="bg-gray-50 p-4 rounded"><dt class="text-sm text-gray-500">Categoría</dt><dd class="text-lg font-medium">{{ str_replace('_',' ', ucfirst($mass->special_category)) }}</dd></div>
          <div class="bg-gray-50 p-4 rounded"><dt class="text-sm text-gray-500">Fecha</dt><dd class="text-lg font-medium">{{ $mass->starts_at->format('d/m/Y') }}</dd></div>
          <div class="bg-gray-50 p-4 rounded"><dt class="text-sm text-gray-500">Hora</dt><dd class="text-lg font-medium">{{ $mass->starts_at->format('H:i') }}</dd></div>
        </dl>
        <dl class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-4">
          <div class="bg-gray-50 p-4 rounded"><dt class="text-sm text-gray-500">Padre</dt><dd class="text-lg font-medium">{{ $mass->priest?->name ?? '—' }}</dd></div>
          <div class="bg-gray-50 p-4 rounded"><dt class="text-sm text-gray-500">Monto de reserva</dt><dd class="text-lg font-medium">{{ $mass->reservation_amount !== null ? 'Q '.number_format($mass->reservation_amount,2) : '—' }}</dd></div>
          <div class="bg-gray-50 p-4 rounded sm:col-span-1 sm:col-span-2"><dt class="text-sm text-gray-500">Comentarios</dt><dd class="text-lg font-medium">{{ $mass->notes ?: '—' }}</dd></div>
        </dl>
        @php($d = $mass->special_details ?? [])
        <div class="border-t pt-4">
          <h3 class="font-semibold mb-2">Detalles</h3>
          @if(empty($d))
            <p class="text-gray-500">Sin detalles.</p>
          @else
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-3">
              @switch($mass->special_category)
                @case('bautismo')
                  <div class="bg-gray-50 p-3 rounded"><dt class="text-sm text-gray-500">Niño/a</dt><dd class="text-lg">{{ $d['child_name'] ?? '—' }}</dd></div>
                  <div class="bg-gray-50 p-3 rounded"><dt class="text-sm text-gray-500">Fecha de nacimiento</dt><dd class="text-lg">{{ $d['birth_date'] ?? '—' }}</dd></div>
                  <div class="bg-gray-50 p-3 rounded"><dt class="text-sm text-gray-500">Padre</dt><dd class="text-lg">{{ $d['father_name'] ?? '—' }}</dd></div>
                  <div class="bg-gray-50 p-3 rounded"><dt class="text-sm text-gray-500">Madre</dt><dd class="text-lg">{{ $d['mother_name'] ?? '—' }}</dd></div>
                  <div class="bg-gray-50 p-3 rounded sm:col-span-2"><dt class="text-sm text-gray-500">Padrinos</dt><dd class="text-lg">{{ $d['godparents'] ?? '—' }}</dd></div>
                @break
                @case('confirmacion')
                  <div class="bg-gray-50 p-3 rounded"><dt class="text-sm text-gray-500">Candidato</dt><dd class="text-lg">{{ $d['candidate_name'] ?? '—' }}</dd></div>
                  <div class="bg-gray-50 p-3 rounded"><dt class="text-sm text-gray-500">Padrino/Madrina</dt><dd class="text-lg">{{ $d['sponsor'] ?? '—' }}</dd></div>
                  <div class="bg-gray-50 p-3 rounded sm:col-span-2"><dt class="text-sm text-gray-500">Parroquia</dt><dd class="text-lg">{{ $d['parish'] ?? '—' }}</dd></div>
                @break
                @case('primera_comunion')
                  <div class="bg-gray-50 p-3 rounded"><dt class="text-sm text-gray-500">Grupo</dt><dd class="text-lg">{{ $d['group'] ?? '—' }}</dd></div>
                  <div class="bg-gray-50 p-3 rounded"><dt class="text-sm text-gray-500">Catequista</dt><dd class="text-lg">{{ $d['catechist'] ?? '—' }}</dd></div>
                  <div class="bg-gray-50 p-3 rounded sm:col-span-2"><dt class="text-sm text-gray-500">Colegio/Escuela</dt><dd class="text-lg">{{ $d['school'] ?? '—' }}</dd></div>
                @break
                @case('matrimonio')
                  <div class="bg-gray-50 p-3 rounded"><dt class="text-sm text-gray-500">Novia</dt><dd class="text-lg">{{ $d['bride_name'] ?? '—' }}</dd></div>
                  <div class="bg-gray-50 p-3 rounded"><dt class="text-sm text-gray-500">Novio</dt><dd class="text-lg">{{ $d['groom_name'] ?? '—' }}</dd></div>
                  <div class="bg-gray-50 p-3 rounded sm:col-span-2"><dt class="text-sm text-gray-500">Testigos</dt><dd class="text-lg">{{ $d['witnesses'] ?? '—' }}</dd></div>
                  <div class="bg-gray-50 p-3 rounded sm:col-span-2"><dt class="text-sm text-gray-500">Fecha matrimonio civil</dt><dd class="text-lg">{{ $d['civil_marriage_date'] ?? '—' }}</dd></div>
                @break
                @default
                  <div class="text-gray-500">—</div>
              @endswitch
            </dl>
          @endif
        </div>
        <div class="border-t pt-4">
          <h3 class="font-semibold mb-2">Intenciones</h3>
          @if($mass->intentions->isEmpty())
            <p class="text-gray-500">Sin intenciones aún.</p>
          @else
            <ul class="list-disc pl-5 space-y-1">
              @foreach($mass->intentions as $i)
                <li>{{ $i->public_text ?? $i->type }} — {{ $i->donor_name }}</li>
              @endforeach
            </ul>
          @endif
        </div>
        <div class="border-t pt-4">
          <h3 class="font-semibold mb-2">Historial de cambios</h3>
          @if(isset($histories) && $histories->isNotEmpty())
            <ul class="space-y-2">
              @foreach($histories as $h)
                <li class="p-3 bg-gray-50 rounded border border-gray-200">
                  <div class="text-sm text-gray-700">
                    <span class="font-medium">{{ ucfirst($h->action) }}</span>
                    por {{ $h->user?->name ?? 'Sistema' }}
                    <span class="text-gray-500">— {{ $h->created_at->diffForHumans() }}</span>
                  </div>
                  <div class="text-sm text-gray-600"><span class="font-medium">Justificación:</span> {{ $h->justification }}</div>
                </li>
              @endforeach
            </ul>
          @else
            <p class="text-gray-500">Sin registros todavía.</p>
          @endif
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
