@extends('layouts.public')

@section('title', 'Pago Exitoso - Basílica del Rosario')
@section('meta_description', 'Tu intención de misa ha sido registrada exitosamente.')

@section('content')
@php
    $parish = $portalParish ?? config('portal.parish');
    $parishContact = $parish['contact'] ?? [];
@endphp
<div class="max-w-2xl mx-auto text-center">
    <!-- Ícono de éxito -->
    <div class="mb-6 inline-flex items-center justify-center w-20 h-20 rounded-full bg-green-500/20 border-2 border-green-500">
        <svg class="w-10 h-10 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
        </svg>
    </div>

    <!-- Título -->
    <h1 class="font-serif text-3xl sm:text-4xl font-semibold text-crema mb-4">¡Pago Exitoso!</h1>

    <div class="mb-8 p-6 rounded-xl border border-[#BFA24E]/30 bg-[#BFA24E]/5">
        <p class="text-lg text-crema leading-relaxed">
            Tu intención de misa ha sido registrada exitosamente.
        </p>
        <p class="mt-3 text-crema/80">
            Recibirás un correo de confirmación con los detalles de tu intención en los próximos minutos.
        </p>

        @if($intention)
            <div class="mt-4 text-left text-sm text-crema/90 space-y-1">
                <p><span class="font-semibold text-[#BFA24E]">Intención:</span> {{ $intention->public_text ?? '—' }}</p>
                <p><span class="font-semibold text-[#BFA24E]">Solicitante:</span> {{ $intention->donor_name ?? '—' }}</p>
                @if($intention->dedicatee)
                    <p><span class="font-semibold text-[#BFA24E]">Dedicado a:</span> {{ $intention->dedicatee->name }}</p>
                @endif
                <p><span class="font-semibold text-[#BFA24E]">Código:</span> {{ $intention->code }}</p>
            </div>
        @elseif($awaitingCertificate)
            <p class="mt-4 text-sm text-yellow-200">
                Estamos terminando de generar tu constancia personalizada. Esto suele tardar unos segundos; vuelve a cargar esta página si el botón aún no aparece.
            </p>
        @endif
    </div>

    <!-- Información adicional -->
    <div class="mb-8 space-y-4 text-left">
        <div class="p-4 rounded-lg border border-[#BFA24E]/20 bg-[#0f172a]/30">
            <h3 class="font-semibold text-crema mb-2 flex items-center gap-2">
                <svg class="w-5 h-5 text-[#BFA24E]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                ¿Qué sigue?
            </h3>
            <ul class="space-y-2 text-sm text-crema/80">
                <li>• Tu intención será incluida en las próximas misas según disponibilidad</li>
                <li>• Recibirás un correo de confirmación</li>
                <li>• Puedes revisar tu recibo en el correo enviado</li>
            </ul>
        </div>

        <div class="p-4 rounded-lg border border-[#BFA24E]/20 bg-[#0f172a]/30">
            <h3 class="font-semibold text-crema mb-2 flex items-center gap-2">
                <svg class="w-5 h-5 text-[#BFA24E]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                ¿Necesitas ayuda?
            </h3>
            <p class="text-sm text-crema/80">
                Si tienes alguna consulta, puedes contactarnos:
            </p>
            <ul class="mt-2 space-y-1 text-sm text-crema/70">
                <li>
                    WhatsApp: 
                    <a href="{{ $parishContact['whatsapp_link'] ?? '#' }}" class="text-[#BFA24E] hover:underline" target="_blank" rel="noopener">
                        {{ $parishContact['whatsapp_display'] ?? $parishContact['whatsapp'] }}
                    </a>
                </li>
                <li>
                    Email: 
                    <a href="mailto:{{ $parishContact['email'] ?? '' }}" class="text-[#BFA24E] hover:underline">
                        {{ $parishContact['email'] ?? '' }}
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <!-- Acciones -->
    <div class="flex flex-col sm:flex-row gap-3 justify-center">
        @if($personalCertificateUrl)
            <a 
                href="{{ $personalCertificateUrl }}" 
                target="_blank"
                class="inline-flex items-center justify-center px-6 py-3 rounded-lg bg-[#BFA24E] text-[#0f172a] font-semibold hover:bg-[#BFA24E]/90 transition shadow-lg"
            >
                <svg class="mr-2 w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Descargar certificado personalizado
            </a>
        @endif
        <a 
            href="{{ $genericCertificateUrl }}" 
            class="inline-flex items-center justify-center px-6 py-3 rounded-lg {{ $personalCertificateUrl ? 'border border-[#BFA24E]/40 bg-transparent text-crema hover:bg-[#BFA24E]/10' : 'bg-[#BFA24E] text-[#0f172a] hover:bg-[#BFA24E]/90' }} font-semibold transition"
        >
            <svg class="mr-2 w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Descargar certificado genérico
        </a>
        <a 
            href="{{ route('home') }}" 
            class="inline-flex items-center justify-center px-6 py-3 rounded-lg border border-[#BFA24E]/30 bg-transparent text-crema font-semibold hover:bg-[#BFA24E]/10 transition"
        >
            <svg class="mr-2 w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
            Volver al inicio
        </a>
    </div>

    <!-- Enlace adicional a horarios -->
    <div class="mt-4">
        <a 
            href="{{ route('horarios') }}" 
            class="inline-flex items-center text-[#BFA24E] hover:text-[#BFA24E]/80 text-sm transition"
        >
            Ver horarios de misa
            <svg class="ml-1 w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
        </a>
    </div>

    <!-- Mensaje de gratitud -->
    <div class="mt-12 p-6 rounded-xl border border-[#BFA24E]/20 bg-gradient-to-br from-[#BFA24E]/10 to-[#BFA24E]/5">
        <p class="font-serif text-xl text-crema italic">
            "Que el Señor bendiga tu generosidad y escuche tu intención"
        </p>
        <p class="mt-2 text-sm text-crema/70">
            — Parroquia Santo Domingo
        </p>
    </div>
</div>
@endsection
