@extends('layouts.admin')

@section('title', 'Editar intención — ' . $intention->mass->starts_at->format('d/m/Y H:i'))

@php
    if (!isset($maxRepetitions)) {
        $maxRepetitions = (int) config('portal.intentions_max_repetitions', 1000);
    }
    $ignoreCapacity = $ignoreCapacity ?? config('portal.intentions_admin_ignore_capacity', true);
    $mass = $intention->mass;
    $typeOptions = $isRosary
        ? [
            'rosario' => [
                'label' => 'Rosario',
                'price' => 30,
                'description' => 'Intención exclusiva para la jornada del Rosario.',
                'badge' => 'Rosario',
            ],
        ]
        : [
            'rezada' => [
                'label' => 'Misa rezada',
                'price' => 50,
                'description' => 'Liturgia habitual. Hasta 4 intenciones por misa.',
                'badge' => 'Ordinaria',
            ],
            'cantada' => [
                'label' => 'Misa cantada',
                'price' => 150,
                'description' => 'Incluye coro / músicos. Cupo limitado.',
                'badge' => 'Ordinaria',
            ],
        ];
    $defaultType = old('type', $intention->type);
    $initialTimes = (int) old('times', old('novena') ? 9 : 1);
    $initialExtras = collect(old('extra_mass_ids', []))->filter()->map(fn ($id) => (int) $id)->values();
    $categoryOptions = [
        '' => 'Sin categoría',
        'acciones_de_gracia' => 'Acciones de gracia',
        'peticiones' => 'Peticiones',
        'difuntos' => 'Difuntos',
    ];
    $paymentOptions = [
        'cash' => ['label' => 'Efectivo', 'description' => 'Se cancela directamente en secretaría.'],
        'transfer' => ['label' => 'Transferencia', 'description' => 'Banco Industrial — subir boleta digital.'],
        'card' => ['label' => 'Tarjeta', 'description' => 'Pago en POS o enlace seguro.'],
    ];
    $occupied = $mass->occupied ?? $mass->intentions()->count();
    $capacity = $mass->capacity ?? 0;
    $available = $capacity > 0 ? max(0, $capacity - $occupied) : null;
    $capacityLabel = $ignoreCapacity ? '∞' : ($capacity ?: '—');
    $availableLabel = $ignoreCapacity ? '∞' : ($available ?? '∞');
    $availableClass = ($ignoreCapacity || ($available ?? 1) > 0) ? 'text-emerald-600' : 'text-red-600';
    $localizedDate = $mass->starts_at->copy()->locale('es')->translatedFormat('l d \d\e F, H:i');
    $dedicateeName = old('dedicatee', optional($intention->dedicatee)->name);
    $receipt = $intention->receipt;
    $receiptUrl = $receipt ? \Illuminate\Support\Facades\Storage::disk($receipt->disk)->url($receipt->path) : null;
    $groupSelection = collect(old('group_mass_ids', $siblings->pluck('mass_instance_id')->all()))
        ->map(fn ($id) => (string) $id)
        ->unique()
        ->values();
    $groupEditorState = [
        'enabled' => (bool) old('group_editor', $groupSelection->isNotEmpty()),
        'selection' => $groupSelection->all(),
    ];
    $forcePrepaid = $intention->channel !== 'online';
    $initialPrepaid = $forcePrepaid ? true : (bool) old('is_prepaid', $intention->is_prepaid);
    $intentionConfig = [
        'initialType' => $defaultType,
        'initialPaymentMethod' => old('payment_method', $intention->payment_method),
        'initialPrepaid' => $initialPrepaid,
        'initialNovena' => (bool) old('novena', false),
        'initialTimes' => max(1, $initialTimes),
        'initialExtra' => $initialExtras->all(),
        'suggestedIds' => ($nextMasses ?? collect())->pluck('id')->values()->all(),
        'prices' => collect($typeOptions)->mapWithKeys(fn ($option, $key) => [$key => $option['price']])->all(),
        'labels' => collect($typeOptions)->mapWithKeys(fn ($option, $key) => [$key => $option['label']])->all(),
        'hasReceipt' => (bool) $receipt,
        'maxTimes' => $maxRepetitions,
        'forcePrepaid' => $forcePrepaid,
    ];
@endphp

