@extends('layouts.app')
@section('title', 'Historia — Parroquia Santo Domingo')
@section('meta_description', 'Historia de la Basílica de Nuestra Señora del Rosario: orígenes, reconstrucciones sísmicas, título de Basílica y devociones.')
@section('content')

  <!-- Hero / Portada -->
  @php
    $heroAssets = \App\Support\HeroImage::resolve();
    $heroWebp = $heroAssets['hero_webp'];
    $heroJpg = $heroAssets['hero_jpg'];
    $heroImg = $heroAssets['final'];
  @endphp
  <section class="relative min-h-[35vh] sm:min-h-[48vh] md:min-h-[60vh] w-full overflow-hidden rounded-b-2xl sm:rounded-b-3xl border-b border-carbon/10">
    @if($heroImg)
      <img src="{{ $heroImg }}" alt="Basílica del Rosario" class="absolute inset-0 h-full w-full object-cover object-center" loading="lazy" decoding="async" width="1600" height="600">
    @else
      <div class="absolute inset-0 bg-gradient-to-br from-oro/30 to-oroscuro/20"></div>
    @endif
    <div class="absolute inset-0 bg-gradient-to-t from-carbon/80 via-carbon/50 to-carbon/30"></div>
    <div class="relative z-10 mx-auto flex h-full max-w-5xl items-end px-4 sm:px-6 lg:px-8 pb-4 sm:pb-8">
      <div>
        <span class="inline-block rounded-full bg-oro/30 px-2.5 sm:px-3 py-0.5 sm:py-1 text-[10px] sm:text-xs font-medium tracking-wide text-crema backdrop-blur">Nuestra historia</span>
        <h1 class="mt-2 sm:mt-4 font-serif text-2xl sm:text-4xl font-semibold text-crema drop-shadow leading-tight">Basílica de Nuestra Señora del Rosario</h1>
        <p class="mt-1 sm:mt-2 max-w-xl text-crema/80 text-xs sm:text-base">Templo neoclásico, casa de devoción y memoria viva de fe.</p>
      </div>
    </div>
  </section>

  <!-- Contenido con índice lateral -->
  <section class="relative mx-auto max-w-5xl px-4 sm:px-6 lg:px-8 py-8 sm:py-12">
    <div class="lg:grid lg:grid-cols-12 lg:gap-10">
      <!-- Índice - oculto en móvil, visible en desktop -->
      <aside class="hidden lg:block mb-8 lg:mb-0 lg:col-span-3 lg:sticky lg:top-24 self-start">
        <nav aria-label="Índice de secciones" class="rounded-xl border border-carbon/10 bg-white/70 backdrop-blur p-4 text-sm shadow-sm">
          <p class="mb-2 font-semibold text-carbon/80">Contenido</p>
          <ol class="space-y-1 list-decimal list-inside marker:text-oroscuro">
            <li><a href="#origenes" class="hover:text-oroscuro">Orígenes y traslado</a></li>
            <li><a href="#reconstrucciones" class="hover:text-oroscuro">Reconstrucciones sísmicas</a></li>
            <li><a href="#titulo" class="hover:text-oroscuro">Título de Basílica</a></li>
            <li><a href="#virgen" class="hover:text-oroscuro">Imagen de la Virgen</a></li>
            <li><a href="#devociones" class="hover:text-oroscuro">Otras devociones</a></li>
            <li><a href="#significado" class="hover:text-oroscuro">Significado cultural</a></li>
          </ol>
        </nav>
      </aside>

      <!-- Artículo -->
      <article class="prose prose-sm sm:prose max-w-none lg:col-span-9 prose-headings:text-carbon prose-p:text-carbon/80 prose-a:text-oroscuro">
