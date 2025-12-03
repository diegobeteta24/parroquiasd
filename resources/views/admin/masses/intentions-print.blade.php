@extends('layouts.admin')

@section('title', 'Hoja de intenciones — ' . $mass->starts_at->format('d/m/Y H:i'))

@section('header')
    <div class="flex items-center justify-between">
        <h2 class="font-semibold text-xl text-gray-800">Hoja de intenciones — {{ $mass->starts_at->format('d/m/Y H:i') }}</h2>
        <button onclick="window.print()" class="px-3 py-2 rounded bg-amber-600 text-white hover:bg-amber-700 print:hidden">Imprimir hoja</button>
    </div>
@endsection

@section('content')
<style>
@media print {
    header, nav, .print\:hidden { display: none !important; }
    main { padding: 0 !important; }
}
table { width:100%; border-collapse: collapse; }
th, td { padding: .5rem; border-bottom: 1px solid #e5e7eb; vertical-align: top; }
th { text-transform: uppercase; font-size: .75rem; color: #6b7280; text-align: left; }
</style>

<div class="py-6">
    <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
        <div class="bg-white shadow-sm sm:rounded-lg">
            <div class="p-6">
                <div class="mb-3 text-sm text-gray-600">
                    Estado: <strong>{{ ucfirst($mass->status) }}</strong>
                    @if($mass->priest) — Sacerdote: <strong>{{ $mass->priest->name }}</strong>@endif
                </div>
                <div class="overflow-x-auto">
                    <table>
                        <thead>
                            <tr>
                                <th style="width:14%">Categoría</th>
                                <th>Texto público</th>
                                <th style="width:22%">Donante</th>
                                <th style="width:32%">Dedicatario</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($mass->intentions as $i)
                            <tr>
                                <td>{{ $i->category ? str_replace('_',' ', ucfirst($i->category)) : ucfirst($i->type) }}</td>
                                <td>{{ $i->public_text }}</td>
                                <td>{{ $i->donor_name }}</td>
                                <td>
                                    @php $d = $i->dedicatee ?? $i->dedicatees->first(); @endphp
                                    {{ $d?->name ?? '—' }}
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-sm text-gray-500">No hay intenciones.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
