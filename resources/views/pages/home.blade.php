@extends('layouts.app')

@section('title', 'Parroquia Santo Domingo — Basílica del Rosario')
@section('meta_description', 'Bienvenidos a la Parroquia Santo Domingo. Consulta horarios, solicita intenciones de misa y conoce nuestra historia.')

@section('content')
    @php
        $parish = $portalParish ?? config('portal.parish');
        $parishContact = $parish['contact'] ?? [];
        $parishAddress = $parish['address'] ?? [];
        $parishSchedules = $parish['schedules'] ?? [];
        $officeHours = $parish['office_hours'] ?? [];
        $confessionSummary = implode(' · ', array_filter([
            $parishSchedules['confessions_week'] ?? null,
            $parishSchedules['confessions_sunday'] ?? null,
        ]));
        $heroAssets = \App\Support\HeroImage::resolve([
            'force_season' => request()->boolean('octubre') ? 'rosary_month' : null,
        ]);
        $isOctober = $heroAssets['season_key'] === 'rosary_month';
        $octGallery = $heroAssets['gallery'];
        $finalHero = $heroAssets['final'];
        $localBase = $heroAssets['local_base'];
        $heroWebp = $heroAssets['hero_webp'];
        $heroJpg = $heroAssets['hero_jpg'];
        $banner = $heroAssets['banner'];
    @endphp

    @if($localBase)
        @push('critical-preload')
            <link rel="preload" as="image" href="{{ $localBase }}" fetchpriority="high" />
        @endpush
    @endif

    <!-- Hero devocional -->
    <section class="relative isolate overflow-hidden">
        <!-- Imagen hero con altura adaptativa -->
        <div class="relative h-[60svh] sm:h-[65svh] md:h-[70svh]">
            <picture>
                @if($heroWebp)
                    <source type="image/webp" srcset="{{ $heroWebp }}" />
                @endif
                @if($heroJpg)
                    <source type="image/jpeg" srcset="{{ $heroJpg }}" />
                @endif
                <x-ui.responsive-image :src="$finalHero" alt="Basílica de Nuestra Señora del Rosario" width="1600" height="900" class="absolute inset-0 h-full w-full object-cover" :priority="true" />
            </picture>
            <!-- Gradiente mejorado para legibilidad en móvil -->
            <div class="absolute inset-0 bg-gradient-to-t from-carbon via-carbon/70 to-carbon/30"></div>
            
            <!-- Contenido sobre la imagen en móvil -->
            <div class="absolute inset-x-0 bottom-0 p-4 pb-8 sm:hidden">
                <span class="inline-flex items-center rounded-full border border-oro/50 bg-carbon/60 backdrop-blur px-3 py-1 text-[10px] font-medium text-oro">Patrona de la Ciudad desde 1561</span>
                <h1 class="mt-2 font-serif text-2xl font-semibold text-crema leading-tight text-shadow-lg">Parroquia Santo Domingo</h1>
                <p class="text-sm text-crema/90 font-medium mt-1">Basílica de Nuestra Señora del Rosario</p>
            </div>
        </div>
        
        <!-- Panel de información - Adaptativo -->
        <div class="section-shell relative z-10 mt-4 sm:-mt-100 lg:-mt-80 pb-8 sm:pb-12">
            <!-- Versión móvil simplificada -->
            <div class="sm:hidden space-y-4">
                <!-- Botones de acción principales -->
                <div class="grid grid-cols-2 gap-3">
                    <a href="{{ url('/horarios') }}" class="flex flex-col items-center gap-2 rounded-2xl bg-gradient-to-br from-oro to-oroscuro p-4 text-carbon shadow-lg active:scale-[0.98] transition-transform">
                        <span class="text-2xl">⏰</span>
                        <span class="text-sm font-semibold">Horarios</span>
                    </a>
                    <a href="{{ url('/intenciones') }}" class="flex flex-col items-center gap-2 rounded-2xl bg-gradient-to-br from-carbon to-carbon/90 border border-oro/30 p-4 text-crema shadow-lg active:scale-[0.98] transition-transform">
                        <span class="text-2xl">✠</span>
                        <span class="text-sm font-semibold">Ofrecer Misa</span>
                    </a>
                </div>
                
                <!-- Tarjeta de horarios rápidos -->
                <div class="rounded-2xl bg-carbon/95 border border-white/10 p-4 space-y-3">
                    <div class="flex items-center gap-2 text-oro">
                        <span class="h-2 w-2 rounded-full bg-oro animate-pulse"></span>
                        <span class="text-xs font-semibold uppercase tracking-wider">Próximas Misas</span>
                    </div>
                    <div class="grid grid-cols-2 gap-3 text-crema">
                        <div>
                            <p class="text-[10px] uppercase tracking-wide text-crema/60">Domingos</p>
                            <p class="text-sm font-medium">{{ $parishSchedules['sunday_masses'] ?? '6:30 · 8:00 · 12:00' }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] uppercase tracking-wide text-crema/60">Entre semana</p>
                            <p class="text-sm font-medium">{{ $parishSchedules['weekday_masses'] ?? '7:00 · 12:00 · 18:30' }}</p>
                        </div>
                    </div>
                    <a href="{{ $parishContact['whatsapp_link'] ?? '#' }}" target="_blank" rel="noopener noreferrer" class="flex items-center justify-center gap-2 w-full rounded-xl bg-green-600 py-3 text-sm font-semibold text-white active:bg-green-700 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                        Escribir por WhatsApp
                    </a>
                </div>
                
                <!-- Tags informativos compactos -->
                <div class="flex flex-wrap gap-2 justify-center">
                    <span class="rounded-full bg-carbon/80 border border-white/10 px-3 py-1 text-[10px] text-crema/80">Orden de Predicadores</span>
                    <span class="rounded-full bg-carbon/80 border border-white/10 px-3 py-1 text-[10px] text-crema/80">Desde 1541</span>
                </div>
            </div>
            
            <!-- Versión desktop (original mejorada) -->
            <div class="hidden sm:block glass-dark rounded-3xl border border-white/15 p-6 sm:p-8 space-y-6">
                    <div class="text-crema">
                        <span class="pill border-white/30 bg-white/10 text-crema/90">Patrona de la Ciudad desde 1561</span>
                        <h1 class="mt-3 font-serif text-3xl sm:text-4xl font-semibold text-shadow">Parroquia Santo Domingo · Basílica de Nuestra Señora del Rosario</h1>
                        <p class="mt-3 text-sm text-crema/85 sm:text-base">En esta casa de oración, la Virgen del Rosario nos acoge como Madre. Los frailes dominicos servimos al pueblo de Dios con la predicación de la Verdad, los sacramentos que dan vida y las obras de misericordia. Bienvenido a tu hogar espiritual en el corazón histórico de Guatemala.</p>
                        <div class="mt-5 flex flex-wrap gap-3">
                            <a href="{{ url('/horarios') }}" class="btn-primary">Horarios y sacramentos</a>
                            <a href="{{ url('/intenciones') }}" class="btn-secondary text-crema">Solicitar intención</a>
                            <a href="{{ $parishContact['whatsapp_link'] ?? '#' }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 rounded-md border border-white/30 bg-white/10 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-crema hover:bg-white/20 focus:outline-none focus-visible:ring-2 focus-visible:ring-white/60">WhatsApp</a>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-2 text-[11px] text-crema/80">
                        <span class="inline-flex items-center gap-2 rounded-full border border-white/20 px-3 py-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor"><path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z"/></svg>
                            Orden de Predicadores (O.P.)
                        </span>
                        <span class="inline-flex items-center gap-2 rounded-full border border-white/20 px-3 py-1">Señor Sepultado Cristo del Amor</span>
                        <span class="inline-flex items-center gap-2 rounded-full border border-white/20 px-3 py-1">Virgen del Rosario — Coronada 1934</span>
                        <span class="inline-flex items-center gap-2 rounded-full border border-white/20 px-3 py-1">Patrona contra los Terremotos</span>
                    </div>
                    <div class="grid gap-6 lg:grid-cols-[minmax(0,1.1fr)_minmax(0,0.9fr)]">
                        <dl class="grid gap-4 text-crema/85 sm:grid-cols-3">
                            <div>
                                <dt class="text-xs uppercase tracking-[0.2em] text-crema/70">Presencia dominica</dt>
                                <dd class="mt-1 text-xl font-semibold">1541</dd>
                            </div>
                            <div>
                                <dt class="text-xs uppercase tracking-[0.2em] text-crema/70">Templo actual</dt>
                                <dd class="mt-1 text-xl font-semibold">1808</dd>
                            </div>
                            <div>
                                <dt class="text-xs uppercase tracking-[0.2em] text-crema/70">Confesiones</dt>
                                <dd class="mt-1 text-xl font-semibold">{{ $confessionSummary }}</dd>
                            </div>
                        </dl>
                        <div class="surface-panel-dark space-y-4 p-5">
                            <div class="flex flex-wrap items-center gap-2 text-[11px] uppercase tracking-[0.3em] text-crema/70">
                                <span class="inline-block h-2 w-2 rounded-full bg-oro"></span>
                                Agenda pastoral
                                @if($isOctober)
                                    <span class="pill border-crema/40 bg-white/10 text-crema/80">Mes del Rosario</span>
                                @endif
                            </div>
                            <ul class="space-y-3 text-sm text-crema/90">
                                <li>
                                    <p class="font-semibold">Domingos</p>
                                    <p class="text-crema/70">{{ $parishSchedules['sunday_masses'] ?? '' }}</p>
                                </li>
                                <li>
                                    <p class="font-semibold">Misas diarias</p>
                                    <p class="text-crema/70">{{ $parishSchedules['weekday_masses'] ?? '' }}</p>
                                </li>
                                <li>
                                    <p class="font-semibold">Confesiones</p>
                                    <p class="text-crema/70">{{ $confessionSummary }}</p>
                                </li>
                            </ul>
                            <div class="flex flex-wrap gap-3">
                                <a href="{{ url('/horarios') }}" class="btn-primary">Ver agenda</a>
                                <a href="{{ $parishContact['whatsapp_link'] ?? '#' }}" target="_blank" rel="noopener noreferrer" class="btn-secondary text-crema">Escríbenos</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Camino dominico -->
    <section class="section-shell py-12 sm:py-20 space-y-10 sm:space-y-16">
        <div class="grid gap-8 sm:gap-12 lg:grid-cols-[minmax(0,1.1fr)_minmax(0,0.9fr)] lg:items-center">
            <div>
                <p class="eyebrow">Comunidad Dominica</p>
                <h2 class="mt-3 sm:mt-4 font-serif text-2xl sm:text-4xl font-semibold text-carbon">Contemplar y dar lo Contemplado</h2>
                <p class="mt-4 sm:mt-6 text-base sm:text-lg text-carbon/80 leading-relaxed">Desde 1541, los frailes dominicos servimos en esta tierra guatemalteca. Nuestro carisma es buscar la Verdad en la oración y compartirla en la predicación. Aquí custodiamos la imagen centenaria de Nuestra Señora del Rosario, Patrona de la ciudad contra los terremotos.</p>
                <blockquote class="mt-4 sm:mt-6 pl-4 border-l-4 border-oro text-carbon/70 italic text-sm sm:text-base">
                    «La gracia de la predicación fue confiada a Santo Domingo para que anunciara el Evangelio de la paz.»
                </blockquote>
                <div class="mt-6 sm:mt-10 grid gap-4 grid-cols-1 sm:grid-cols-3">
                    <article class="halo-card">
                        <p class="text-[10px] sm:text-xs font-semibold tracking-[0.2em] text-carbon/60">ORACIÓN</p>
                        <p class="mt-1 sm:mt-2 text-base sm:text-lg font-semibold">Santo Rosario</p>
                        <p class="mt-1 text-xs sm:text-sm text-carbon/70">Rezamos diariamente el Rosario en comunidad. En octubre, procesiones y vigilias preparan la fiesta patronal.</p>
                    </article>
                    <article class="halo-card">
                        <p class="text-[10px] sm:text-xs font-semibold tracking-[0.2em] text-carbon/60">PREDICACIÓN</p>
                        <p class="mt-1 sm:mt-2 text-base sm:text-lg font-semibold">Palabra de Vida</p>
                        <p class="mt-1 text-xs sm:text-sm text-carbon/70">Homilías que nutren el alma, retiros y catequesis inspirados en Santo Domingo de Guzmán.</p>
                    </article>
                    <article class="halo-card">
                        <p class="text-[10px] sm:text-xs font-semibold tracking-[0.2em] text-carbon/60">CARIDAD</p>
                        <p class="mt-1 sm:mt-2 text-base sm:text-lg font-semibold">Amor Fraterno</p>
                        <p class="mt-1 text-xs sm:text-sm text-carbon/70">Despacho parroquial abierto y acompañamiento espiritual para quienes más lo necesitan.</p>
                    </article>
                </div>
                <div class="mt-6 sm:mt-8 flex flex-wrap gap-3">
                    <a href="{{ url('/contacto') }}" class="btn-secondary text-sm sm:text-base">Ser voluntario</a>
                    <a href="{{ url('/galeria') }}" class="btn-primary text-sm sm:text-base">Ver galería</a>
                </div>
            </div>
            <div class="space-y-4">
                @php
                    $histImg = null;
                    if ($isOctober) {
                        foreach (['octubre-card.jpg','octubre-card.webp','octubre-2.jpg','octubre-2.webp'] as $f) {
                            if (file_exists(public_path('images/'.$f))) { $histImg = asset('images/'.$f); break; }
                        }
                    }
                    if (!$histImg) {
                        $histImg = file_exists(public_path('images/historia.jpg')) ? asset('images/historia.jpg') : (file_exists(public_path('images/historia.webp')) ? asset('images/historia.webp') : $banner);
                    }
                @endphp
                @if($histImg)
                    <div class="devotion-card overflow-hidden">
                        <img src="{{ $histImg }}" alt="Interior de la basílica" class="h-48 sm:h-72 w-full rounded-2xl object-cover" loading="lazy" decoding="async">
                        <p class="mt-3 sm:mt-4 text-sm font-semibold text-carbon/70">Camarín de Nuestra Señora del Rosario</p>
                        <p class="text-xs sm:text-sm text-carbon/70">Imagen tallada en el siglo XVI, coronada canónicamente por S.S. Pío XI en 1934. Patrona de la ciudad contra los sismos.</p>
                    </div>
                @endif
                <div class="grid gap-3 sm:gap-4 grid-cols-2">
                    <a href="{{ url('/intenciones') }}" class="devotion-card text-xs sm:text-sm" itemscope itemtype="https://schema.org/Service">
                        <p class="eyebrow text-carbon/60 text-[10px] sm:text-xs">Intenciones</p>
                        <p class="mt-1 sm:mt-2 text-base sm:text-xl font-semibold text-carbon">Ofrenda una misa</p>
                        <p class="mt-1 sm:mt-2 text-carbon/70 hidden sm:block">Comparte la intención y fecha deseada.</p>
                        <span class="mt-2 sm:mt-4 inline-flex items-center gap-1 sm:gap-2 text-xs sm:text-sm font-semibold text-oro">Solicitar <span aria-hidden="true">→</span></span>
                    </a>
                    <a href="{{ url('/horarios') }}" class="devotion-card text-xs sm:text-sm" itemscope itemtype="https://schema.org/Event">
                        <p class="eyebrow text-carbon/60 text-[10px] sm:text-xs">Agenda</p>
                        <p class="mt-1 sm:mt-2 text-base sm:text-xl font-semibold text-carbon">Sacramentos</p>
                        <p class="mt-1 sm:mt-2 text-carbon/70 hidden sm:block">Misas, confesiones, bautizos.</p>
                        <span class="mt-2 sm:mt-4 inline-flex items-center gap-1 sm:gap-2 text-xs sm:text-sm font-semibold text-oro">Ver horarios <span aria-hidden="true">→</span></span>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Reseña histórica -->
    <section class="section-shell py-12 sm:py-20">
        <div class="grid gap-8 sm:gap-12 lg:grid-cols-[minmax(0,0.8fr)_minmax(0,1fr)]">
            <div>
                <p class="eyebrow">Memoria de fe</p>
                <h2 class="mt-3 sm:mt-4 font-serif text-2xl sm:text-3xl font-semibold text-carbon">Siglos de Rosario y esperanza</h2>
                <p class="mt-3 sm:mt-4 text-sm sm:text-base text-carbon/75">La historia de esta basílica une la antigua capital de Santiago de Guatemala con la Nueva Guatemala de la Asunción. Tras los terremotos de Santa Marta (1773), la Orden de Predicadores trasladó la imagen del Rosario y reconstruyó el templo.</p>
                <div class="mt-5 sm:mt-6 flex flex-wrap gap-3">
                    <a href="{{ url('/historia') }}" class="btn-primary text-sm sm:text-base">Leer historia</a>
                    <a href="{{ url('/galeria') }}" class="btn-secondary text-sm sm:text-base">Ver fotos</a>
                </div>
            </div>
            <div class="devotion-card">
                <div class="timeline">
                    <div class="timeline-item">
                        <p class="text-xs font-semibold tracking-[0.2em] text-carbon/50">1561</p>
                        <h3 class="text-lg font-semibold text-carbon">Patrona contra terremotos</h3>
                        <p class="text-sm text-carbon/70">La imagen de Nuestra Señora del Rosario es proclamada protectora de la ciudad.</p>
                    </div>
                    <div class="timeline-item">
                        <p class="text-xs font-semibold tracking-[0.2em] text-carbon/50">1776 – 1808</p>
                        <h3 class="text-lg font-semibold text-carbon">Traslado y dedicación</h3>
                        <p class="text-sm text-carbon/70">Los dominicos levantan el nuevo templo en la capital trasladada tras los terremotos de Santa Marta; se consagra el 8 de noviembre de 1808.</p>
                    </div>
                    <div class="timeline-item">
                        <p class="text-xs font-semibold tracking-[0.2em] text-carbon/50">1852</p>
                        <h3 class="text-lg font-semibold text-carbon">Hermandad del Señor Sepultado</h3>
                        <p class="text-sm text-carbon/70">Nace la cofradía del Cristo del Amor, heredera del Santo Entierro fundado en 1582.</p>
                    </div>
                    <div class="timeline-item">
                        <p class="text-xs font-semibold tracking-[0.2em] text-carbon/50">1934</p>
                        <h3 class="text-lg font-semibold text-carbon">Coronación Pontificia</h3>
                        <p class="text-sm text-carbon/70">Pío XI concede la coronación canónica a la Virgen del Rosario, reafirmando su patronazgo.</p>
                    </div>
                    <div class="timeline-item">
                        <p class="text-xs font-semibold tracking-[0.2em] text-carbon/50">1976</p>
                        <h3 class="text-lg font-semibold text-carbon">Reconstrucción fraterna</h3>
                        <p class="text-sm text-carbon/70">Tras el terremoto, la comunidad impulsa una restauración integral que mantiene vivo el santuario.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Vida sacramental -->
    <section class="bg-carbon text-crema/90 py-16">
        <div class="section-shell space-y-10">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="eyebrow text-crema/60">Vida sacramental</p>
                    <h2 class="mt-2 font-serif text-3xl font-semibold">Un ritmo de oración y servicio</h2>
                    <p class="mt-2 max-w-2xl text-sm text-crema/70">Consulta horarios y prepara confesiones, bautizos, matrimonios o bendiciones familiares.</p>
                </div>
                <a href="{{ url('/horarios') }}" class="btn-primary">Agenda detallada</a>
            </div>
            <div class="grid gap-6 md:grid-cols-3">
                <article class="surface-panel-dark h-full">
                    <p class="text-sm font-semibold text-crema/80">Domingos</p>
                    <p class="mt-2 text-2xl font-semibold">{{ $parishSchedules['sunday_masses'] ?? '' }}</p>
                    <p class="mt-4 text-sm text-crema/70">Liturgia solemne con coro y procesión del Rosario durante fiestas.</p>
                </article>
                <article class="surface-panel-dark h-full">
                    <p class="text-sm font-semibold text-crema/80">Entre semana</p>
                    <p class="mt-2 text-2xl font-semibold">{{ $parishSchedules['weekday_masses'] ?? '' }}</p>
                    <p class="mt-4 text-sm text-crema/70">Misa matutina y vespertina con rezo comunitario del Rosario.</p>
                </article>
                <article class="surface-panel-dark h-full">
                    <p class="text-sm font-semibold text-crema/80">Confesiones y adoración</p>
                    <ul class="mt-3 space-y-3 text-sm text-crema/75">
                        <li>Confesiones: {{ $confessionSummary }}</li>
                        <li>Rosario: {{ $parishSchedules['rosary'] ?? '' }}</li>
                        <li>Hora Santa: {{ $parishSchedules['holy_hour'] ?? '' }}</li>
                    </ul>
                </article>
            </div>
        </div>
    </section>

    <!-- Hermandades y piedad popular -->
    <section class="section-shell py-20">
        <div class="grid gap-10 lg:grid-cols-2">
            <article class="devotion-card">
                <p class="eyebrow text-carbon/60">Hermandad del Señor Sepultado</p>
                <h3 class="mt-3 text-2xl font-semibold text-carbon">Cristo del Amor</h3>
                <p class="mt-3 text-sm text-carbon/70">Fundada en 1852, la hermandad cuida la imagen venerada desde el siglo XVI, cuya procesión inspira la piedad popular del Viernes Santo. El estandarte dominico y las Armas Christi acompañan la caminata penitencial.</p>
                <ul class="mt-4 space-y-2 text-sm text-carbon/70">
                    <li>Procesión del Viernes Santo y tercer domingo de Cuaresma.</li>
                    <li>Formación espiritual para cargadores y familias.</li>
                </ul>
                <div class="mt-5 flex flex-wrap gap-3">
                    <a href="{{ url('/galeria') }}" class="btn-secondary">Ver procesiones</a>
                    <a href="{{ url('/historia') }}" class="btn-primary">Conocer más</a>
                </div>
            </article>
            <article class="devotion-card">
                <p class="eyebrow text-carbon/60">Virgen del Rosario</p>
                <h3 class="mt-3 text-2xl font-semibold text-carbon">Patrona y Madre</h3>
                <p class="mt-3 text-sm text-carbon/70">La imagen original, tallada en el siglo XVI y coronada en 1934, recorre las calles durante octubre para bendecir a la ciudad. Sus novenas, peregrinaciones y visitas familiares mantienen viva la fe.</p>
                <ul class="mt-4 space-y-2 text-sm text-carbon/70">
                    <li>Novenario con predicadores invitados.</li>
                    <li>Rosario de la aurora y visitas a enfermos.</li>
                </ul>
                <div class="mt-5 flex flex-wrap gap-3">
                    <a href="{{ url('/horarios') }}" class="btn-secondary">Participar en el novenario</a>
                    <a href="{{ url('/contacto') }}" class="btn-primary">Solicitar visita</a>
                </div>
            </article>
        </div>
    </section>

    @if($isOctober)
        <!-- Mes del Rosario -->
        <section class="section-shell">
            <div class="rounded-3xl border border-oro/40 bg-gradient-to-br from-oro/15 via-white to-oro/5 p-8 shadow">
                <div class="grid gap-8 lg:grid-cols-[minmax(0,1fr)_minmax(0,0.9fr)] lg:items-center">
                    <div>
                        <p class="eyebrow text-oroscuro">Octubre · Mes del Rosario</p>
                        <h3 class="mt-3 font-serif text-3xl font-semibold text-carbon">María nos reúne en la casa de Santo Domingo</h3>
                        <p class="mt-4 text-base text-carbon/80">Cada octubre celebramos a la Madre de Dios con rosarios procesionales, predicaciones sobre los misterios y la fiesta patronal del 7 de octubre. Invitamos a traer tu rosario, a ofrecer flores y a rezar en familia.</p>
                        <ul class="mt-6 space-y-3 text-sm text-carbon/80">
                            <li class="flex items-start gap-3"><span class="pill">Rosario diario</span><span>30 minutos antes de la misa vespertina.</span></li>
                            <li class="flex items-start gap-3"><span class="pill">7 de octubre</span><span>Misa y procesión con la imagen coronada.</span></li>
                            <li class="flex items-start gap-3"><span class="pill">Familias</span><span>Entrega de subsidios catequéticos y bendición de rosarios.</span></li>
                        </ul>
                        <div class="mt-6 flex flex-wrap gap-3">
                            <a href="{{ url('/horarios') }}" class="btn-primary">Horarios del mes</a>
                            <a href="{{ url('/historia') }}" class="btn-secondary">Origen de la devoción</a>
                        </div>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        @foreach($octGallery->take(4) as $img)
                            <img src="{{ $img }}" alt="Mes del Rosario" class="h-40 w-full rounded-2xl border border-oro/30 object-cover" loading="lazy" decoding="async">
                        @endforeach
                    </div>
                </div>
            </div>
        </section>
    @endif

    @if($isOctober && $octGallery->isNotEmpty())
        @php $calendarImages = $octGallery->values(); @endphp
        <section class="section-shell py-16" data-calendar-viewer data-calendar-images='@json($calendarImages)'>
            <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
                <div>
                    <p class="eyebrow text-carbon/60">Recursos descargables</p>
                    <h3 class="font-serif text-3xl font-semibold">Calendario del Mes del Rosario</h3>
                </div>
                <div class="text-xs text-carbon/70">Toca o haz clic para ampliar · Disponible para descarga</div>
            </div>
            <div class="grid gap-6 md:grid-cols-2">
                @foreach($calendarImages as $idx => $img)
                    <figure class="rounded-2xl border border-carbon/10 bg-white/90 shadow">
                        <button type="button" class="block w-full" data-calendar-open="{{ $idx }}">
                            <img src="{{ $img }}" alt="Calendario del Mes del Rosario — página {{ $idx+1 }}" class="w-full h-auto object-contain" loading="lazy" decoding="async">
                        </button>
                        <figcaption class="flex items-center justify-between text-xs text-carbon/70 px-4 py-3 border-t border-carbon/10">
                            <span>Página {{ $idx+1 }}</span>
                            <a href="{{ $img }}" download class="inline-flex items-center gap-1 hover:text-oro">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M12 3v10.586l3.293-3.293 1.414 1.414L12 17.414l-4.707-4.707 1.414-1.414L12 13.586V3h0z"/><path d="M5 19h14v2H5z"/></svg>
                                Descargar
                            </a>
                        </figcaption>
                    </figure>
                @endforeach
            </div>

            <div class="fixed inset-0 z-50 hidden" data-calendar-modal aria-hidden="true" role="dialog" aria-label="Calendario del Mes del Rosario">
                <div class="absolute inset-0 bg-black/75" data-calendar-dismiss></div>
                <div class="absolute inset-0 flex flex-col" role="document">
                    <div class="flex items-center justify-between gap-2 p-3 bg-black/60 text-white text-sm">
                        <div class="flex items-center gap-3">
                            <button type="button" data-calendar-prev class="px-2 py-1 rounded bg-white/10 hover:bg-white/20">‹ Anterior</button>
                            <button type="button" data-calendar-next class="px-2 py-1 rounded bg-white/10 hover:bg-white/20">Siguiente ›</button>
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="button" data-calendar-zoom-out class="px-2 py-1 rounded bg-white/10 hover:bg-white/20">−</button>
                            <span data-calendar-zoom class="min-w-[3rem] text-center">100%</span>
                            <button type="button" data-calendar-zoom-in class="px-2 py-1 rounded bg-white/10 hover:bg-white/20">+</button>
                            <a data-calendar-download href="#" download class="ml-2 px-2 py-1 rounded bg-white/10 hover:bg-white/20">Descargar</a>
                            <button type="button" data-calendar-close class="ml-2 px-2 py-1 rounded bg-white/10 hover:bg-white/20">Cerrar ✕</button>
                        </div>
                    </div>
                    <div class="relative flex-1 overflow-auto bg-white" data-calendar-dismiss-surface>
                        <div class="min-h-full min-w-full flex items-center justify-center p-4">
                            <img data-calendar-image src="" alt="Calendario del Mes del Rosario (ampliado)" class="max-w-full h-auto" />
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif

    <!-- Contacto -->
    <section class="section-shell py-20">
        <!-- Sección de Santos Dominicos del mes -->
        <div class="mb-16 rounded-3xl border border-oro/30 bg-gradient-to-br from-white via-oro/5 to-white p-8 shadow">
            <p class="eyebrow text-oroscuro mb-4">Santos de la Familia Dominicana</p>
            <div class="grid gap-6 md:grid-cols-3">
                <div class="text-center">
                    <div class="mx-auto h-16 w-16 rounded-full bg-oro/20 flex items-center justify-center mb-3">
                        <span class="text-2xl">⚜️</span>
                    </div>
                    <h3 class="font-serif text-lg font-semibold text-carbon">Santo Domingo de Guzmán</h3>
                    <p class="text-xs text-carbon/60 mt-1">8 de agosto</p>
                    <p class="text-sm text-carbon/70 mt-2">Fundador de la Orden de Predicadores. Su amor por la Verdad y la Virgen María nos inspira.</p>
                </div>
                <div class="text-center">
                    <div class="mx-auto h-16 w-16 rounded-full bg-oro/20 flex items-center justify-center mb-3">
                        <span class="text-2xl">📖</span>
                    </div>
                    <h3 class="font-serif text-lg font-semibold text-carbon">Santo Tomás de Aquino</h3>
                    <p class="text-xs text-carbon/60 mt-1">28 de enero</p>
                    <p class="text-sm text-carbon/70 mt-2">Doctor Angélico, patrono de las universidades. Su teología ilumina nuestra fe.</p>
                </div>
                <div class="text-center">
                    <div class="mx-auto h-16 w-16 rounded-full bg-oro/20 flex items-center justify-center mb-3">
                        <span class="text-2xl">🌹</span>
                    </div>
                    <h3 class="font-serif text-lg font-semibold text-carbon">Santa Rosa de Lima</h3>
                    <p class="text-xs text-carbon/60 mt-1">23 de agosto</p>
                    <p class="text-sm text-carbon/70 mt-2">Primera santa de América. Su vida de oración y penitencia es ejemplo para nosotros.</p>
                </div>
            </div>
        </div>

        <div class="grid gap-10 lg:grid-cols-[minmax(0,0.9fr)_minmax(0,1.1fr)] lg:items-start">
            <div class="devotion-card space-y-5">
                <div>
                    <p class="eyebrow text-carbon/60">Despacho parroquial</p>
                    <h2 class="mt-2 font-serif text-3xl font-semibold text-carbon">Estamos para servirte</h2>
                    <p class="mt-3 text-sm text-carbon/70">Coordinamos sacramentos, bendiciones y acompañamiento espiritual. Escríbenos o visítanos en el horario publicado.</p>
                </div>
                <ul class="space-y-4 text-sm text-carbon/80">
                    <li>
                        <span class="font-semibold">Dirección:</span>
                        {{ trim(($parishAddress['street'] ?? '') . ' ' . ($parishAddress['zone'] ?? '')) }}, {{ $parishAddress['city'] ?? 'Ciudad de Guatemala' }}
                    </li>
                    <li>
                        <span class="font-semibold">Teléfono:</span>
                        <a href="{{ $parishContact['phone_link'] ?? '#' }}" class="hover:text-oro">{{ $parishContact['phone_display'] ?? $parishContact['phone'] }}</a>
                    </li>
                    <li>
                        <span class="font-semibold">WhatsApp:</span>
                        <a href="{{ $parishContact['whatsapp_link'] ?? '#' }}" class="hover:text-oro" target="_blank" rel="noopener noreferrer">{{ $parishContact['whatsapp_display'] ?? $parishContact['whatsapp'] }}</a>
                    </li>
                    <li>
                        <span class="font-semibold">Correo:</span>
                        <a href="mailto:{{ $parishContact['email'] ?? '' }}" class="hover:text-oro">{{ $parishContact['email'] ?? '' }}</a>
                    </li>
                </ul>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ url('/contacto') }}" class="btn-secondary">Detalle de contacto</a>
                    <a href="{{ $parishContact['whatsapp_link'] ?? '#' }}" target="_blank" rel="noopener noreferrer" class="btn-primary">Escribir por WhatsApp</a>
                </div>
            </div>
            <div class="rounded-3xl border border-carbon/10 overflow-hidden">
                <iframe title="Ubicación Basílica" src="{{ $parishContact['maps_embed'] ?? '' }}" width="600" height="450" style="border:0;" allowfullscreen loading="lazy" referrerpolicy="no-referrer-when-downgrade" class="h-full w-full"></iframe>
            </div>
        </div>
    </section>
@endsection