<div class="not-prose mb-6 sm:mb-10 rounded-xl border border-oro/30 bg-gradient-to-br from-oro/10 to-oro/5 p-4 sm:p-6">
          <blockquote class="font-serif text-base sm:text-xl leading-relaxed text-carbon/90">
            «Desde sus cimientos coloniales hasta su constante reconstrucción, la Basílica permanece como signo de esperanza y oración en el corazón de la ciudad.»
          </blockquote>
          <p class="mt-2 sm:mt-3 text-xs sm:text-sm text-carbon/60 text-right">— «Veritas» • Lema OP</p>
        </div>

        <h2 id="origenes" class="scroll-mt-24">Orígenes y traslado de la Orden Dominica</h2>
        <p>La presencia de los frailes dominicos en Guatemala se remonta a 1541, cuando llegaron como misioneros para evangelizar estas tierras. Sus raíces en la actual ubicación se establecieron después de 1773, cuando los terremotos de Santa Marta obligaron el traslado desde la antigua capital de Santiago de los Caballeros (Antigua Guatemala). La nueva construcción en la Nueva Guatemala de la Asunción inició en 1788 bajo la dirección del arquitecto <strong>Pedro Garcí-Aguirre</strong> y se consagró solemnemente el 8 de noviembre de 1808.</p>

        <div class="not-prose my-6 sm:my-8 rounded-lg border border-carbon/10 bg-white/60 backdrop-blur p-3 sm:p-4 flex gap-3 sm:gap-4 items-start">
          <div class="h-8 w-8 sm:h-10 sm:w-10 flex-shrink-0 rounded-full bg-oro/20 flex items-center justify-center font-serif text-sm sm:text-lg">1773</div>
          <p class="text-xs sm:text-sm leading-relaxed text-carbon/80">Terremotos obligan el traslado dominico. Inicia un nuevo capítulo urbano y espiritual para la Orden en la naciente capital.</p>
        </div>

        <h2 id="reconstrucciones" class="scroll-mt-24">Reconstrucciones sísmicas</h2>
        <p>A lo largo de su historia el edificio ha debido levantarse nuevamente en dos ocasiones: después de los terremotos de 1917–1918 y tras el terremoto de 1976. Estas restauraciones consolidaron su fisonomía neoclásica y aseguraron la preservación de su patrimonio artístico.</p>

        <h2 id="titulo" class="scroll-mt-24">Título de Basílica</h2>
        <p>En 1968 la Santa Sede otorgó el título de <em>Basílica Menor de Nuestra Señora del Rosario</em>, reconociendo el valor espiritual y la importancia arquitectónica del templo dentro de la vida eclesial del país.</p>

        <div class="grid gap-4 sm:gap-6 my-6 sm:my-10 grid-cols-1 sm:grid-cols-2">
          <div class="rounded-xl border border-carbon/10 bg-white/60 p-4 sm:p-5 backdrop-blur">
            <p class="text-xs uppercase tracking-wide text-carbon/60 mb-1 sm:mb-2">Título Pontificio</p>
            <p class="font-serif text-base sm:text-lg leading-snug">Basílica Menor otorgada en 1968</p>
          </div>
          <div class="rounded-xl border border-carbon/10 bg-white/60 p-4 sm:p-5 backdrop-blur">
            <p class="text-xs uppercase tracking-wide text-carbon/60 mb-1 sm:mb-2">Coronación</p>
            <p class="font-serif text-base sm:text-lg leading-snug">Imagen mariana coronada en 1933</p>
          </div>
        </div>

        <h2 id="virgen" class="scroll-mt-24">La Imagen de Nuestra Señora del Rosario</h2>
        <p>La parroquia custodia con veneración filial la histórica imagen mariana —una escultura de plata del siglo XVI— trasladada desde la antigua capital. Fue solemnemente coronada el 28 de enero de 1933 con joyas preciosas donadas por el pueblo guatemalteco, recibiendo el título de <strong>«Reina de Guatemala»</strong>. Cada octubre, durante 31 días, se celebra una intensa festividad con rezos del Santo Rosario, procesiones solemnes y actos devocionales que congregan multitudes de fieles que acuden a postrarse ante su Madre.</p>
        <div class="not-prose my-6 sm:my-8 rounded-lg border border-oro/30 bg-oro/10 p-3 sm:p-4">
          <p class="font-serif text-sm sm:text-lg italic text-carbon/80 text-center">«Oh Señora del Rosario, Madre nuestra, protege a Guatemala y guíanos a tu Hijo Jesús.»</p>
        </div>

        <h2 id="devociones" class="scroll-mt-24">Otras Devociones Destacadas</h2>
        <p>También se venera con profunda piedad la imagen del <strong>Cristo Yacente del Amor (Señor Sepultado)</strong>, consagrada en 1973. Su majestuoso cortejo de Viernes Santo recorre las calles capitalinas y es parte fundamental de la Semana Santa guatemalteca, expresión viva de la fe del pueblo chapín. La Hermandad del Señor Sepultado, fundada en 1852, continúa la tradición del Santo Entierro que data de 1582.</p>
        <p class="mt-4">Las procesiones de cuaresma y Semana Santa son momentos de profunda espiritualidad donde los cucuruchos y cargadores expresan su fe penitencial, acompañados por los cantos tradicionales y el aroma del corozo e incienso que perfuman las alfombras de aserrín y flores.</p>

        <h2 id="significado" class="scroll-mt-24">Significado Cultural y Religioso</h2>
        <p>La Basílica-Parroquia Santo Domingo es hoy un referente de identidad religiosa y patrimonio cultural de Guatemala. Articula liturgia solemne, arte sacro de siglos, tradición procesional arraigada y peregrinación mariana constante. Es punto de encuentro para generaciones de fieles que buscan el consuelo de la Virgen del Rosario y la misericordia de Cristo.</p>
        <p class="mt-4">Aquí la fe se transmite de padres a hijos: desde el bautismo hasta las exequias, la Basílica acompaña los momentos fundamentales de la vida cristiana. Los frailes dominicos continúan su misión de «contemplar y dar lo contemplado», predicando la Verdad del Evangelio con la sencillez y profundidad que caracterizó a Santo Domingo de Guzmán.</p>

        <div class="not-prose mt-8 sm:mt-12 rounded-lg border border-oro/30 bg-oro/10 p-3 sm:p-4 text-xs text-carbon/70">
          Fuente: recopilación de crónicas históricas y directorios eclesiales.
        </div>

        <p class="mt-6 sm:mt-8"><a href="{{ url('/') }}" class="inline-flex items-center gap-2 text-oroscuro hover:text-oro text-sm sm:text-base">&larr; Volver al inicio</a></p>
      </article>
    </div>
  </section>
@endsection

@section('structured_data')
<script type="application/ld+json">
{!! json_encode([
  '@context' => 'https://schema.org',
  '@type' => 'Article',
  'headline' => 'Historia de la Basílica de Nuestra Señora del Rosario',
  'inLanguage' => 'es',
  'publisher' => [
    '@type' => 'Organization',
    'name' => 'Parroquia Santo Domingo',
    'logo' => [
       '@type' => 'ImageObject',
       'url' => asset('favicon.ico')
    ]
  ],
  'author' => [
     '@type' => 'Organization',
     'name' => 'Parroquia Santo Domingo'
  ],
  'mainEntityOfPage' => url('/historia'),
  'image' => isset($heroImg) && $heroImg ? $heroImg : asset('favicon.ico'),
  'dateModified' => now()->toDateString(),
]) !!}
</script>
@endsection
