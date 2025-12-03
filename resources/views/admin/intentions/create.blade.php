@extends('layouts.admin')

@section('title', 'Registrar intención — ' . $mass->starts_at->format('d/m/Y H:i'))

@php
    if (!isset($maxRepetitions)) {
        $maxRepetitions = (int) config('portal.intentions_max_repetitions', 1000);
    }
    $ignoreCapacity = $ignoreCapacity ?? config('portal.intentions_admin_ignore_capacity', true);
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
    $defaultType = old('type', array_key_first($typeOptions));
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
    $intentionConfig = [
        'initialType' => $defaultType,
        'initialPaymentMethod' => old('payment_method', 'cash'),
        'initialPrepaid' => (bool) old('is_prepaid', true),
        'initialNovena' => (bool) old('novena'),
        'initialTimes' => max(1, $initialTimes),
        'initialExtra' => $initialExtras->all(),
        'suggestedIds' => ($nextMasses ?? collect())->pluck('id')->values()->all(),
        'prices' => collect($typeOptions)->mapWithKeys(fn ($option, $key) => [$key => $option['price']])->all(),
        'labels' => collect($typeOptions)->mapWithKeys(fn ($option, $key) => [$key => $option['label']])->all(),
        'maxTimes' => $maxRepetitions,
        'forcePrepaid' => true,
    ];
@endphp

