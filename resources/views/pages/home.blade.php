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
    <section class="relative isolate overflow-hidden pb-20">
        <picture>
            @if($heroWebp)
                <source type="image/webp" srcset="{{ $heroWebp }}" />
            @endif
            @if($heroJpg)
                <source type="image/jpeg" srcset="{{ $heroJpg }}" />
            @endif
            <x-ui.responsive-image :src="$finalHero" alt="Basílica de Nuestra Señora del Rosario" width="1600" height="900" class="h-[70svh] w-full object-cover" :priority="true" />
        </picture>
        <div class="absolute inset-0 bg-gradient-to-t from-carbon/95 via-carbon/60 to-transparent"></div>
        <div class="section-shell relative z-10 -mt-20 sm:-mt-28">
            <div class="glass-dark rounded-3xl border border-white/15 p-6 sm:p-8 space-y-6">
                    <div class="text-crema">
                        <span class="pill border-white/30 bg-white/10 text-crema/90">Patrona de la Ciudad desde 1561</span>
                        <h1 class="mt-3 font-serif text-3xl sm:text-4xl font-semibold text-shadow">Parroquia Santo Domingo · Basílica de Nuestra Señora del Rosario</h1>
                        <p class="mt-3 text-sm text-crema/85 sm:text-base">En esta casa de la Virgen del Rosario, los frailes dominicos servimos con la predicación, los sacramentos y las obras de misericordia. Celebramos la fe en el corazón histórico de la Ciudad de Guatemala.</p>
                        <div class="mt-5 flex flex-wrap gap-3">
                            <a href="{{ url('/horarios') }}" class="btn-primary">Horarios y sacramentos</a>
                            <a href="{{ url('/intenciones') }}" class="btn-secondary text-crema">Solicitar intención</a>
                            <a href="{{ $parishContact['whatsapp_link'] ?? '#' }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 rounded-md border border-white/30 bg-white/10 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-crema hover:bg-white/20 focus:outline-none focus-visible:ring-2 focus-visible:ring-white/60">WhatsApp</a>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-2 text-[11px] text-crema/80">
                        <span class="inline-flex items-center gap-2 rounded-full border border-white/20 px-3 py-1">Orden de Predicadores</span>
                        <span class="inline-flex items-center gap-2 rounded-full border border-white/20 px-3 py-1">Señor Sepultado Cristo del Amor</span>
                        <span class="inline-flex items-center gap-2 rounded-full border border-white/20 px-3 py-1">Virgen del Rosario (coronada 1934)</span>
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
    <section class="section-shell py-20 space-y-16">
        <div class="grid gap-12 lg:grid-cols-[minmax(0,1.1fr)_minmax(0,0.9fr)] lg:items-center">
            <div>
                <p class="eyebrow">Comunidad dominica</p>
                <h2 class="mt-4 font-serif text-4xl font-semibold text-carbon">Vivimos y predicamos el Rosario</h2>
                <p class="mt-6 text-lg text-carbon/80 leading-relaxed">Somos parte de la misión confiada a la Orden de Predicadores: contemplar y dar lo contemplado. Aquí custodiamos la imagen centenaria de Nuestra Señora del Rosario, patrona de la ciudad contra los terremotos, y acompañamos a las familias con liturgia digna, formación y caridad.</p>
                <div class="mt-10 grid gap-4 sm:grid-cols-3">
                    <article class="halo-card">
                        <p class="text-xs font-semibold tracking-[0.2em] text-carbon/60">Rosario perpetuo</p>
                        <p class="mt-2 text-lg font-semibold">Rezamos diario</p>
                        <p class="mt-1 text-sm text-carbon/70">Procesiones y vigilias que preparan la fiesta del 7 de octubre.</p>
                    </article>
                    <article class="halo-card">
                        <p class="text-xs font-semibold tracking-[0.2em] text-carbon/60">Predicación</p>
                        <p class="mt-2 text-lg font-semibold">Palabra viva</p>
                        <p class="mt-1 text-sm text-carbon/70">Homilías y retiros inspirados en Santo Domingo de Guzmán.</p>
                    </article>
                    <article class="halo-card">
                        <p class="text-xs font-semibold tracking-[0.2em] text-carbon/60">Caridad</p>
                        <p class="mt-2 text-lg font-semibold">Servicio cercano</p>
                        <p class="mt-1 text-sm text-carbon/70">Despacho parroquial y pastoral social para quienes más necesitan.</p>
                    </article>
                </div>
                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="{{ url('/contacto') }}" class="btn-secondary">Ser voluntario</a>
                    <a href="{{ url('/galeria') }}" class="btn-primary">Ver galería</a>
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
                        <img src="{{ $histImg }}" alt="Interior de la basílica" class="h-72 w-full rounded-2xl object-cover" loading="lazy" decoding="async">
                        <p class="mt-4 text-sm font-semibold text-carbon/70">Camarín de la Virgen del Rosario</p>
                        <p class="text-sm text-carbon/70">Coronada canónicamente en 1934, patrona contra los sismos desde 1561.</p>
                    </div>
                @endif
                <div class="grid gap-4 sm:grid-cols-2">
                    <a href="{{ url('/intenciones') }}" class="devotion-card text-sm" itemscope itemtype="https://schema.org/Service">
                        <p class="eyebrow text-carbon/60">Intenciones</p>
                        <p class="mt-2 text-xl font-semibold text-carbon">Ofrenda una misa</p>
                        <p class="mt-2 text-carbon/70">Comparte la intención y fecha deseada; el despacho dominico te acompaña.</p>
                        <span class="mt-4 inline-flex items-center gap-2 text-sm font-semibold text-oro">Escribir <span aria-hidden="true">→</span></span>
                    </a>
                    <a href="{{ url('/horarios') }}" class="devotion-card text-sm" itemscope itemtype="https://schema.org/Event">
                        <p class="eyebrow text-carbon/60">Agenda</p>
                        <p class="mt-2 text-xl font-semibold text-carbon">Sacramentos y despacho</p>
                        <p class="mt-2 text-carbon/70">Misas, confesiones, bautizos y bendiciones familia por familia.</p>
                        <span class="mt-4 inline-flex items-center gap-2 text-sm font-semibold text-oro">Ver horarios <span aria-hidden="true">→</span></span>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Reseña histórica -->
    <section class="section-shell py-20">
        <div class="grid gap-12 lg:grid-cols-[minmax(0,0.8fr)_minmax(0,1fr)]">
            <div>
                <p class="eyebrow">Memoria de fe</p>
                <h2 class="mt-4 font-serif text-3xl font-semibold text-carbon">Siglos de Rosario y esperanza</h2>
                <p class="mt-4 text-carbon/75">La historia de esta basílica une la antigua capital de Santiago de Guatemala con la Nueva Guatemala de la Asunción. Tras los terremotos de Santa Marta (1773), la Orden de Predicadores trasladó la imagen del Rosario y reconstruyó el templo, que volvió a levantarse después de los sismos de 1917-18 y 1976 gracias a las Asociaciones Dominicanas.</p>
                <div class="mt-6 flex flex-wrap gap-3">
                    <a href="{{ url('/historia') }}" class="btn-primary">Leer historia completa</a>
                    <a href="{{ url('/galeria') }}" class="btn-secondary">Ver fotografías</a>
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