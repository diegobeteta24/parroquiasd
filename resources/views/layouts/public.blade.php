<!DOCTYPE html>
<html lang="es" class="h-full scroll-smooth">
<head>
    @php
        $parish = $portalParish ?? config('portal.parish');
        $parishContact = $parish['contact'] ?? [];
        $siteUrl = config('app.url') ?? url('/');
    @endphp
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name'))</title>
    <meta name="description" content="@yield('meta_description', 'Basílica del Rosario — Parroquia Santo Domingo. Intenciones de misa, horarios y contacto.')">
    <meta name="color-scheme" content="light dark">
    <meta name="robots" content="index,follow,max-image-preview:large">
    <meta name="referrer" content="strict-origin-when-cross-origin" />
    <meta name="theme-color" content="#111112" media="(prefers-color-scheme: dark)">
    <meta name="theme-color" content="#f5efe7" media="(prefers-color-scheme: light)">
    <meta property="og:title" content="@yield('og_title', 'Basílica del Rosario — Parroquia Santo Domingo')">
    <meta property="og:description" content="@yield('og_description', 'Solicita una intención de misa o consulta horarios y contacto.')">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <link rel="canonical" href="{{ url()->current() }}">
    <!-- Google Fonts optimized -->
    <link rel="preconnect" href="https://fonts.googleapis.com" fetchpriority="low">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin fetchpriority="low">
    <link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;600;700&family=Inter:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;600;700&family=Inter:wght@400;500;600;700&display=swap" media="print" onload="this.media='all'">
    <noscript>
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;600;700&family=Inter:wght@400;500;600;700&display=swap">
    </noscript>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="icon" href="/favicon.ico">
    @stack('head')
</head>
<body class="min-h-full bg-[#0f172a] text-[#F8F6F1] antialiased">
    <a href="#contenido" class="sr-only focus:not-sr-only focus:absolute focus:top-2 focus:left-2 focus:z-50 bg-[#BFA24E] text-[#0f172a] px-4 py-2 rounded">Saltar al contenido</a>
    <header class="border-b border-[#BFA24E]/20">
        <div class="max-w-7xl mx-auto px-4 py-4 flex items-center justify-between">
            <a href="{{ route('home') }}" class="font-semibold tracking-tight">{{ $parish['full_name'] ?? 'Parroquia Santo Domingo' }}</a>
            <nav class="hidden md:flex items-center gap-4 text-sm">
                <a href="{{ route('home') }}" class="hover:text-[#BFA24E]">Inicio</a>
                <a href="{{ route('historia') }}" class="hover:text-[#BFA24E]">Historia</a>
                <a href="{{ route('galeria') }}" class="hover:text-[#BFA24E]">Galería</a>
                <a href="{{ route('horarios') }}" class="hover:text-[#BFA24E]">Horarios</a>
                <a href="{{ route('contacto') }}" class="hover:text-[#BFA24E]">Contacto</a>
            </nav>
        </div>
    </header>

    <main id="contenido" class="max-w-7xl mx-auto px-4 py-10">
        @yield('content')
    </main>

    <footer class="border-t border-[#BFA24E]/20 py-6 text-center text-sm">
        <p>&copy; {{ now()->year }} Parroquia Santo Domingo</p>
    </footer>
    @stack('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('img:not([loading]):not([data-critical])').forEach(i=>i.loading='lazy');
        });
    </script>
</body>
</html>
