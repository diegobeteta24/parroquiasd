@extends('layouts.admin')

@section('title', 'Misa del ' . $mass->starts_at->format('d/m/Y H:i'))

@section('header')
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Misa — {{ $mass->starts_at->format('d/m/Y H:i') }}
        </h2>
        <div class="flex flex-wrap gap-2">
            @if(!$mass->starts_at->isToday())
                <a href="{{ route('admin.intentions.create', $mass) }}" class="inline-flex items-center px-3 py-2 bg-emerald-600 text-white rounded hover:bg-emerald-700 transition">
                    Registrar intención
                </a>
            @endif
            <a href="{{ route('admin.masses.print', $mass) }}" class="inline-flex items-center px-3 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 transition">
                Imprimir hoja
            </a>
            <a href="{{ route('admin.masses.pdf', $mass) }}" class="inline-flex items-center px-3 py-2 bg-amber-600 text-white rounded hover:bg-amber-700 transition">
                <img src="/favicon.ico" alt="PDF" class="w-4 h-4 mr-2"> Descargar PDF
            </a>
            <a href="{{ route('admin.mass-calendar') }}" class="inline-flex items-center px-3 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 transition">Volver al calendario</a>
        </div>
    </div>
@endsection

@section('content')
<div class="py-6">
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
        <div class="bg-white shadow-sm sm:rounded-lg">
            <div class="p-6">
                @livewire('admin.assign-priest-modal', ['mass' => $mass])
                @if(session('status'))
                    <div class="mb-4 p-3 rounded bg-emerald-50 border border-emerald-200 text-emerald-800">
                        {{ session('status') }}
                        @if(session('new_intention_id'))
                            — <a class="underline font-medium" href="{{ route('admin.intentions.certificate', session('new_intention_id')) }}" target="_blank">Descargar diploma</a>
                        @endif
                    </div>
                @endif
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-6">
                    <div class="bg-gray-50 p-4 rounded">
                        <dt class="text-sm text-gray-500">Fecha y hora</dt>
                        <dd class="text-lg font-medium text-gray-900">{{ $mass->starts_at->format('d/m/Y H:i') }}</dd>
                    </div>
                    <div class="bg-gray-50 p-4 rounded">
                        <dt class="text-sm text-gray-500">Estado</dt>
                        <dd class="text-lg font-medium text-gray-900">{{ ucfirst($mass->status) }}</dd>
                    </div>
                    <div class="bg-gray-50 p-4 rounded sm:col-span-2 flex items-center justify-between"
                         x-data="{ priestName: @js($mass->priest?->name ?? '— No asignado —') }"
                         x-on:priest-updated.window="priestName = $event.detail.name || '— No asignado —'">
                        <div>
                            <dt class="text-sm text-gray-500">Sacerdote asignado</dt>
                            <dd class="text-lg font-medium text-gray-900" x-text="priestName"></dd>
                        </div>
                        <div>
                            <button type="button" x-data x-on:click="$dispatch('open-assign-priest')" class="inline-flex items-center px-3 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Asignar/Editar Padre</button>
                        </div>
                    </div>
                </dl>

                <div class="border-t pt-4">
                    <h3 class="text-lg font-semibold mb-3">Intenciones</h3>
                    @if($mass->intentions->isEmpty())
                        <p class="text-gray-500">No hay intenciones registradas para esta misa.</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead>
                                    <tr class="bg-gray-50">
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Texto público</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Donante</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dedicatario</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pago</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                        <th class="px-4 py-2"></th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($mass->intentions as $i)
                                        <tr>
                                            <td class="px-4 py-2 whitespace-nowrap">{{ ucfirst($i->type) }}</td>
                                            <td class="px-4 py-2">{{ $i->public_text }}</td>
                                            <td class="px-4 py-2 whitespace-nowrap">{{ $i->donor_name }}</td>
                                            <td class="px-4 py-2">
                                                @php $d = $i->dedicatee ?? $i->dedicatees->first(); @endphp
                                                @if(!$d)
                                                    <span class="text-gray-400">—</span>
                                                @else
                                                    <span class="text-gray-700">{{ $d->name ?? ($d->first_name.' '.$d->last_name) }}</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-2 whitespace-nowrap">
                                                <span class="inline-flex items-center gap-1">
                                                    <span class="text-gray-700">{{ strtoupper($i->payment_method ?? 'cash') }}</span>
                                                    @if($i->receipt)
                                                        <a href="{{ Storage::disk($i->receipt->disk)->url($i->receipt->path) }}" target="_blank" class="text-blue-600 hover:underline">Comprobante</a>
                                                    @endif
                                                </span>
                                            </td>
                                            <td class="px-4 py-2 whitespace-nowrap">{{ ucfirst($i->status) }}</td>
                                            <td class="px-4 py-2 text-right space-x-3 whitespace-nowrap">
                                                <a href="{{ route('admin.intentions.edit', $i) }}" class="text-blue-600 hover:text-blue-800 hover:underline">Editar</a>
                                                <a href="{{ route('admin.intentions.certificate', $i) }}" target="__blank" class="text-indigo-600 hover:text-indigo-800 hover:underline">Diploma</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
