@extends('layouts.admin')

@section('header')
<div class="flex items-center justify-between">
    <h2 class="font-semibold text-xl text-gray-800">Misas especiales</h2>
    <a href="{{ route('admin.special-masses.create') }}" class="px-3 py-2 rounded bg-emerald-600 text-white hover:bg-emerald-700">Nueva misa especial</a>
    </div>
@endsection

@section('content')
<div class="py-6">
    <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
        <div class="bg-white shadow-sm sm:rounded-lg">
            <div class="p-6">
                @if(isset($duplicates) && $duplicates->isNotEmpty())
                    <div class="mb-4 p-3 bg-amber-50 border border-amber-200 text-amber-800 rounded">
                        <div class="font-medium">Advertencia: hay horarios duplicados para misas especiales.</div>
                        <ul class="list-disc list-inside text-sm mt-1">
                            @foreach($duplicates as $d)
                                <li>{{ \Carbon\Carbon::parse($d->starts_at)->format('d/m/Y H:i') }} — {{ $d->c }} registros</li>
                            @endforeach
                        </ul>
                        <div class="text-xs text-amber-700 mt-1">Los nuevos duplicados están bloqueados; revisa y corrige estos existentes.</div>
                    </div>
                @endif
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Hora</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Categoría</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Padre</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Cupo</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Monto</th>
                                <th class="px-4 py-2"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($masses as $m)
                                <tr>
                                    <td class="px-4 py-2">{{ $m->starts_at->format('d/m/Y') }}</td>
                                    <td class="px-4 py-2">{{ $m->starts_at->format('H:i') }}</td>
                                    <td class="px-4 py-2">{{ str_replace('_',' ', ucfirst($m->special_category)) }}</td>
                                    <td class="px-4 py-2">{{ $m->priest?->name ?? '—' }}</td>
                                    <td class="px-4 py-2">{{ $m->occupied }}/{{ $m->capacity }}</td>
                                    <td class="px-4 py-2">{{ $m->reservation_amount !== null ? 'Q '.number_format($m->reservation_amount,2) : '—' }}</td>
                                    <td class="px-4 py-2 text-right">
                                        <a href="{{ route('admin.special-masses.show',$m) }}" class="text-blue-600 hover:underline">Ver</a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td class="px-4 py-6 text-gray-500" colspan="5">No hay misas especiales.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">{{ $masses->links() }}</div>
            </div>
        </div>
    </div>
</div>
@endsection
