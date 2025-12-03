@props(['name' => 'menu', 'class' => 'h-5 w-5'])

@php
    $icons = [
        'menu' => '<path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />',
        'x' => '<path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />',
        'chevron-up' => '<path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7" />',
        'arrow-right' => '<path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" />',
    ];
@endphp

<svg class="{{ $class }}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
    {!! $icons[$name] ?? '' !!}
    <title class="sr-only">Icono</title>
    <desc class="sr-only">Decorativo</desc>
    <g class="sr-only">
        <!-- Oculta título/desc para lectores que no requieran -->
    </g>
    <style>title,desc{display:none}</style>
    <!-- Decorative icon -->
</svg>