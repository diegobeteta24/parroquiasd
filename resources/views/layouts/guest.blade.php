<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts / Assets -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        {{-- Livewire solo si la vista lo solicita: @section('use_livewire', true) --}}
        @if (class_exists(\Livewire\Livewire::class) && View::hasSection('use_livewire'))
            @livewireStyles
        @endif
    </head>
    <body>
        <div class="font-sans text-gray-900 dark:text-gray-100 antialiased">
            {{ $slot }}
        </div>

        @if (class_exists(\Livewire\Livewire::class) && View::hasSection('use_livewire'))
            @livewireScripts
        @endif
    </body>
</html>