@section('header')
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Registrar intención — {{ $mass->starts_at->format('d/m/Y H:i') }}
            </h2>
            <p class="text-sm text-gray-500 mt-1">Completa el formulario y confirma en segundos.</p>
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
                <p class="text-xs uppercase tracking-[0.3em] text-gray-500">Misa seleccionada</p>
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
                        @if($mass->starts_at->isToday())
                            <p class="mt-3 text-sm font-semibold text-red-600">No es posible agendar intenciones para el mismo día.</p>
                        @endif
                    </div>
                    <div class="text-right">
                        <span class="inline-flex items-center gap-2 rounded-full border border-indigo-200 bg-indigo-50 px-4 py-1 text-sm font-semibold text-indigo-700">
                            {{ $isRosary ? 'Rosario' : 'Misa ordinaria' }}
                        </span>
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
                        Registro interno sin tope: el número de lugares es referencial y puedes excederlo según sea necesario.
                    </p>
                @else
                    <p class="mt-4 text-xs text-gray-500">
                        Las intenciones adicionales respetarán el aforo automáticamente. Para Rosario no se limita el cupo.
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
                x-data='intentionForm(@json($intentionConfig))'
                action="{{ route('admin.intentions.store', $mass) }}"
                method="POST"
                enctype="multipart/form-data"
                class="grid gap-8 lg:grid-cols-[minmax(0,1fr)_22rem]"
            >
                @csrf

                <div class="space-y-6">
                    <section class="rounded-2xl border border-gray-200 p-6">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <p class="text-xs uppercase tracking-[0.3em] text-gray-400">Paso 1</p>
                                <h3 class="text-lg font-semibold text-gray-900">Detalles de la intención</h3>
                                <p class="text-sm text-gray-600">Selecciona el tipo de misa y describe la intención.</p>
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
                                <p class="px-4 pb-3 pt-2 text-xs text-gray-500">Haz clic en misas del mismo tipo dentro de las próximas {{ $maxRepetitions }} fechas.</p>
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
                                            <option value="{{ $value }}" @selected(old('category', '') === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <p class="mt-1 text-xs text-gray-500">Ayuda a ordenar la hoja impresa.</p>
                                    @error('category')
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700">Texto público</label>
                                    <textarea name="public_text" rows="4" class="mt-2 block w-full rounded-2xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500" placeholder="Ej. En acción de gracias por…">{{ old('public_text') }}</textarea>
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
                            <p class="text-sm text-gray-600">Estos datos solo se usan para seguimiento interno.</p>
                        </div>
                        <div class="grid gap-6 sm:grid-cols-2">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Donante</label>
                                <input type="text" name="donor_name" value="{{ old('donor_name') }}" class="mt-2 block w-full rounded-2xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500" placeholder="Opcional">
                                @error('donor_name')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Teléfono</label>
                                <input type="text" name="phone" value="{{ old('phone') }}" class="mt-2 block w-full rounded-2xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500">
                                @error('phone')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-medium text-gray-700">Correo electrónico</label>
                                <input type="email" name="email" value="{{ old('email') }}" class="mt-2 block w-full rounded-2xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500" placeholder="Opcional">
                                @error('email')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-medium text-gray-700">Dedicatario (opcional)</label>
                                <input type="text" name="dedicatee" value="{{ old('dedicatee') }}" class="mt-2 block w-full rounded-2xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500" placeholder="Nombre completo">
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
                            <p class="text-sm text-gray-600">Define el medio de pago y adjunta respaldo si es necesario.</p>
                        </div>
                        <div class="grid gap-3 sm:grid-cols-3">
                            @foreach($paymentOptions as $value => $option)
                                <label class="rounded-2xl border border-gray-200 bg-white/80 p-4 text-sm shadow-sm hover:border-indigo-300 cursor-pointer" :class="{'ring-2 ring-indigo-500 border-indigo-400': paymentMethod === '{{ $value }}'}">
                                    <input type="radio" name="payment_method" value="{{ $value }}" class="sr-only" x-model="paymentMethod" @checked(old('payment_method', 'cash') === $value)>
                                    <p class="font-semibold text-gray-900">{{ $option['label'] }}</p>
                                    <p class="mt-1 text-gray-600">{{ $option['description'] }}</p>
                                </label>
                            @endforeach
                        </div>
                        @error('payment_method')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <div class="grid gap-4 sm:grid-cols-2">
                            <input type="hidden" name="is_prepaid" value="1">
                            <div class="sm:col-span-2 space-y-1">
                                <label class="inline-flex w-full items-center gap-3 rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-700">
                                    <input type="checkbox" value="1" class="rounded border-gray-300 text-indigo-600" x-model="isPrepaid" checked disabled>
                                    <span>Pagada (registros internos)</span>
                                </label>
                                <p class="text-xs text-gray-500">Las intenciones registradas manualmente ya incluyen su pago.</p>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <input type="number" step="0.01" name="stipend_amount_gtq" value="{{ old('stipend_amount_gtq') }}" placeholder="Monto Q" class="rounded-2xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500">
                                <input type="text" name="payment_ref" value="{{ old('payment_ref') }}" placeholder="Referencia" class="rounded-2xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Comprobante (imagen)</label>
                            <input type="file" name="receipt" id="receipt" accept="image/*" class="mt-2 block w-full rounded-2xl border-dashed border-2 border-gray-200 bg-gray-50 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500" :required="requiresReceipt">
                            <p class="mt-1 text-xs" :class="requiresReceipt ? 'text-amber-600' : 'text-gray-500'" x-text="requiresReceipt ? 'Obligatorio para transferencias y tarjeta (si no está prepagada).' : 'Opcional. Úsalo para subir boletas o evidencias.'"></p>
                            @error('receipt')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="rounded-2xl border border-blue-100 bg-blue-50/70 p-4 text-sm text-blue-900">
                            Transferencia Banco Industrial a nombre de <strong>Parroquia Santo Domingo</strong>.<br>
                            Cuenta monetaria: <strong>093-000118-5</strong>. Enviar intención, fecha, hora y <strong>fotografía de la boleta</strong>.
                        </div>
                    </section>

                    <section class="rounded-2xl border border-gray-200 p-6 space-y-6" id="novena-block">
                        <div>
                            <p class="text-xs uppercase tracking-[0.3em] text-gray-400">Paso 4</p>
                            <h3 class="text-lg font-semibold text-gray-900">Repeticiones y calendario</h3>
                            <p class="text-sm text-gray-600">Activa el modo novena para repetir automáticamente y selecciona fechas clave.</p>
                        </div>
                        <div class="flex flex-col gap-4 rounded-2xl border border-dashed border-gray-200 p-4 sm:flex-row sm:items-center sm:justify-between">
                            <label class="inline-flex items-center gap-3 text-sm font-medium text-gray-700">
                                <input type="checkbox" name="novena" value="1" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" x-model="novena" @checked(old('novena'))>
                                Repetir esta intención (modo novena)
                            </label>
                            <div class="flex flex-wrap items-center gap-3 text-sm text-gray-600" x-show="novena">
                                <span>Plan: <strong x-text="plannedCount"></strong> misa(s)</span>
                                <span>Manual: <strong x-text="extra.length"></strong>/<span x-text="maxExtra"></span></span>
                                <span>Total estimado: <strong x-text="money(plannedTotal)"></strong></span>
                            </div>
                        </div>
                        <div class="grid gap-4 lg:grid-cols-2" x-show="novena" x-cloak>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">¿Cuántas misas desea?</label>
                                <input type="number" min="1" max="{{ $maxRepetitions }}" step="1" name="times" value="{{ max(1, $initialTimes) }}" class="mt-2 block w-32 rounded-2xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500" x-model.number="times">
                                <p class="mt-1 text-xs text-gray-500">Máximo {{ $maxRepetitions }} fechas consecutivas.</p>
                                @error('times')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-700">Fechas manuales</p>
                                <p class="mt-2 text-sm text-gray-600">
                                    Selecciona las fechas estratégicas. El sistema completará el resto en orden cronológico.
                                </p>
                            </div>
                        </div>
                        <div class="rounded-2xl border border-gray-200" x-show="novena" x-cloak>
                            <div id="mini-mass-picker" class="min-h-[460px]"></div>
                            <p class="px-4 pb-3 pt-2 text-xs text-gray-500">Haz clic en eventos del mismo tipo para agregarlos o quitarlos. Rosarios en rojo; ordinarias en verde.</p>
                        </div>
                        @if(($nextMasses ?? collect())->isEmpty())
                            <p class="rounded-xl border border-amber-200 bg-amber-50 p-3 text-sm text-amber-900">No hay misas disponibles en las próximas semanas para repetir esta intención.</p>
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

                    <div class="flex flex-col gap-3 sm:flex-row sm:justify-end">
                        <a href="{{ route('admin.masses.show', $mass) }}" class="inline-flex items-center justify-center rounded-2xl border border-gray-200 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Cancelar</a>
                        <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
                            Registrar intención
                        </button>
                    </div>
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
                                <dd x-text="requiresReceipt ? 'Obligatorio' : 'Opcional'"></dd>
                            </div>
                        </dl>
                        <p class="mt-4 text-xs text-indigo-800" x-show="requiresReceipt">
                            Recuerda subir la imagen de la boleta antes de guardar.
                        </p>
                    </div>

                    <div class="rounded-2xl border border-gray-200 bg-gray-50/80 p-5 text-sm text-gray-700">
                        <h3 class="text-base font-semibold text-gray-900">Recordatorios rápidos</h3>
                        <ul class="mt-3 space-y-2 list-disc pl-4">
                            <li>Una intención por persona. Usa el campo Dedicatario para el nombre público.</li>
                            <li>Si marcas &ldquo;Ya pagada&rdquo;, se registrará como <strong>PAID</strong> y no pedirá recibo.</li>
                            <li>Las repeticiones siguen el orden cronológico. Selecciona manualmente fechas clave (misas especiales, aniversarios, etc.).</li>
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

function intentionForm(config) {
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
            return !this.isPrepaid && ['transfer', 'card'].includes(this.paymentMethod);
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
