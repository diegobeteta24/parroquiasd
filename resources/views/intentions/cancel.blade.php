@extends('layouts.public')

@section('title', 'Pago Cancelado - Basílica del Rosario')
@section('meta_description', 'Has cancelado el proceso de pago.')

@section('content')
@php
    $parish = $portalParish ?? config('portal.parish');
    $parishContact = $parish['contact'] ?? [];
    $parishAddress = $parish['address'] ?? [];
    $officeHours = $parish['office_hours'] ?? [];
@endphp
<div class="max-w-2xl mx-auto text-center">
    <!-- Ícono de cancelación -->
    <div class="mb-6 inline-flex items-center justify-center w-20 h-20 rounded-full bg-orange-500/20 border-2 border-orange-500">
        <svg class="w-10 h-10 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
        </svg>
    </div>

    <!-- Título -->
    <h1 class="font-serif text-3xl sm:text-4xl font-semibold text-crema mb-4">
        Pago Cancelado
    </h1>

    <!-- Mensaje -->
    <div class="mb-8 p-6 rounded-xl border border-orange-500/30 bg-orange-500/5">
        <p class="text-lg text-crema leading-relaxed">
            Has cancelado el proceso de pago.
        </p>
        <p class="mt-3 text-crema/80">
            Tu intención no ha sido registrada. Puedes intentarlo de nuevo cuando lo desees.
        </p>
    </div>

    <!-- Información -->
    <div class="mb-8 p-6 rounded-xl border border-[#BFA24E]/20 bg-[#0f172a]/30 text-left">
        <h3 class="font-semibold text-crema mb-3 flex items-center gap-2">
            <svg class="w-5 h-5 text-[#BFA24E]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            ¿Qué puedes hacer?
        </h3>
        <ul class="space-y-3 text-sm text-crema/80">
            <li class="flex items-start gap-2">
                <svg class="w-5 h-5 text-[#BFA24E] flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Intentar nuevamente el pago en línea</span>
            </li>
            <li class="flex items-start gap-2">
                <svg class="w-5 h-5 text-[#BFA24E] flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Visitar la parroquia presencialmente ({{ $officeHours['weekdays'] ?? '' }})</span>
            </li>
            <li class="flex items-start gap-2">
                <svg class="w-5 h-5 text-[#BFA24E] flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Contactarnos por WhatsApp o teléfono para solicitar tu intención</span>
            </li>
        </ul>
    </div>

    <!-- Opciones de contacto -->
    <div class="mb-8 p-6 rounded-xl border border-[#BFA24E]/20 bg-[#0f172a]/30 text-left">
        <h3 class="font-semibold text-crema mb-3">Formas alternativas de solicitar tu intención</h3>
        <ul class="space-y-3 text-sm text-crema/80">
            <li class="flex items-center gap-3">
                <div class="flex items-center justify-center w-10 h-10 rounded-full bg-green-500/20">
                    <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.890-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="font-medium text-crema">WhatsApp</p>
                    <a href="{{ $parishContact['whatsapp_link'] ?? '#' }}" class="text-[#BFA24E] hover:underline" target="_blank" rel="noopener">
                        {{ $parishContact['whatsapp_display'] ?? $parishContact['whatsapp'] }}
                    </a>
                </div>
            </li>
            <li class="flex items-center gap-3">
                <div class="flex items-center justify-center w-10 h-10 rounded-full bg-[#BFA24E]/20">
                    <svg class="w-5 h-5 text-[#BFA24E]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="font-medium text-crema">Teléfono</p>
                    <a href="{{ $parishContact['phone_link'] ?? '#' }}" class="text-[#BFA24E] hover:underline">
                        {{ $parishContact['phone_display'] ?? $parishContact['phone'] }}
                    </a>
                </div>
            </li>
            <li class="flex items-center gap-3">
                <div class="flex items-center justify-center w-10 h-10 rounded-full bg-[#BFA24E]/20">
                    <svg class="w-5 h-5 text-[#BFA24E]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="font-medium text-crema">Dirección</p>
                    <p class="text-crema/70 text-xs">
                        {{ $parishAddress['street'] ?? '' }} {{ $parishAddress['zone'] ?? '' }}, {{ $parishAddress['city'] ?? 'Ciudad de Guatemala' }}
                    </p>
                </div>
            </li>
        </ul>
    </div>

    <!-- Acciones -->
    <div class="flex flex-col sm:flex-row gap-3 justify-center">
        <a 
            href="{{ route('intentions.checkout') }}" 
            class="inline-flex items-center justify-center px-6 py-3 rounded-lg bg-[#BFA24E] text-[#0f172a] font-semibold hover:bg-[#BFA24E]/90 transition shadow-lg"
        >
            <svg class="mr-2 w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            Intentar nuevamente
        </a>
        <a 
            href="{{ route('home') }}" 
            class="inline-flex items-center justify-center px-6 py-3 rounded-lg border border-[#BFA24E]/30 bg-transparent text-crema font-semibold hover:bg-[#BFA24E]/10 transition"
        >
            Volver al inicio
        </a>
    </div>
</div>
@endsection
