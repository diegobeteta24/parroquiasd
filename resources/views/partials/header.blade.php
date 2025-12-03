@php
    $parish = $portalParish ?? config('portal.parish');
@endphp
<header
    id="site-header"
    class="sticky top-0 z-30 backdrop-blur supports-[backdrop-filter]:bg-carbon/60 bg-carbon/90 border-b border-white/5 transition-[box-shadow,background,transform] duration-300 will-change-transform text-crema"
>
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 items-center justify-between">
            <a href="{{ url('/') }}" class="flex items-center gap-3 focus:outline-none focus-visible:ring-2 focus-visible:ring-oro/60 rounded">
                <img src="/favicon.ico" alt="Escudo Parroquia Santo Domingo" class="h-8 w-8" loading="eager" decoding="async" width="32" height="32">
                <span class="font-serif text-xl leading-tight">{{ $parish['short_name'] ?? 'Basílica del Rosario' }}</span>
            </a>

            <nav aria-label="Navegación principal" class="hidden md:flex items-center gap-6">
                <a href="{{ url('/') }}" class="hover:text-oro focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-oro/60 rounded">Inicio</a>
                <a href="{{ url('/historia') }}" class="hover:text-oro focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-oro/60 rounded">Historia</a>
                <a href="{{ url('/galeria') }}" class="hover:text-oro focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-oro/60 rounded">Galería</a>
                <a href="{{ url('/horarios') }}" class="hover:text-oro focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-oro/60 rounded">Horarios</a>
                <a href="{{ url('/contacto') }}" class="hover:text-oro focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-oro/60 rounded">Contacto</a>
            </nav>

            <button
                type="button"
                class="md:hidden inline-flex items-center justify-center rounded-md p-2 text-[var(--crema)] hover:text-oro focus:outline-none focus-visible:ring-2 focus-visible:ring-oro/60"
                aria-controls="mobile-menu"
                aria-expanded="false"
                aria-label="Abrir menú"
                data-menu-button
            >
                <x-ui.icon name="menu" class="h-6 w-6" />
            </button>
        </div>
    </div>

    <!-- Mobile menu -->
    <div id="mobile-menu" class="md:hidden hidden" data-menu-panel>
        <div class="space-y-1 px-2 pb-3 pt-2">
            <a href="{{ url('/') }}" class="block rounded px-3 py-2 hover:bg-white/5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-oro/60">Inicio</a>
            <a href="{{ url('/historia') }}" class="block rounded px-3 py-2 hover:bg-white/5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-oro/60">Historia</a>
            <a href="{{ url('/galeria') }}" class="block rounded px-3 py-2 hover:bg-white/5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-oro/60">Galería</a>
            <a href="{{ url('/horarios') }}" class="block rounded px-3 py-2 hover:bg-white/5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-oro/60">Horarios</a>
            <a href="{{ url('/contacto') }}" class="block rounded px-3 py-2 hover:bg-white/5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-oro/60">Contacto</a>
        </div>
    </div>
</header>