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
  <section class="relative min-h-[48vh] sm:min-h-[54vh] md:min-h-[60vh] w-full overflow-hidden rounded-b-3xl border-b border-carbon/10">
    @if($heroImg)
  <img src="{{ $heroImg }}" alt="Basílica del Rosario" class="absolute inset-0 h-full w-full object-cover object-center" loading="lazy" decoding="async" width="1600" height="600">
    @else
      <div class="absolute inset-0 bg-gradient-to-br from-oro/30 to-oroscuro/20"></div>
    @endif
    <div class="absolute inset-0 bg-carbon/60 mix-blend-multiply"></div>
    <div class="relative z-10 mx-auto flex h-full max-w-5xl items-end px-4 sm:px-6 lg:px-8 pb-8">
      <div>
        <span class="inline-block rounded-full bg-oro/20 px-3 py-1 text-xs font-medium tracking-wide text-crema/90 backdrop-blur">Nuestra historia</span>
        <h1 class="mt-4 font-serif text-4xl font-semibold text-crema drop-shadow">Basílica de Nuestra Señora del Rosario</h1>
        <p class="mt-2 max-w-xl text-crema/80">Templo neoclásico emblemático, casa de devoción y memoria viva de fe.</p>
      </div>
    </div>
  </section>

  <!-- Contenido con índice lateral -->
  <section class="relative mx-auto max-w-5xl px-4 sm:px-6 lg:px-8 py-12">
    <div class="lg:grid lg:grid-cols-12 lg:gap-10">
      <!-- Índice -->
      <aside class="mb-8 lg:mb-0 lg:col-span-3 lg:sticky lg:top-24 self-start">
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
      <article class="prose max-w-none lg:col-span-9">
        <div class="not-prose mb-10 rounded-xl border border-oro/30 bg-gradient-to-br from-oro/10 to-oro/5 p-6">
          <blockquote class="font-serif text-xl leading-relaxed text-carbon/90">
            “Desde sus cimientos coloniales hasta su constante reconstrucción, la Basílica permanece como signo de esperanza y oración en el corazón de la ciudad.”
          </blockquote>
        </div>

        <h2 id="origenes" class="scroll-mt-24">Orígenes y traslado</h2>
        <p>Sus raíces se remontan a 1773, cuando los frailes dominicos se vieron obligados a trasladar convento e imagen titular desde Antigua Guatemala tras los terremotos que arruinaron la antigua iglesia. La nueva construcción inició en 1788 bajo la dirección del arquitecto <strong>Pedro Garcí-Aguirre</strong> y concluyó en 1808.</p>

        <div class="not-prose my-8 rounded-lg border border-carbon/10 bg-white/60 backdrop-blur p-4 flex gap-4 items-start">
          <div class="h-10 w-10 flex-shrink-0 rounded-full bg-oro/20 flex items-center justify-center font-serif text-lg">1773</div>
          <p class="text-sm leading-relaxed text-carbon/80">Terremotos obligan el traslado dominico. Inicia un nuevo capítulo urbano y espiritual para la Orden en la naciente capital.</p>
        </div>

        <h2 id="reconstrucciones" class="scroll-mt-24">Reconstrucciones sísmicas</h2>
        <p>A lo largo de su historia el edificio ha debido levantarse nuevamente en dos ocasiones: después de los terremotos de 1917–1918 y tras el terremoto de 1976. Estas restauraciones consolidaron su fisonomía neoclásica y aseguraron la preservación de su patrimonio artístico.</p>

        <h2 id="titulo" class="scroll-mt-24">Título de Basílica</h2>
        <p>En 1968 la Santa Sede otorgó el título de <em>Basílica Menor de Nuestra Señora del Rosario</em>, reconociendo el valor espiritual y la importancia arquitectónica del templo dentro de la vida eclesial del país.</p>

        <div class="grid gap-6 my-10 sm:grid-cols-2">
          <div class="rounded-xl border border-carbon/10 bg-white/60 p-5 backdrop-blur">
            <p class="text-xs uppercase tracking-wide text-carbon/60 mb-2">Título Pontificio</p>
            <p class="font-serif text-lg leading-snug">Basílica Menor otorgada en 1968</p>
          </div>
          <div class="rounded-xl border border-carbon/10 bg-white/60 p-5 backdrop-blur">
            <p class="text-xs uppercase tracking-wide text-carbon/60 mb-2">Coronación</p>
            <p class="font-serif text-lg leading-snug">Imagen mariana coronada en 1933</p>
          </div>
        </div>

        <h2 id="virgen" class="scroll-mt-24">La imagen de la Virgen del Rosario</h2>
        <p>La parroquia custodia la histórica imagen mariana —una escultura de plata del siglo XVI— trasladada desde la antigua capital. Fue solemnemente coronada el 28 de enero de 1933 con joyas preciosas, recibiendo el título de <strong>“Reina de Guatemala”</strong>. Cada octubre se celebra una intensa festividad de 31 días con rezos, procesiones y actos devocionales que congregan multitudes de fieles.</p>

        <h2 id="devociones" class="scroll-mt-24">Otras devociones destacadas</h2>
        <p>También se venera la imagen del <strong>Cristo Yacente del Amor (Señor Sepultado)</strong>, consagrada en 1973. Su cortejo de Viernes Santo recorre las calles capitalinas y es parte fundamental de la Semana Santa guatemalteca.</p>

        <h2 id="significado" class="scroll-mt-24">Significado cultural y religioso</h2>
        <p>La Basílica-Parroquia Santo Domingo es hoy un referente de identidad religiosa y patrimonio cultural: articula liturgia, arte sacro, tradición procesional y peregrinación mariana, siendo punto de encuentro para generaciones de fieles.</p>

        <div class="not-prose mt-12 rounded-lg border border-oro/30 bg-oro/10 p-4 text-xs text-carbon/70">
          Fuente: recopilación de crónicas históricas y directorios eclesiales. Horarios y datos pueden variar según disposiciones pastorales vigentes.
        </div>

        <p class="mt-8"><a href="{{ url('/') }}" class="inline-flex items-center gap-2 text-oroscuro hover:text-oro">&larr; Volver al inicio</a></p>
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