@section('header')
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Editar intención #{{ $intention->code ?? $intention->id }}
            </h2>
            <p class="text-sm text-gray-500 mt-1">Actualiza datos, repeticiones y adjuntos. Todas las ediciones requieren justificación.</p>
        </div>
        <a href="{{ route('admin.masses.show', $mass) }}" class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-gray-100 text-gray-700 hover:bg-gray-200 transition">
            <span aria-hidden="true">⟵</span> Volver a la misa
        </a>
    </div>
@endsection

@section('content')
<div class="py-6">
    <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8 space-y-6">
        <div class="grid gap-4 md:grid-cols-3">
            <div class="rounded-2xl border border-gray-100 bg-white/80 p-6 shadow-sm md:col-span-2">
                <p class="text-xs uppercase tracking-[0.3em] text-gray-500">Misa actual</p>
                <div class="mt-3 flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <h3 class="text-2xl font-semibold text-gray-900">{{ $localizedDate }}</h3>
                        <p class="mt-2 text-sm text-gray-600">
                            {{ $mass->is_special ? 'Celebración especial' : 'Misa ordinaria' }}
                            @if($mass->special_category)
                                • {{ ucfirst($mass->special_category) }}
                            @endif
                            @if($mass->priest)
                                • Celebrante: {{ $mass->priest->name }}
                            @endif
                        </p>
                        @if($mass->notes)
                            <p class="mt-2 text-sm text-gray-500">{{ $mass->notes }}</p>
                        @endif
                        <p class="mt-2 text-xs uppercase tracking-[0.2em] text-gray-400">Código interno: {{ $intention->code }}</p>
                    </div>
                    <div class="text-right space-y-2">
                        <span class="inline-flex items-center gap-2 rounded-full border border-indigo-200 bg-indigo-50 px-4 py-1 text-sm font-semibold text-indigo-700">
                            {{ $isRosary ? 'Rosario' : 'Misa ordinaria' }}
                        </span>
                        <p class="text-sm font-semibold {{ $intention->status === 'paid' ? 'text-emerald-600' : 'text-gray-600' }}">
                            Estado: {{ strtoupper($intention->status) }}
                        </p>
                    </div>
                </div>
            </div>
            <div class="rounded-2xl border border-gray-100 bg-white/80 p-6 shadow-sm">
                <p class="text-xs uppercase tracking-[0.3em] text-gray-500">Cupo</p>
                <dl class="mt-3 grid grid-cols-3 gap-3 text-center">
                    <div>
                        <dt class="text-sm text-gray-500">Capacidad</dt>
                        <dd class="text-lg font-semibold text-gray-900">{{ $capacityLabel }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-500">Registradas</dt>
                        <dd class="text-lg font-semibold text-gray-900">{{ $occupied }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-500">Disponibles</dt>
                        <dd class="text-lg font-semibold {{ $availableClass }}">
                            {{ $availableLabel }}
                        </dd>
                    </div>
                </dl>
                @if($ignoreCapacity)
                    <p class="mt-4 text-xs text-gray-500">
                        Este registro interno puede exceder el aforo publicado; usa la cifra como referencia.
                    </p>
                @else
                    <p class="mt-4 text-xs text-gray-500">
                        Las modificaciones respetarán el aforo automáticamente. Para Rosario no se limita el cupo.
                    </p>
                @endif
            </div>
        </div>

        @if ($errors->any())
            <div class="rounded-2xl border border-red-200 bg-red-50/80 p-4 text-sm text-red-800">
                <p class="font-semibold">Por favor corrige lo siguiente:</p>
                <ul class="mt-2 list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="rounded-3xl border border-gray-100 bg-white p-6 shadow-sm">
            <form
                x-data='editIntentionForm(@json($intentionConfig))'
                action="{{ route('admin.intentions.update', $intention) }}"
                method="POST"
                enctype="multipart/form-data"
                class="grid gap-8 lg:grid-cols-[minmax(0,1fr)_22rem]"
            >
                @csrf
                @method('PUT')

                <div class="space-y-6">
                    <section class="rounded-2xl border border-gray-200 p-6">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <p class="text-xs uppercase tracking-[0.3em] text-gray-400">Paso 1</p>
                                <h3 class="text-lg font-semibold text-gray-900">Detalles de la intención</h3>
                                <p class="text-sm text-gray-600">Ajusta el tipo de misa y el texto público.</p>
                            </div>
                            <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-600">
                                {{ $isRosary ? 'Solo Rosario' : 'Ordinaria' }}
                            </span>
                        </div>
                        <div class="mt-6 space-y-6">
                            <div>
                                <p class="text-sm font-medium text-gray-700">Tipo de misa</p>
                                <div class="mt-3 grid gap-3 sm:grid-cols-{{ count($typeOptions) > 1 ? '2' : '1' }}">
                                    @foreach($typeOptions as $value => $option)
                                        <label class="relative flex cursor-pointer gap-4 rounded-2xl border border-gray-200 bg-white/80 p-4 transition hover:border-indigo-300" :class="{'ring-2 ring-indigo-500 border-indigo-400 bg-indigo-50/60': itype === '{{ $value }}'}">
                                            <input type="radio" name="type" value="{{ $value }}" class="sr-only" x-model="itype" @checked($defaultType === $value)>
                                            <div class="space-y-1 text-sm">
                                                <p class="text-base font-semibold text-gray-900">{{ $option['label'] }}</p>
                                                <p class="text-gray-600">{{ $option['description'] }}</p>
                                            </div>
                                            <div class="ml-auto text-right">
                                                <p class="text-xs uppercase text-gray-400">Ofrenda</p>
                                                <p class="text-lg font-semibold text-indigo-600">Q{{ number_format($option['price'], 2) }}</p>
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                                @error('type')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="grid gap-6 sm:grid-cols-2">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Categoría</label>
                                    <select name="category" class="mt-2 block w-full rounded-2xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500">
                                        @foreach($categoryOptions as $value => $label)
                                            <option value="{{ $value }}" @selected(old('category', $intention->category ?? '') === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <p class="mt-1 text-xs text-gray-500">Ayuda a ordenar la hoja impresa.</p>
                                    @error('category')
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700">Texto público</label>
                                    <textarea name="public_text" rows="4" class="mt-2 block w-full rounded-2xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500" placeholder="Ej. En acción de gracias por…">{{ old('public_text', $intention->public_text) }}</textarea>
                                    <p class="mt-1 text-xs text-gray-500">Se imprimirá tal cual para el celebrante.</p>
                                    @error('public_text')
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="rounded-2xl border border-gray-200 p-6 space-y-6">
                        <div>
                            <p class="text-xs uppercase tracking-[0.3em] text-gray-400">Paso 2</p>
                            <h3 class="text-lg font-semibold text-gray-900">Contacto y dedicatoria</h3>
                            <p class="text-sm text-gray-600">Solo para seguimiento interno.</p>
                        </div>
                        <div class="grid gap-6 sm:grid-cols-2">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Donante</label>
                                <input type="text" name="donor_name" value="{{ old('donor_name', $intention->donor_name) }}" class="mt-2 block w-full rounded-2xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500" placeholder="Opcional">
                                @error('donor_name')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Teléfono</label>
                                <input type="text" name="phone" value="{{ old('phone', $intention->phone) }}" class="mt-2 block w-full rounded-2xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500">
                                @error('phone')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-medium text-gray-700">Correo electrónico</label>
                                <input type="email" name="email" value="{{ old('email', $intention->email) }}" class="mt-2 block w-full rounded-2xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500" placeholder="Opcional">
                                @error('email')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-medium text-gray-700">Dedicatario (opcional)</label>
                                <input type="text" name="dedicatee" value="{{ $dedicateeName }}" class="mt-2 block w-full rounded-2xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500" placeholder="Nombre completo">
                                <p class="mt-1 text-xs text-gray-500">Una intención por persona. Si no aplica, deje vacío.</p>
                                @error('dedicatee')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </section>

                    <section class="rounded-2xl border border-gray-200 p-6 space-y-6">
                        <div>
                            <p class="text-xs uppercase tracking-[0.3em] text-gray-400">Paso 3</p>
                            <h3 class="text-lg font-semibold text-gray-900">Pago y comprobante</h3>
                            <p class="text-sm text-gray-600">Actualiza el medio de pago o adjunta una nueva boleta.</p>
                        </div>
                        <div class="grid gap-3 sm:grid-cols-3">
                            @foreach($paymentOptions as $value => $option)
                                <label class="rounded-2xl border border-gray-200 bg-white/80 p-4 text-sm shadow-sm hover:border-indigo-300 cursor-pointer" :class="{'ring-2 ring-indigo-500 border-indigo-400': paymentMethod === '{{ $value }}'}">
                                    <input type="radio" name="payment_method" value="{{ $value }}" class="sr-only" x-model="paymentMethod" @checked(old('payment_method', $intention->payment_method) === $value)>
                                    <p class="font-semibold text-gray-900">{{ $option['label'] }}</p>
                                    <p class="mt-1 text-gray-600">{{ $option['description'] }}</p>
                                </label>
                            @endforeach
                        </div>
                        @error('payment_method')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <div class="grid gap-4 sm:grid-cols-2">
                            @if($forcePrepaid)
                                <input type="hidden" name="is_prepaid" value="1">
                                <div class="sm:col-span-2 space-y-1">
                                    <label class="inline-flex w-full items-center gap-3 rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-700">
                                        <input type="checkbox" value="1" class="rounded border-gray-300 text-indigo-600" x-model="isPrepaid" checked disabled>
                                        <span>Pagada (registro interno)</span>
                                    </label>
                                    <p class="text-xs text-gray-500">Esta intención no proviene del webhook, por lo que se considera pagada automáticamente.</p>
                                </div>
                            @else
                                <label class="inline-flex items-center gap-3 rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-700">
                                    <input type="checkbox" name="is_prepaid" value="1" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" x-model="isPrepaid" @checked(old('is_prepaid', $intention->is_prepaid))>
                                    <span>Marcar como ya pagada</span>
                                </label>
                            @endif
                            <div class="grid grid-cols-2 gap-3">
                                <input type="number" step="0.01" name="stipend_amount_gtq" value="{{ old('stipend_amount_gtq', $intention->stipend_amount_gtq) }}" placeholder="Monto Q" class="rounded-2xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500">
                                <input type="text" name="payment_ref" value="{{ old('payment_ref', $intention->payment_ref) }}" placeholder="Referencia" class="rounded-2xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                            @error('stipend_amount_gtq')
                                <p class="text-xs text-red-600">{{ $message }}</p>
                            @enderror
                            @error('payment_ref')
                                <p class="text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Comprobante (imagen)</label>
                            <input type="file" name="receipt" id="receipt" accept="image/*" class="mt-2 block w-full rounded-2xl border-dashed border-2 border-gray-200 bg-gray-50 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500" :required="requiresReceipt">
                            <p class="mt-1 text-xs" :class="requiresReceipt ? 'text-amber-600' : 'text-gray-500'" x-text="requiresReceipt ? 'Obligatorio si es transferencia o tarjeta sin marcar como pagada.' : 'Opcional. Usa esta sección para actualizar la boleta.'"></p>
                            @if($receiptUrl)
                                <p class="mt-2 text-xs text-gray-500">Comprobante actual: <a href="{{ $receiptUrl }}" target="_blank" class="text-indigo-600 underline">Ver / descargar</a></p>
                            @endif
                            @error('receipt')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="rounded-2xl border border-blue-100 bg-blue-50/70 p-4 text-sm text-blue-900">
                            Transferencia Banco Industrial a nombre de <strong>Parroquia Santo Domingo</strong>.<br>
                            Cuenta monetaria: <strong>093-000118-5</strong>. Recuerda subir intención, fecha, hora y <strong>fotografía de la boleta</strong>.
                        </div>
                    </section>

                    <section class="rounded-2xl border border-gray-200 p-6 space-y-6" id="group-block" x-data='@json($groupEditorState)'>
                        <input type="hidden" name="group_editor" :value="enabled ? 1 : 0">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <p class="text-xs uppercase tracking-[0.3em] text-gray-400">Plan recurrente</p>
                                <h3 class="text-lg font-semibold text-gray-900">Editar días de la novena existente</h3>
                                <p class="text-sm text-gray-600">Selecciona qué misas mantienen la intención (excluye la fecha actual).</p>
                            </div>
                            <label class="inline-flex items-center gap-3 rounded-full border border-gray-200 bg-gray-50 px-4 py-2 text-sm text-gray-700">
                                <input type="checkbox" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" x-model="enabled">
                                Activar edición
                            </label>
                        </div>
                        <div class="rounded-2xl border border-dashed border-gray-200 p-4" x-show="enabled" x-cloak>
                            @if(($nextMasses ?? collect())->isEmpty())
                                <p class="text-sm text-amber-700">No hay misas disponibles para reprogramar esta novena.</p>
                            @else
                                <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                                    @foreach(($nextMasses ?? collect()) as $m2)
                                        @php
                                            $checked = $groupSelection->contains($m2->id);
                                        @endphp
                                        <label class="flex items-center gap-3 rounded-2xl border border-gray-200 bg-white/70 p-3 text-sm text-gray-700">
                                            <input type="checkbox" name="group_mass_ids[]" value="{{ $m2->id }}" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" x-model="selection" :disabled="!enabled" @checked($checked)>
                                            <span>
                                                {{ $m2->starts_at->format('d/m/Y H:i') }}
                                                @if($m2->is_special && $m2->special_category === 'rosario')
                                                    • Rosario
                                                @endif
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                                <p class="mt-3 text-xs text-gray-500">Seleccionadas: <strong x-text="selection.length"></strong>. Se crearán/eliminarán registros para igualar esta lista.</p>
                            @endif
                        </div>
                        @error('group_mass_ids')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </section>

                    <section class="rounded-2xl border border-gray-200 p-6 space-y-6" id="novena-block">
                        <div>
                            <p class="text-xs uppercase tracking-[0.3em] text-gray-400">Agregar más fechas</p>
                            <h3 class="text-lg font-semibold text-gray-900">Modo novena (nuevas repeticiones)</h3>
                            <p class="text-sm text-gray-600">Activa el modo novena para sumar fechas adicionales automáticamente.</p>
                        </div>
                        <div class="flex flex-col gap-4 rounded-2xl border border-dashed border-gray-200 p-4 sm:flex-row sm:items-center sm:justify-between">
                            <label class="inline-flex items-center gap-3 text-sm font-medium text-gray-700">
                                <input type="checkbox" name="novena" value="1" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" x-model="novena" @checked(old('novena'))>
                                Repetir esta intención (agregar nuevas)
                            </label>
                            <div class="flex flex-wrap items-center gap-3 text-sm text-gray-600" x-show="novena">
                                <span>Plan: <strong x-text="plannedCount"></strong> misa(s)</span>
                                <span>Manual: <strong x-text="extra.length"></strong>/<span x-text="maxExtra"></span></span>
                                <span>Total estimado: <strong x-text="money(plannedTotal)"></strong></span>
                            </div>
                        </div>
                        <div class="grid gap-4 lg:grid-cols-2" x-show="novena" x-cloak>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">¿Cuántas misas nuevas?</label>
                                <input type="number" min="1" max="{{ $maxRepetitions }}" step="1" name="times" value="{{ max(1, $initialTimes) }}" class="mt-2 block w-32 rounded-2xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500" x-model.number="times">
                                <p class="mt-1 text-xs text-gray-500">Máximo {{ $maxRepetitions }} fechas consecutivas (incluye la actual).</p>
                                @error('times')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-700">Fechas manuales prioritarias</p>
                                <p class="mt-2 text-sm text-gray-600">
                                    Selecciona eventos clave y el sistema llenará el resto cronológicamente.
                                </p>
                            </div>
                        </div>
                        <div class="rounded-2xl border border-gray-200" x-show="novena" x-cloak>
                            <div id="mini-mass-picker" class="min-h-[460px]"></div>
                            <p class="px-4 pb-3 pt-2 text-xs text-gray-500">Haz clic en misas del mismo tipo dentro de las próximas {{ $maxRepetitions }} fechas.</p>
                        </div>
                        @if(($nextMasses ?? collect())->isEmpty())
                            <p class="rounded-xl border border-amber-200 bg-amber-50 p-3 text-sm text-amber-900">No hay misas disponibles en las próximas semanas para nuevas repeticiones.</p>
                        @else
                            <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-3" x-show="novena" x-cloak>
                                @foreach(($nextMasses ?? collect()) as $m2)
                                    <label class="flex items-center gap-3 rounded-2xl border border-gray-200 bg-white/70 p-3 text-sm text-gray-700">
                                        <input type="checkbox" name="extra_mass_ids[]" value="{{ $m2->id }}" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" x-model="extra" @checked(in_array($m2->id, old('extra_mass_ids', [])))>
                                        <span>{{ $m2->starts_at->format('d/m/Y H:i') }}</span>
                                    </label>
                                @endforeach
                            </div>
                        @endif
                        @error('extra_mass_ids')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </section>

                    <section class="rounded-2xl border border-gray-200 p-6 space-y-5">
                        <div>
                            <p class="text-xs uppercase tracking-[0.3em] text-gray-400">Justificación</p>
                            <h3 class="text-lg font-semibold text-gray-900">Motivo de la edición</h3>
                            <p class="text-sm text-gray-600">Se registrará en el historial y se usa para auditoría.</p>
                        </div>
                        <textarea name="justification" rows="3" class="mt-2 block w-full rounded-2xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500" placeholder="Ej. Se corrigió el texto público por solicitud del donante." required>{{ old('justification') }}</textarea>
                        @error('justification')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <div class="grid gap-3 sm:grid-cols-2">
                            <a href="{{ route('admin.masses.show', $mass) }}" class="inline-flex items-center justify-center rounded-2xl border border-gray-200 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Cancelar</a>
                            <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
                                Guardar cambios
                            </button>
                        </div>
                    </section>
                </div>

                <aside class="space-y-5">
                    <div class="rounded-2xl border border-indigo-100 bg-indigo-50/60 p-5">
                        <div class="flex items-center justify-between">
                            <h3 class="text-base font-semibold text-indigo-900">Resumen en vivo</h3>
                            <span class="rounded-full bg-white px-3 py-1 text-sm font-semibold text-indigo-700" x-text="money(plannedTotal)"></span>
                        </div>
                        <dl class="mt-4 space-y-3 text-sm text-indigo-900">
                            <div class="flex items-center justify-between">
                                <dt>Tipo seleccionado</dt>
                                <dd class="font-semibold" x-text="readableType"></dd>
                            </div>
                            <div class="flex items-center justify-between">
                                <dt>Monto unitario</dt>
                                <dd class="font-semibold" x-text="money(unit)"></dd>
                            </div>
                            <div class="flex items-center justify-between">
                                <dt>Plan de repeticiones</dt>
                                <dd class="font-semibold"><span x-text="plannedCount"></span> misa<span x-text="plannedCount === 1 ? '' : 's'"></span></dd>
                            </div>
                            <div class="flex items-center justify-between">
                                <dt>Manual seleccionadas</dt>
                                <dd><span x-text="extra.length"></span>/<span x-text="maxExtra"></span></dd>
                            </div>
                            <div class="flex items-center justify-between">
                                <dt>Estado del pago</dt>
                                <dd x-text="isPrepaid ? 'Marcada como pagada' : 'Pendiente de cobro'"></dd>
                            </div>
                            <div class="flex items-center justify-between">
                                <dt>Comprobante</dt>
                                <dd x-text="requiresReceipt ? 'Obligatorio' : (hasReceipt ? 'Registrado' : 'Opcional')"></dd>
                            </div>
                        </dl>
                        <p class="mt-4 text-xs text-indigo-800" x-show="requiresReceipt">
                            Debes adjuntar la imagen de la boleta antes de guardar.
                        </p>
                    </div>

                    <div class="rounded-2xl border border-gray-200 bg-gray-50/80 p-5 text-sm text-gray-700 space-y-4">
                        <div>
                            <h3 class="text-base font-semibold text-gray-900">Intenciones hermanas</h3>
                            @if($siblings->isEmpty())
                                <p class="mt-2 text-sm text-gray-600">Esta intención no tiene repeticiones asociadas.</p>
                            @else
                                <ul class="mt-3 space-y-2">
                                    @foreach($siblings as $sib)
                                        <li class="flex items-center justify-between rounded-xl border border-gray-200 bg-white/70 px-3 py-2">
                                            <span class="text-sm text-gray-700">{{ $sib->mass?->starts_at?->format('d/m/Y H:i') ?? '—' }}</span>
                                            <span class="text-xs font-semibold text-gray-500">ID {{ $sib->id }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                        <div class="border-t border-dashed border-gray-200 pt-4">
                            <label class="block text-sm font-semibold text-gray-900" for="move_to_mass_id">Mover a otra misa</label>
                            <select name="move_to_mass_id" id="move_to_mass_id" class="mt-2 block w-full rounded-2xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Mantener fecha actual</option>
                                @foreach(($nextMasses ?? collect()) as $m2)
                                    <option value="{{ $m2->id }}" @selected(old('move_to_mass_id') == $m2->id)>{{ $m2->starts_at->format('d/m/Y H:i') }}</option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-xs text-gray-500">Solo se permiten misas del mismo tipo y con cupo.</p>
                            @error('move_to_mass_id')
                                <p class="text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="rounded-2xl border border-gray-200 bg-gray-50/80 p-5 text-sm text-gray-700">
                        <h3 class="text-base font-semibold text-gray-900">Recordatorios rápidos</h3>
                        <ul class="mt-3 space-y-2 list-disc pl-4">
                            <li>Toda modificación queda registrada con fecha, usuario y justificación.</li>
                            <li>Si marcas «Ya pagada» se moverá a estado <strong>PAID</strong> y pedirá fecha de cobro.</li>
                            <li>Al editar el plan recurrente solo se afectan las fechas futuras seleccionadas.</li>
                            <li>Para Rosario solo se permiten intenciones de tipo Rosario.</li>
                        </ul>
                    </div>
                </aside>
            </form>
        </div>
    </div>
</div>

<script>
const ensureFullCalendarAssets = (() => {
    let promise;
    return () => {
        if (promise) return promise;
        promise = new Promise(async (resolve, reject) => {
            const ensureCss = (href) => {
                if (!document.querySelector(`link[rel="stylesheet"][href="${href}"]`)) {
                    const link = document.createElement('link');
                    link.rel = 'stylesheet';
                    link.href = href;
                    document.head.appendChild(link);
                }
            };
            ensureCss('https://cdn.jsdelivr.net/npm/fullcalendar@6.1.19/main.min.css');
            if (!window.FullCalendar) {
                const script = document.createElement('script');
                script.src = 'https://cdn.jsdelivr.net/npm/fullcalendar@6.1.19/index.global.min.js';
                script.async = true;
                script.onload = () => resolve();
                script.onerror = () => reject(new Error('No se pudo cargar FullCalendar'));
                document.head.appendChild(script);
            } else {
                resolve();
            }
        });
        return promise;
    };
})();

const initMiniMassPicker = async () => {
    if (window.__miniMassPicker) return;
    const mount = document.getElementById('mini-mass-picker');
    if (!mount) return;

    await ensureFullCalendarAssets().catch(() => {});
    const Calendar = window.FullCalendar?.Calendar;
    if (!Calendar) return;

    const getComponent = () => {
        const form = mount.closest('form');
        return form && form.__x && form.__x.$data ? form.__x.$data : null;
    };

    const calendar = new Calendar(mount, {
        initialView: 'dayGridMonth',
        locale: 'es',
        timeZone: 'America/Guatemala',
        height: 'auto',
        selectable: false,
        initialDate: '{{ now(config('app.timezone'))->toDateString() }}',
        events: (info, success, failure) => {
            fetch(`{{ route('admin.mass-events') }}?start=${info.startStr}&end=${info.endStr}`)
                .then((response) => response.json())
                .then(success)
                .catch(failure);
        },
        eventClick: (info) => {
            info.jsEvent?.preventDefault();
            const comp = getComponent();
            if (!comp || !Array.isArray(comp.extra)) return;
            const id = parseInt(info.event.id, 10);
            const allowedSet = new Set(Array.isArray(comp.suggested) ? comp.suggested : []);
            const isRosaryEvent = !!info.event.extendedProps?.is_special && info.event.extendedProps?.special_category === 'rosario';
            const thisIsRosary = {{ $isRosary ? 'true' : 'false' }};
            if (isRosaryEvent !== thisIsRosary) return;
            if (!allowedSet.has(id)) {
                try { info.el.animate([{ opacity: 1 }, { opacity: 0.5 }, { opacity: 1 }], { duration: 200 }); } catch (e) {}
                return;
            }
            const maxSelectable = Math.max(0, comp.maxExtra);
            const idx = comp.extra.indexOf(id);
            if (idx >= 0) {
                comp.extra.splice(idx, 1);
                info.el.classList.remove('ring-2', 'ring-amber-400');
                info.el.style.opacity = '';
            } else {
                if (comp.extra.length >= maxSelectable) {
                    try { info.el.animate([{ transform: 'scale(1)' }, { transform: 'scale(1.03)' }, { transform: 'scale(1)' }], { duration: 180 }); } catch (e) {}
                    return;
                }
                comp.extra.push(id);
                info.el.classList.add('ring-2', 'ring-amber-400');
                info.el.style.opacity = '0.85';
            }
        },
        eventDidMount: (arg) => {
            const comp = getComponent();
            const id = parseInt(arg.event.id, 10);
            if (comp && Array.isArray(comp.extra) && comp.extra.includes(id)) {
                arg.el.classList.add('ring-2', 'ring-amber-400');
                arg.el.style.opacity = '0.85';
            }
            const allowedSet = new Set(Array.isArray(comp?.suggested) ? comp.suggested : []);
            const isRosaryEvent = !!arg.event.extendedProps?.is_special && arg.event.extendedProps?.special_category === 'rosario';
            const thisIsRosary = {{ $isRosary ? 'true' : 'false' }};
            if (isRosaryEvent !== thisIsRosary || !allowedSet.has(id)) {
                arg.el.style.opacity = 0.25;
                arg.el.style.pointerEvents = 'none';
                arg.el.title = 'Solo puede seleccionar eventos del mismo tipo y dentro de las próximas {{ $maxRepetitions }} fechas';
            }
        },
    });

    calendar.render();
    window.__miniMassPicker = calendar;
};

document.addEventListener('DOMContentLoaded', () => {
    initMiniMassPicker();
    const novenaToggle = document.querySelector('input[name="novena"]');
    if (novenaToggle) {
        novenaToggle.addEventListener('change', () => {
            if (novenaToggle.checked) {
                setTimeout(() => document.dispatchEvent(new Event('init-mini-mass-picker')), 120);
            }
        }, { once: true });
    }
});

document.addEventListener('init-mini-mass-picker', () => {
    setTimeout(() => {
        if (!window.__miniMassPicker) {
            initMiniMassPicker();
        }
    }, 60);
});

function editIntentionForm(config) {
    return {
        itype: config.initialType,
        paymentMethod: config.initialPaymentMethod,
        isPrepaid: !!config.initialPrepaid,
        novena: !!config.initialNovena,
        times: config.initialTimes ?? 1,
        extra: Array.isArray(config.initialExtra) ? config.initialExtra.slice() : [],
        suggested: Array.isArray(config.suggestedIds) ? config.suggestedIds : [],
        prices: config.prices || {},
        labels: config.labels || {},
        hasReceipt: !!config.hasReceipt,
        maxTimes: Number(config.maxTimes ?? 30),
        forcePrepaid: !!config.forcePrepaid,
        get unit() {
            return Number(this.prices[this.itype] ?? 0);
        },
        get plannedCount() {
            const base = this.novena ? parseInt(this.times, 10) || 1 : 1;
            return Math.max(1, Math.min(this.maxTimes, base));
        },
        get maxExtra() {
            return Math.max(0, this.plannedCount - 1);
        },
        get plannedTotal() {
            return this.unit * this.plannedCount;
        },
        get requiresReceipt() {
            if (this.forcePrepaid) return false;
            if (this.isPrepaid) return false;
            if (!['transfer', 'card'].includes(this.paymentMethod)) return false;
            return !this.hasReceipt;
        },
        get readableType() {
            return this.labels[this.itype] || '—';
        },
        money(value) {
            const number = Number(value || 0);
            return 'Q ' + number.toLocaleString('es-GT', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        },
        init() {
            if (this.forcePrepaid) {
                this.isPrepaid = true;
            }
            this.$watch('times', () => {
                const allowed = this.maxExtra;
                if (this.extra.length > allowed) {
                    this.extra.splice(allowed);
                }
            });
            this.$watch('novena', (active) => {
                if (!active) {
                    this.times = 1;
                    this.extra = [];
                } else if (this.times < 2) {
                    this.times = 9;
                }
                if (active) {
                    setTimeout(() => document.dispatchEvent(new Event('init-mini-mass-picker')), 120);
                }
            });
        },
    };
}
</script>
@endsection
