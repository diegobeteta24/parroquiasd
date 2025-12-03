<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name')) — Administración</title>

    @vite(['resources/css/app.css','resources/js/app.js'])
    @if (class_exists(\Livewire\Livewire::class))
        @livewireStyles
    @endif
</head>
<body class="min-h-full bg-gray-100 font-sans antialiased">
    <script>
        // Fuerza modo claro para el área de administración
        try {
            localStorage.setItem('theme', 'light');
            document.documentElement.classList.remove('dark');
        } catch (e) { /* ignore */ }
    </script>
    <x-banner />

    @include('navigation-menu')

    @hasSection('header')
        <header class="bg-white shadow">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                @yield('header')
            </div>
        </header>
    @endif

    <main class="px-4 sm:px-0">
        {{-- Livewire slot (components using ->layout) --}}
        @isset($slot)
            {{ $slot }}
        @endisset
        {{-- Blade section fallback (classic @extends views) --}}
        @yield('content')
    </main>

    @stack('modals')
    @if (class_exists(\Livewire\Livewire::class))
        @livewireScripts
    @endif
    @stack('scripts')
</body>
</html>
