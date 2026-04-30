@php
    $parish = $portalParish ?? config('portal.parish');
@endphp
<!-- Barra superior con lema dominicano -->
<div class="bg-gradient-to-r from-oroscuro via-oro to-oroscuro text-carbon text-center py-1 text-xs font-medium tracking-wider hidden sm:block">
    <span class="italic">Laudare • Benedicere • Praedicare</span>
    <span class="mx-2">|</span>
    <span>Orden de Predicadores desde 1216</span>
</div>
<header
    id="site-header"
    class="sticky top-0 z-30 backdrop-blur supports-[backdrop-filter]:bg-carbon/60 bg-carbon/90 border-b border-white/5 transition-[box-shadow,background,transform] duration-300 will-change-transform text-crema"
>
    <div class="mx-auto max-w-7xl px-3 sm:px-6 lg:px-8">
        <div class="flex h-14 sm:h-16 items-center justify-between">
            <a href="{{ url('/') }}" class="flex items-center gap-2 sm:gap-3 focus:outline-none focus-visible:ring-2 focus-visible:ring-oro/60 rounded min-w-0">
                <img src="/favicon.ico" alt="Escudo Parroquia Santo Domingo" class="h-7 w-7 sm:h-8 sm:w-8 flex-shrink-0" loading="eager" decoding="async" width="32" height="32">
                <div class="flex flex-col min-w-0">
                    <span class="font-serif text-base sm:text-xl leading-tight truncate">{{ $parish['short_name'] ?? 'Basílica del Rosario' }}</span>
                    <span class="text-[10px] text-crema/60 tracking-wide hidden lg:block">Parroquia Santo Domingo de Guzmán</span>
                </div>
            </a>

            <nav aria-label="Navegación principal" class="hidden md:flex items-center gap-6">
                <a href="{{ url('/') }}" class="hover:text-oro focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-oro/60 rounded">Inicio</a>
                <a href="{{ url('/historia') }}" class="hover:text-oro focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-oro/60 rounded">Historia</a>
                <a href="{{ url('/galeria') }}" class="hover:text-oro focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-oro/60 rounded">Galería</a>
                <a href="{{ url('/horarios') }}" class="hover:text-oro focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-oro/60 rounded">Horarios</a>
                <a href="{{ url('/contacto') }}" class="hover:text-oro focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-oro/60 rounded">Contacto</a>
                <a href="{{ url('/intenciones') }}" class="ml-2 inline-flex items-center gap-2 rounded-full bg-oro px-4 py-2 text-sm font-semibold text-carbon shadow hover:bg-oroscuro transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-oro/60">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    Ofrecer Misa
                </a>
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
    <div id="mobile-menu" class="md:hidden hidden border-t border-white/10" data-menu-panel>
        <div class="px-3 py-4 space-y-1 bg-carbon/95">
            <a href="{{ url('/') }}" class="flex items-center gap-3 rounded-lg px-4 py-3 hover:bg-white/5 active:bg-white/10 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-oro/60 transition-colors">
                <span class="text-lg">🏠</span>
                <span>Inicio</span>
            </a>
            <a href="{{ url('/historia') }}" class="flex items-center gap-3 rounded-lg px-4 py-3 hover:bg-white/5 active:bg-white/10 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-oro/60 transition-colors">
                <span class="text-lg">📜</span>
                <span>Historia</span>
            </a>
            <a href="{{ url('/galeria') }}" class="flex items-center gap-3 rounded-lg px-4 py-3 hover:bg-white/5 active:bg-white/10 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-oro/60 transition-colors">
                <span class="text-lg">📷</span>
                <span>Galería</span>
            </a>
            <a href="{{ url('/horarios') }}" class="flex items-center gap-3 rounded-lg px-4 py-3 hover:bg-white/5 active:bg-white/10 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-oro/60 transition-colors">
                <span class="text-lg">⏰</span>
                <span>Horarios</span>
            </a>
            <a href="{{ url('/contacto') }}" class="flex items-center gap-3 rounded-lg px-4 py-3 hover:bg-white/5 active:bg-white/10 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-oro/60 transition-colors">
                <span class="text-lg">📞</span>
                <span>Contacto</span>
            </a>
            <div class="pt-2 mt-2 border-t border-white/10">
                <a href="{{ url('/intenciones') }}" class="flex items-center justify-center gap-2 rounded-lg px-4 py-3 bg-gradient-to-r from-oro to-oroscuro text-carbon font-semibold shadow-lg active:scale-[0.98] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-oro/60 transition-transform">
                    <span class="text-lg">✠</span>
                    <span>Ofrecer una Santa Misa</span>
                </a>
            </div>
        </div>
    </div>
</header>