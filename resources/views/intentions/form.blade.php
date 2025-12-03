@extends('layouts.app')

@section('title', 'Solicitar Intención de Misa - Basílica del Rosario')
@section('meta_description', 'Solicita una intención de misa en línea. Pago seguro con tarjeta de crédito o débito.')

@section('content')
    @php
        $heroAssets = \App\Support\HeroImage::resolve();
        $heroWebp = $heroAssets['hero_webp'];
        $heroJpg  = $heroAssets['hero_jpg'];
        $heroImg  = $heroAssets['final'];
        $regularMasses = $regularMasses ?? collect();
        $rosaryMasses = $rosaryMasses ?? collect();
        $hasMassOptions = $regularMasses->isNotEmpty() || $rosaryMasses->isNotEmpty();
        $parish = $portalParish ?? config('portal.parish');
        $parishContact = $parish['contact'] ?? [];
        $officeHours = $parish['office_hours'] ?? [];
    @endphp
    
    <!-- Hero Section -->
    <section class="relative min-h-[42vh] sm:min-h-[48vh] md:min-h-[50vh] w-full overflow-hidden rounded-b-3xl border-b border-carbon/10">
        @if($heroImg)
            <img src="{{ $heroImg }}" alt="Basílica del Rosario" class="absolute inset-0 h-full w-full object-cover" loading="lazy" decoding="async" width="1600" height="600">
        @else
            <div class="absolute inset-0 bg-gradient-to-br from-oro/30 to-oroscuro/20"></div>
        @endif
        <div class="absolute inset-0 bg-carbon/60 mix-blend-multiply"></div>
        <div class="relative z-10 mx-auto flex h-full max-w-5xl items-end px-4 sm:px-6 lg:px-8 pb-6">
            <div>
                <h1 class="font-serif text-3xl font-semibold text-crema drop-shadow">Solicitar Intención de Misa</h1>
                <p class="mt-1 text-crema/80 text-sm">Pago seguro en línea con tarjeta de crédito o débito.</p>
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8 py-12">
        <!-- Mensaje informativo -->
        <div class="mb-6 rounded-xl border border-oro/30 bg-gradient-to-br from-oro/10 to-oro/5 p-5">
            <div class="flex items-start gap-3">
                <svg class="w-6 h-6 text-oro flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div class="flex-1">
                    <h3 class="font-semibold text-carbon mb-1">Pago en línea disponible</h3>
                    <p class="text-sm text-carbon/70">
                        Procesamos tu ofrenda mediante la plataforma segura de Recurrente. Si notas algún inconveniente, podemos asistirte por nuestros canales directos:
                    </p>
                    <ul class="mt-3 text-sm text-carbon/80 space-y-1">
                        <li><span class="font-semibold">WhatsApp:</span> <a href="{{ $parishContact['whatsapp_link'] ?? '#' }}" class="text-oro hover:underline font-medium" target="_blank" rel="noopener">{{ $parishContact['whatsapp_display'] ?? $parishContact['whatsapp'] }}</a></li>
                        <li><span class="font-semibold">Teléfono:</span> <a href="{{ $parishContact['phone_link'] ?? '#' }}" class="text-oro hover:underline font-medium">{{ $parishContact['phone_display'] ?? $parishContact['phone'] }}</a></li>
                    </ul>
                </div>
            </div>
        </div>

        @if(session('errors') && session('errors')->any())
            <div class="mb-6 rounded-xl border border-red-500/30 bg-red-50 p-5">
                <p class="font-semibold text-red-800">Por favor corrige los siguientes errores:</p>
                <ul class="mt-2 space-y-1 text-sm text-red-700">
                    @foreach(session('errors')->all() as $error)
                        <li>• {{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Formulario -->
        <form method="POST" action="{{ route('intentions.checkout') }}" class="space-y-6" data-intention-form>
            @csrf

            <!-- Tipo de Intención -->
            <div class="rounded-xl border border-carbon/10 bg-gradient-to-br from-oro/15 to-oro/5 p-5 sm:p-6">
                <h2 class="font-serif text-xl font-semibold text-carbon mb-4">Tipo de Intención *</h2>
                
                <div class="space-y-3">
                    <label class="flex items-start gap-3 p-4 rounded-lg border border-carbon/10 bg-white/70 backdrop-blur cursor-pointer hover:border-oro/40 transition">
                        <input type="radio" name="intention_type" value="rosario" class="mt-1 text-oro focus:ring-oro" data-price="Q30.00" data-label="Rosario" {{ old('intention_type') === 'rosario' ? 'checked' : '' }}>
                        <div class="flex-1">
                            <p class="font-semibold text-carbon">Rosario</p>
                            <p class="text-sm text-carbon/70">Rezo del Santo Rosario</p>
                            <p class="text-sm font-semibold text-oro mt-1">Q30.00</p>
                        </div>
                    </label>

                    <label class="flex items-start gap-3 p-4 rounded-lg border border-carbon/10 bg-white/70 backdrop-blur cursor-pointer hover:border-oro/40 transition">
                        <input type="radio" name="intention_type" value="normal" class="mt-1 text-oro focus:ring-oro" data-price="Q50.00" data-label="Misa Rezada" {{ old('intention_type', 'normal') === 'normal' ? 'checked' : '' }}>
                        <div class="flex-1">
                            <p class="font-semibold text-carbon">Misa Rezada</p>
                            <p class="text-sm text-carbon/70">Santa Misa rezada</p>
                            <p class="text-sm font-semibold text-oro mt-1">Q50.00</p>
                        </div>
                    </label>

                    <label class="flex items-start gap-3 p-4 rounded-lg border border-carbon/10 bg-white/70 backdrop-blur cursor-pointer hover:border-oro/40 transition">
                        <input type="radio" name="intention_type" value="cantada" class="mt-1 text-oro focus:ring-oro" data-price="Q150.00" data-label="Misa Cantada" {{ old('intention_type') === 'cantada' ? 'checked' : '' }}>
                        <div class="flex-1">
                            <p class="font-semibold text-carbon">Misa Cantada</p>
                            <p class="text-sm text-carbon/70">Santa Misa con canto</p>
                            <p class="text-sm font-semibold text-oro mt-1">Q150.00</p>
                        </div>
                    </label>
                </div>
                @error('intention_type')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Datos de la Intención -->
            <div class="rounded-xl border border-carbon/10 bg-white/70 backdrop-blur p-5 sm:p-6">
                <h2 class="font-serif text-xl font-semibold text-carbon mb-5">Intención</h2>
                
                <div class="space-y-4">
                    <div>
                        <label for="public_text" class="block text-sm font-medium text-carbon mb-1.5">
                            Texto de la intención <span class="text-red-600">*</span>
                        </label>
                        <textarea 
                            id="public_text" 
                            name="public_text" 
                            rows="3" 
                            required
                            maxlength="500"
                            placeholder="Ej: Por la salud de Juan Pérez, Por el eterno descanso de María González"
                            class="w-full px-4 py-2.5 rounded-lg border border-carbon/20 bg-white/90 focus:border-oro focus:ring-2 focus:ring-oro/20 outline-none transition text-carbon resize-none"
                        >{{ old('public_text') }}</textarea>
                        <p class="mt-1 text-xs text-carbon/60">Máximo 500 caracteres</p>
                        @error('public_text')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="dedicatee_name" class="block text-sm font-medium text-carbon mb-1.5">
                            Dedicatario (opcional)
                        </label>
                        <input
                            type="text"
                            id="dedicatee_name"
                            name="dedicatee_name"
                            maxlength="255"
                            value="{{ old('dedicatee_name') }}"
                            placeholder="Nombre de la persona por la que se ofrece"
                            class="w-full px-4 py-2.5 rounded-lg border border-carbon/20 bg-white/90 focus:border-oro focus:ring-2 focus:ring-oro/20 outline-none transition text-carbon"
                        >
                        @error('dedicatee_name')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="category" class="block text-sm font-medium text-carbon mb-1.5">
                            Categoría (opcional)
                        </label>
                        <select 
                            id="category" 
                            name="category"
                            class="w-full px-4 py-2.5 rounded-lg border border-carbon/20 bg-white/90 focus:border-oro focus:ring-2 focus:ring-oro/20 outline-none transition text-carbon"
                        >
                            <option value="">Seleccionar...</option>
                            <option value="acciones_de_gracia" {{ old('category') === 'acciones_de_gracia' ? 'selected' : '' }}>Acción de Gracias</option>
                            <option value="peticiones" {{ old('category') === 'peticiones' ? 'selected' : '' }}>Peticiones</option>
                            <option value="difuntos" {{ old('category') === 'difuntos' ? 'selected' : '' }}>Por los Difuntos</option>
                        </select>
                        @error('category')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Selección de Misa -->
            <div class="rounded-xl border border-carbon/10 bg-gradient-to-br from-crema/40 to-white p-5 sm:p-6">
                <h2 class="font-serif text-xl font-semibold text-carbon mb-2">Selecciona la misa *</h2>
                <p class="text-sm text-carbon/70 mb-4">Elige la fecha y hora en la que deseas que se lea tu intención.</p>

                @if($hasMassOptions)
                    <div>
                        <label for="mass_instance_id" class="block text-sm font-medium text-carbon mb-1.5">Fecha y hora de la misa</label>
                        <select
                            id="mass_instance_id"
                            name="mass_instance_id"
                            required
                            class="w-full px-4 py-2.5 rounded-lg border border-carbon/20 bg-white/90 focus:border-oro focus:ring-2 focus:ring-oro/20 outline-none transition text-carbon"
                        >
                            <option value="">Selecciona una misa...</option>

                            @if($regularMasses->isNotEmpty())
                                <optgroup label="Misas regulares">
                                    @foreach($regularMasses as $mass)
                                        @php
                                            $dateLabel = $mass->starts_at->timezone(config('app.timezone'))->format('d/m/Y - h:i A');
                                        @endphp
                                        <option value="{{ $mass->id }}" data-mass-type="regular" {{ (int)old('mass_instance_id') === $mass->id ? 'selected' : '' }}>
                                            {{ $dateLabel }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endif

                            @if($rosaryMasses->isNotEmpty())
                                <optgroup label="Santo Rosario">
                                    @foreach($rosaryMasses as $mass)
                                        @php
                                            $dateLabel = $mass->starts_at->timezone(config('app.timezone'))->format('d/m/Y - h:i A');
                                        @endphp
                                        <option value="{{ $mass->id }}" data-mass-type="rosario" {{ (int)old('mass_instance_id') === $mass->id ? 'selected' : '' }}>
                                            {{ $dateLabel }} — Rosario
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endif
                        </select>
                        <p class="mt-2 text-xs text-carbon/60">Si no encuentras la fecha que deseas, por favor contáctanos por WhatsApp al <a href="{{ $parishContact['whatsapp_link'] ?? '#' }}" target="_blank" rel="noopener" class="text-oro font-semibold hover:underline">{{ $parishContact['whatsapp_display'] ?? $parishContact['whatsapp'] }}</a>.</p>
                    </div>
                @else
                    <div class="rounded-lg border border-amber-300 bg-amber-50 p-4 text-sm text-amber-900">
                        Por el momento no hay misas disponibles para solicitar en línea. Escríbenos a 
                        <a href="{{ $parishContact['whatsapp_link'] ?? '#' }}" class="text-amber-700 font-semibold hover:underline" target="_blank" rel="noopener">WhatsApp {{ $parishContact['whatsapp_display'] ?? $parishContact['whatsapp'] }}</a>
                        o llámanos al <a href="{{ $parishContact['phone_link'] ?? '#' }}" class="text-amber-700 font-semibold hover:underline">{{ $parishContact['phone_display'] ?? $parishContact['phone'] }}</a> para coordinar tu intención.
                    </div>
                @endif

                @error('mass_instance_id')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Datos del Donante -->
            <div class="rounded-xl border border-carbon/10 bg-white/70 backdrop-blur p-5 sm:p-6">
                <h2 class="font-serif text-xl font-semibold text-carbon mb-5">Tus Datos</h2>
                
                <div class="space-y-4">
                    <div>
                        <label for="donor_name" class="block text-sm font-medium text-carbon mb-1.5">
                            Nombre completo <span class="text-red-600">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="donor_name" 
                            name="donor_name" 
                            required
                            maxlength="255"
                            value="{{ old('donor_name') }}"
                            placeholder="Juan Pérez"
                            class="w-full px-4 py-2.5 rounded-lg border border-carbon/20 bg-white/90 focus:border-oro focus:ring-2 focus:ring-oro/20 outline-none transition text-carbon"
                        >
                        @error('donor_name')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="donor_email" class="block text-sm font-medium text-carbon mb-1.5">
                            Correo electrónico <span class="text-red-600">*</span>
                        </label>
                        <input 
                            type="email" 
                            id="donor_email" 
                            name="donor_email" 
                            required
                            maxlength="255"
                            value="{{ old('donor_email') }}"
                            placeholder="juan@ejemplo.com"
                            class="w-full px-4 py-2.5 rounded-lg border border-carbon/20 bg-white/90 focus:border-oro focus:ring-2 focus:ring-oro/20 outline-none transition text-carbon"
                        >
                        <p class="mt-1 text-xs text-carbon/60">Recibirás confirmación en este correo</p>
                        @error('donor_email')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="donor_phone" class="block text-sm font-medium text-carbon mb-1.5">
                            Teléfono (opcional)
                        </label>
                        <input 
                            type="tel" 
                            id="donor_phone" 
                            name="donor_phone"
                            maxlength="20"
                            value="{{ old('donor_phone') }}"
                            placeholder="+502 1234-5678"
                            class="w-full px-4 py-2.5 rounded-lg border border-carbon/20 bg-white/90 focus:border-oro focus:ring-2 focus:ring-oro/20 outline-none transition text-carbon"
                        >
                        @error('donor_phone')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Información de Pago -->
            <div class="rounded-xl border border-carbon/10 bg-white/70 backdrop-blur p-5 sm:p-6">
                <div class="flex items-start gap-3">
                    <svg class="w-6 h-6 text-oro flex-shrink-0 mt-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                    <div class="flex-1">
                        <h3 class="font-semibold text-carbon">Pago seguro</h3>
                        <p class="mt-1 text-sm text-carbon/70">
                            Al continuar, serás redirigido a la plataforma segura de Recurrente para completar tu pago con tarjeta de crédito o débito.
                        </p>
                        <p class="mt-2 text-xs text-carbon/60">
                            Tus datos están protegidos y la transacción es 100% segura.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Resumen -->
            <div class="rounded-xl border border-carbon/10 bg-white/80 backdrop-blur p-5 sm:p-6" data-intention-summary>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-carbon/70">Tipo seleccionado</span>
                    <span class="font-semibold" data-intention-type>Selecciona un tipo</span>
                </div>
                <div class="flex items-center justify-between text-sm mt-3">
                    <span class="text-carbon/70">Ofrenda</span>
                    <span class="font-semibold" data-intention-price>—</span>
                </div>
                <div class="flex items-center justify-between text-sm mt-3">
                    <span class="text-carbon/70">Misa / Rosario</span>
                    <span class="font-semibold text-right max-w-[14rem]" data-intention-mass>Elige una fecha</span>
                </div>
            </div>

            <!-- Botones de Acción -->
            <div class="flex flex-col sm:flex-row gap-3 justify-end">
                <a href="{{ route('home') }}" class="btn-secondary text-center">
                    Cancelar
                </a>
                <button type="submit" class="btn-primary inline-flex items-center justify-center {{ $hasMassOptions ? '' : 'opacity-60 cursor-not-allowed' }}" @if(!$hasMassOptions) disabled @endif>
                    Continuar al pago
                    <svg class="ml-2 w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </button>
            </div>
        </form>

        <!-- Información Adicional -->
        <div class="mt-8 rounded-xl border border-carbon/10 bg-white/70 backdrop-blur p-5 sm:p-6">
            <h3 class="font-serif text-lg font-semibold text-carbon mb-3">¿Necesitas ayuda?</h3>
            <p class="text-sm text-carbon/70 mb-3">
                Si tienes dudas o prefieres hacer tu solicitud presencialmente, contáctanos:
            </p>
            <ul class="space-y-2 text-sm text-carbon/80">
                <li>
                    <span class="font-medium">WhatsApp:</span> 
                    <a href="{{ $parishContact['whatsapp_link'] ?? '#' }}" class="text-oro hover:underline" target="_blank" rel="noopener">
                        {{ $parishContact['whatsapp_display'] ?? $parishContact['whatsapp'] }}
                    </a>
                <li>
                    <span class="font-medium">Horario de atención:</span> {{ $officeHours['weekdays'] ?? '' }}@if(!empty($officeHours['saturday'])) · {{ $officeHours['saturday'] }}@endif
                </li>
                    <a href="{{ $parishContact['phone_link'] ?? '#' }}" class="text-oro hover:underline">
                        {{ $parishContact['phone_display'] ?? $parishContact['phone'] }}
                    </a>
                </li>
                <li>
                    <span class="font-medium">Horario de atención:</span> Lunes a Viernes 9:00 - 12:00 y 15:00 - 17:00
                </li>
            </ul>
        </div>
    </section>
@endsection

