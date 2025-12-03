<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Acceso — {{ config('app.name') }}</title>
    <meta name="robots" content="noindex,nofollow" />
    @vite(['resources/css/app.css','resources/js/app.js'])
    @if (class_exists(\Livewire\Livewire::class))
        @livewireStyles
    @endif
</head>
<body class="min-h-full bg-slate-950 text-white antialiased">
    <div class="absolute inset-0 bg-gradient-to-br from-indigo-900 via-slate-900 to-indigo-950 opacity-90"></div>
    <div class="absolute inset-0 pointer-events-none">
        <div class="h-full w-full bg-[radial-gradient(circle_at_top,_rgba(255,255,255,0.08),_transparent_45%)]"></div>
    </div>
    <div class="relative z-10 min-h-full flex items-center justify-center py-16 px-6">
        <div class="w-full max-w-md">
            @yield('content')
        </div>
    </div>
    @if (class_exists(\Livewire\Livewire::class))
        @livewireScripts
    @endif
</body>
</html>
