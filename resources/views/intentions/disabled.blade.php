@extends('layouts.site')

@section('title', 'Intenciones en línea')

@section('content')
@php
    $parish = $portalParish ?? config('portal.parish');
    $parishContact = $parish['contact'] ?? [];
@endphp
<div class="max-w-3xl mx-auto py-16 px-6">
    <div class="bg-white border border-gray-200 shadow-xl rounded-2xl p-10 text-center">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-rose-50 text-rose-500 mb-6">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-8 h-8">
                <path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25ZM11.25 8.25a.75.75 0 0 1 1.5 0v5.25a.75.75 0 0 1-1.5 0V8.25Zm.75 9.75a1.125 1.125 0 1 0 0-2.25 1.125 1.125 0 0 0 0 2.25Z" clip-rule="evenodd" />
            </svg>
        </div>
        <h1 class="text-2xl font-semibold text-gray-900 mb-4">Intenciones en línea no disponibles</h1>
        <p class="text-gray-600 leading-relaxed">
            Estamos realizando ajustes en nuestro sistema de pagos en línea. Por el momento las intenciones solo se pueden coordinar de forma presencial o por nuestros canales directos.
        </p>
        <div class="mt-8 grid gap-4 sm:grid-cols-2">
            <a href="{{ $parishContact['whatsapp_link'] ?? '#' }}" target="_blank" rel="noopener" class="flex items-center justify-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-700 font-medium hover:bg-emerald-100 transition">
                <span>WhatsApp {{ $parishContact['whatsapp_display'] ?? $parishContact['whatsapp'] }}</span>
            </a>
            <a href="{{ $parishContact['phone_link'] ?? '#' }}" class="flex items-center justify-center gap-2 rounded-xl border border-sky-200 bg-sky-50 px-4 py-3 text-sky-700 font-medium hover:bg-sky-100 transition">
                <span>Teléfono {{ $parishContact['phone_display'] ?? $parishContact['phone'] }}</span>
            </a>
        </div>
        <p class="mt-6 text-sm text-gray-500">Gracias por tu comprensión y generosidad.</p>
    </div>
</div>
@endsection
