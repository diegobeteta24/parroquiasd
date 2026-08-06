@php
    $parish = $portalParish ?? config('portal.parish');
    $parishContact = $parish['contact'] ?? [];
    $parishAddress = $parish['address'] ?? [];
    $schedules = $parish['schedules'] ?? [];
@endphp

<!-- Sección de oración antes del footer -->
<section class="bg-gradient-to-b from-carbon to-[#0b0b0b] text-crema py-8 sm:py-12">
    <div class="mx-auto max-w-4xl px-4 text-center">
        <p class="eyebrow text-oro/80 mb-3 sm:mb-4">Oración por las Intenciones</p>
        <blockquote class="font-serif text-base sm:text-xl md:text-2xl leading-relaxed text-crema/90 italic">
            «Oh Dios, que por medio de Santo Domingo de Guzmán iluminaste a tu Iglesia; 
            concédenos que las intenciones que te encomendamos sean atendidas según tu santa voluntad.»
        </blockquote>
        <p class="mt-3 sm:mt-4 text-crema/60 text-xs sm:text-sm">Amén.</p>
    </div>
</section>

<footer class="border-t border-white/5 bg-[#0b0b0b] text-crema">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8 sm:py-12">
        <!-- Versión móvil compacta -->
        <div class="sm:hidden space-y-6">
            <!-- Logo y CTA principal -->
            <div class="text-center">
                <div class="flex items-center justify-center gap-2">
                    <img src="/favicon.ico" alt="Escudo" class="h-8 w-8" loading="lazy" decoding="async" width="32" height="32">
                    <span class="font-serif text-lg">Basílica del Rosario</span>
                </div>
                <p class="mt-2 text-xs text-crema/60">Orden de Predicadores desde 1541</p>
            </div>
            
            <!-- Acciones rápidas móvil -->
            <div class="grid grid-cols-2 gap-3">
                <a href="{{ url('/horarios') }}" class="flex flex-col items-center gap-1 rounded-xl bg-white/5 p-3 text-center active:bg-white/10 transition-colors">
                    <span class="text-xl">⏰</span>
                    <span class="text-xs font-medium">Horarios</span>
                </a>
                <a href="{{ url('/contacto') }}" class="flex flex-col items-center gap-1 rounded-xl bg-white/5 p-3 text-center active:bg-white/10 transition-colors">
                    <span class="text-xl">📍</span>
                    <span class="text-xs font-medium">Ubicación</span>
                </a>
            </div>
            
            <!-- Botón WhatsApp destacado -->
            <a href="{{ $parishContact['whatsapp_link'] ?? '#' }}" target="_blank" rel="noopener noreferrer" class="flex items-center justify-center gap-2 w-full rounded-xl bg-green-600 py-3 text-sm font-semibold text-white active:bg-green-700 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                WhatsApp Pastoral
            </a>
            
            <!-- Links compactos -->
            <div class="flex flex-wrap justify-center gap-x-4 gap-y-2 text-xs text-crema/60">
                <a href="{{ url('/historia') }}" class="hover:text-oro">Historia</a>
                <a href="{{ url('/galeria') }}" class="hover:text-oro">Galería</a>
                <a href="{{ url('/intenciones') }}" class="text-oro font-medium">Ofrecer Misa</a>
            </div>
        </div>
        
        <!-- Versión desktop (grid completo) -->
        <div class="hidden sm:grid grid-cols-1 md:grid-cols-4 gap-10">
            <!-- Logo y descripción -->
            <div class="md:col-span-1">
                <div class="flex items-center gap-3">
                    <img src="/favicon.ico" alt="Escudo Parroquia Santo Domingo" class="h-10 w-10" loading="lazy" decoding="async" width="40" height="40">
                    <span class="font-serif text-xl leading-tight">Basílica del Rosario</span>
                </div>
                <p class="mt-4 text-sm text-crema/70 max-w-xs">Casa de la Virgen del Rosario y el Señor Sepultado Cristo del Amor. Frailes Dominicos al servicio de la fe desde 1541.</p>
                <p class="mt-3 text-xs text-oro italic">Veritas • La Verdad os hará libres</p>
            </div>

            <!-- Horarios rápidos -->
            <div>
                <h2 class="text-xs font-semibold tracking-wider text-crema/90 uppercase flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-oro" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/></svg>
                    Santa Misa
                </h2>
                <ul class="mt-3 space-y-2 text-sm text-crema/75">
                    <li><span class="text-crema/50">Lun-Vie:</span> {{ $schedules['weekday_masses'] ?? '7:00 · 12:00 · 18:30' }}</li>
                    <li><span class="text-crema/50">Sábado:</span> {{ $schedules['saturday_mass'] ?? '18:30' }}</li>
                    <li><span class="text-crema/50">Domingo:</span> {{ $schedules['sunday_masses'] ?? '6:30 · 8:00 · 12:00 · 16:30 · 18:30' }}</li>
                </ul>
            </div>

            <!-- Secciones -->
            <div>
                <h2 class="text-xs font-semibold tracking-wider text-crema/90 uppercase">Navegación</h2>
                <ul class="mt-3 space-y-2 text-sm">
                    <li><a href="{{ url('/') }}" class="text-crema/80 hover:text-oro transition-colors">Inicio</a></li>
                    <li><a href="{{ url('/historia') }}" class="text-crema/80 hover:text-oro transition-colors">Historia</a></li>
                    <li><a href="{{ url('/galeria') }}" class="text-crema/80 hover:text-oro transition-colors">Galería</a></li>
                    <li><a href="{{ url('/horarios') }}" class="text-crema/80 hover:text-oro transition-colors">Horarios y Sacramentos</a></li>
                    <li><a href="{{ url('/contacto') }}" class="text-crema/80 hover:text-oro transition-colors">Contacto</a></li>
                    <li><a href="{{ url('/intenciones') }}" class="text-oro hover:text-oroscuro transition-colors font-medium">Ofrecer una Misa</a></li>
                </ul>
            </div>

            <!-- Contacto -->
            <div>
                <h2 class="text-xs font-semibold tracking-wider text-crema/90 uppercase">Contacto Parroquial</h2>
                <ul class="mt-3 space-y-2 text-sm text-crema/75">
                    <li class="flex items-start gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-oro mt-0.5 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/></svg>
                        <span>{{ $parishAddress['street'] ?? '12 Avenida 10-09' }}, {{ $parishAddress['zone'] ?? 'Zona 1' }}<br>{{ $parishAddress['city'] ?? 'Ciudad de Guatemala' }}</span>
                    </li>
                    <li class="flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-oro flex-shrink-0" viewBox="0 0 20 20" fill="currentColor"><path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"/></svg>
                        <a href="{{ $parishContact['phone_link'] ?? '#' }}" class="hover:text-oro transition-colors">{{ $parishContact['phone_display'] ?? $parishContact['phone'] ?? '' }}</a>
                    </li>
                    <li class="flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-oro flex-shrink-0" viewBox="0 0 20 20" fill="currentColor"><path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/><path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/></svg>
                        <a href="mailto:{{ $parishContact['email'] ?? '' }}" class="hover:text-oro transition-colors break-all">{{ $parishContact['email'] ?? '' }}</a>
                    </li>
                </ul>
                <a href="{{ $parishContact['whatsapp_link'] ?? '#' }}" target="_blank" rel="noopener noreferrer" class="mt-4 inline-flex items-center gap-2 rounded-full bg-green-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-green-700 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                    WhatsApp Pastoral
                </a>
            </div>
        </div>
        
        <!-- Pie de página -->
        <div class="mt-8 sm:mt-10 pt-6 border-t border-white/10 text-[11px] sm:text-[13px] text-crema/60">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4 text-center sm:text-left">
                <div>
                    <p>&copy; {{ date('Y') }} Parroquia Santo Domingo · O.P.</p>
                    <p class="text-crema/40 text-[10px] sm:text-xs mt-1">Arquidiócesis de Guatemala</p>
                </div>
                <div class="text-crema/50 italic hidden sm:block">«Contempláis y compartid lo contemplado»</div>
                <div>
                    <span class="text-crema/40">Sitio desarrollado por</span> <a href="https://betegar.com" target="_blank" rel="noopener" class="text-oro/80 hover:text-oro">Betegar</a>
                </div>
            </div>
        </div>
    </div>
</footer>