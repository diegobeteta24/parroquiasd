@php
    $parish = $portalParish ?? config('portal.parish');
    $parishContact = $parish['contact'] ?? [];
    $parishAddress = $parish['address'] ?? [];
@endphp
<footer class="mt-16 border-t border-white/5 bg-[#0b0b0b] text-crema">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-10">
            <div>
                <div class="flex items-center gap-3">
                    <img src="/favicon.ico" alt="Escudo Parroquia Santo Domingo" class="h-8 w-8" loading="lazy" decoding="async" width="32" height="32">
                    <span class="font-serif text-xl leading-tight">Basílica del Rosario</span>
                </div>
                <p class="mt-4 text-sm text-crema/70 max-w-xs">Lugar de fe y devoción. Te esperamos para celebrar juntos la Eucaristía.</p>
            </div>

            <div>
                <h2 class="text-xs font-semibold tracking-wider text-crema/90 uppercase">Secciones</h2>
                <ul class="mt-3 space-y-2 text-sm">
                    <li><a href="{{ url('/historia') }}" class="text-crema/80 hover:text-oro transition-colors">Historia</a></li>
                    <li><a href="{{ url('/galeria') }}" class="text-crema/80 hover:text-oro transition-colors">Galería</a></li>
                    <li><a href="{{ url('/horarios') }}" class="text-crema/80 hover:text-oro transition-colors">Horarios</a></li>
                    <li><a href="{{ url('/contacto') }}" class="text-crema/80 hover:text-oro transition-colors">Contacto</a></li>
                </ul>
            </div>

            <div>
                <h2 class="text-xs font-semibold tracking-wider text-crema/90 uppercase">Contacto</h2>
                <ul class="mt-3 space-y-2 text-sm text-crema/75">
                    <li>{{ $parishAddress['city'] ?? 'Ciudad de Guatemala' }}, {{ $parishAddress['zone'] ?? '' }}</li>
                    <li><a href="mailto:{{ $parishContact['email'] ?? '' }}" class="hover:text-oro transition-colors">{{ $parishContact['email'] ?? '' }}</a></li>
                </ul>
            </div>
        </div>
        <div class="mt-10 border-t border-white/10 pt-6 text-[13px] text-crema/60 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <p>&copy; <span id="year-copy">{{ date('Y') }}</span> Parroquia Santo Domingo. Todos los derechos reservados.</p>
            <p class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-3"><span>Hecho con fe en Guatemala</span><span class="hidden sm:inline">•</span><span>Desarrollada por Betegar</span></p>
        </div>
    </div>
</footer>