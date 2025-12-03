<!DOCTYPE html>
<html lang="es" class="h-full scroll-smooth">
<head>
    @php
        $parish = $portalParish ?? config('portal.parish');
        $parishAddress = $parish['address'] ?? [];
        $parishContact = $parish['contact'] ?? [];
        $parishSocial = array_values(array_filter($parish['social'] ?? []));
        $siteUrl = config('app.url') ?? url('/');
    @endphp
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Basílica de Nuestra Señora del Rosario — Parroquia Santo Domingo')</title>
    <meta name="description" content="@yield('meta_description', 'Sitio institucional de la Basílica de Nuestra Señora del Rosario — Parroquia Santo Domingo. Intenciones de misa, horarios y contacto.')">
    <meta name="robots" content="index,follow,max-image-preview:large,max-snippet:-1,max-video-preview:-1">
    <!-- Seguridad: estos headers se envían por Nginx; se eliminan meta http-equiv inválidos para evitar warnings -->
    <meta name="referrer" content="strict-origin-when-cross-origin" />
    <meta name="theme-color" content="#111112" />
    <meta name="color-scheme" content="light dark" />
        <link rel="canonical" href="{{ url()->current() }}">

        <meta property="og:title" content="@yield('og_title', 'Basílica de Nuestra Señora del Rosario — Parroquia Santo Domingo')">
        <meta property="og:description" content="@yield('og_description', 'Solicita una intención de misa o consulta horarios y contacto.')">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ $parish['short_name'] ?? 'Basílica del Rosario' }}">
        <meta property="og:url" content="{{ url()->current() }}">
        <meta property="og:image" content="@yield('og_image', asset('favicon.ico'))">

        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="@yield('twitter_title', 'Basílica de Nuestra Señora del Rosario — Parroquia Santo Domingo')">
        <meta name="twitter:description" content="@yield('twitter_description', 'Solicita una intención de misa o consulta horarios y contacto.')">
        <meta name="twitter:image" content="@yield('twitter_image', asset('favicon.ico'))">

    <link rel="icon" href="/favicon.ico">
    <link rel="apple-touch-icon" href="/favicon.ico">
    <meta name="theme-color" content="#111112" media="(prefers-color-scheme: dark)">
    <meta name="theme-color" content="#f5efe7" media="(prefers-color-scheme: light)">
    <link rel="alternate" hreflang="es" href="{{ url('/') }}">

        <!-- Google Fonts (optimización: preload + swap sin bloquear render) -->
    <link rel="preconnect" href="https://fonts.googleapis.com" fetchpriority="low">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin fetchpriority="low">
    <link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;600;700&family=Inter:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;600;700&family=Inter:wght@400;500;600;700&display=swap" media="print" onload="this.media='all'">
    <noscript>
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;600;700&family=Inter:wght@400;500;600;700&display=swap">
    </noscript>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('preload')
    @stack('critical-preload')
        @php
            $shouldLoadLivewire = class_exists(\Livewire\Livewire::class) && (auth()->check() || View::hasSection('use_livewire'));
        @endphp
        @if($shouldLoadLivewire)
            @livewireStyles
        @endif

    <!-- JSON-LD base -->
        <script type="application/ld+json">
        {!! json_encode([
            '@context' => 'https://schema.org',
            '@graph' => [
                [
                    '@type' => 'Organization',
                    'name' => $parish['full_name'] ?? 'Parroquia Santo Domingo',
                    'url' => $siteUrl,
                    'logo' => asset('favicon.ico'),
                    'telephone' => $parishContact['phone'] ?? null,
                    'email' => $parishContact['email'] ?? null,
                    'sameAs' => $parishSocial,
                    'address' => [
                        '@type' => 'PostalAddress',
                        'streetAddress' => trim(($parishAddress['street'] ?? '') . ' ' . ($parishAddress['zone'] ?? '')),
                        'addressLocality' => $parishAddress['city'] ?? 'Ciudad de Guatemala',
                        'addressRegion' => $parishAddress['region'] ?? 'Guatemala',
                        'postalCode' => $parishAddress['postal_code'] ?? '01001',
                        'addressCountry' => $parishAddress['country_code'] ?? 'GT',
                    ],
                ],
                [
                    '@type' => 'Place',
                    'name' => $parish['short_name'] ?? 'Basílica del Rosario',
                    'url' => $siteUrl,
                    'address' => [
                        '@type' => 'PostalAddress',
                        'streetAddress' => trim(($parishAddress['street'] ?? '') . ' ' . ($parishAddress['zone'] ?? '')),
                        'addressLocality' => $parishAddress['city'] ?? 'Ciudad de Guatemala',
                        'addressRegion' => $parishAddress['region'] ?? 'Guatemala',
                        'postalCode' => $parishAddress['postal_code'] ?? '01001',
                        'addressCountry' => $parishAddress['country_code'] ?? 'GT',
                    ],
                    'geo' => isset($parishAddress['geo']) ? [
                        '@type' => 'GeoCoordinates',
                        'latitude' => $parishAddress['geo']['lat'],
                        'longitude' => $parishAddress['geo']['lng'],
                    ] : null,
                ],
                [
                    '@type' => 'WebSite',
                    'url' => $siteUrl,
                    'name' => $parish['short_name'] ?? 'Basílica del Rosario',
                    'inLanguage' => 'es',
                ],
            ],
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
        </script>
        @hasSection('structured_data')
            @yield('structured_data')
        @endif
    @stack('head')
    @stack('structured-data')
</head>
<body class="min-h-full bg-crema text-carbon antialiased">
        <a href="#contenido" class="sr-only focus:not-sr-only focus:absolute focus:top-2 focus:left-2 focus:z-50 bg-oro text-carbon px-4 py-2 rounded">Saltar al contenido</a>

        @include('partials.header')

    <main id="contenido" class="relative px-4 sm:px-0">
                @yield('content')
        </main>

        @include('partials.footer')

        <button
                type="button"
                class="fixed bottom-6 right-6 z-40 hidden items-center gap-2 rounded-full bg-oro text-carbon px-4 py-3 shadow-lg focus:outline-none focus-visible:ring-2 focus-visible:ring-oro/60"
                aria-label="Volver arriba"
                data-backtotop
        >
                <x-ui.icon name="chevron-up" class="h-5 w-5" />
                <span class="text-sm font-medium">Arriba</span>
        </button>

    @stack('modals')
    @if($shouldLoadLivewire ?? false)
        @livewireScripts
    @endif
    @stack('scripts')
    <!-- Lazy-load defensivo: añade loading="lazy" a imágenes no críticas que no lo tengan -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Evita tocar imágenes marcadas como críticas mediante data-critical
            document.querySelectorAll('img:not([loading]):not([data-critical])').forEach(img => {
                img.setAttribute('loading', 'lazy');
            });
        });
    </script>
</body>
</html>
