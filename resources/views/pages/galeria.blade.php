@extends('layouts.app')
@section('title', 'Galería — Parroquia Santo Domingo')
@section('meta_description', 'Galería fotográfica de la Basílica del Rosario: patrimonio, devoción y vida parroquial en imágenes.')
@section('content')

  @php
    $heroAssets = \App\Support\HeroImage::resolve();
    $heroWebp = $heroAssets['hero_webp'];
    $heroJpg = $heroAssets['hero_jpg'];
    $heroImg = $heroAssets['final'];
  @endphp
  <section class="relative min-h-[32vh] sm:min-h-[42vh] md:min-h-[50vh] w-full overflow-hidden rounded-b-2xl sm:rounded-b-3xl border-b border-carbon/10">
    @if($heroImg)
      <img src="{{ $heroImg }}" alt="Galería Basílica del Rosario" class="absolute inset-0 h-full w-full object-cover" loading="lazy" decoding="async" width="1600" height="600">
    @else
      <div class="absolute inset-0 bg-gradient-to-br from-oro/30 to-oroscuro/20"></div>
    @endif
    <div class="absolute inset-0 bg-gradient-to-t from-carbon/80 via-carbon/50 to-carbon/30"></div>
    <div class="relative z-10 mx-auto flex h-full max-w-5xl items-end px-4 sm:px-6 lg:px-8 pb-4 sm:pb-6">
      <div>
        <h1 class="font-serif text-2xl sm:text-3xl font-semibold text-crema drop-shadow">Galería Fotográfica</h1>
        <p class="mt-1 text-crema/80 text-xs sm:text-sm">Patrimonio, devoción y vida parroquial en imágenes.</p>
      </div>
    </div>
  </section>

  <section class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8 py-8 sm:py-12">
    <!-- Introducción -->
    <div class="mb-6 sm:mb-10 text-center max-w-3xl mx-auto">
      <p class="text-xs sm:text-sm text-carbon/70">Contempla la belleza de nuestra Basílica: el arte sacro, las procesiones y los momentos de comunidad que nos unen como familia parroquial.</p>
    </div>
    @php
      $items = collect(range(1,30))->map(function($i){
          $base = 'galeria ' . $i;
          $webp = public_path("images/{$base}.webp");
          $jpg  = public_path("images/{$base}.jpg");
          $jpeg = public_path("images/{$base}.jpeg");
          $src = file_exists($webp) ? asset("images/{$base}.webp") : (file_exists($jpg) ? asset("images/{$base}.jpg") : (file_exists($jpeg) ? asset("images/{$base}.jpeg") : null));
          return $src ? [
            'i'=>$i,
            'src'=>$src,
            'alt'=>"Fotografía {$i} de la basílica"
          ] : null;
      })->filter()->values();
    @endphp

    @if($items->isEmpty())
      <p class="text-sm text-carbon/60">No hay imágenes disponibles todavía.</p>
    @else
      <!-- Controles -->
      <div class="mb-4 sm:mb-6 flex justify-end">
        <span class="text-xs text-carbon/50">{{ $items->count() }} imágenes</span>
      </div>

      <!-- Masonry usando columns - 2 columnas en móvil -->
      <div class="[column-fill:_balance] columns-2 sm:columns-2 md:columns-3 lg:columns-4 gap-2 sm:gap-3" data-gallery-grid>
        @foreach($items as $photo)
          <figure class="mb-2 sm:mb-3 break-inside-avoid relative group cursor-zoom-in overflow-hidden rounded-lg border border-carbon/10 bg-carbon/5" data-gallery-item data-index="{{ $loop->index }}">
              <button type="button" class="relative block w-full focus:outline-none" data-gallery-trigger="{{ $loop->index }}">
                  <x-ui.responsive-image :src="$photo['src']" :alt="$photo['alt']" width="800" height="600" class="w-full transition-transform duration-300 group-hover:scale-[1.04]" />
              </button>
              <figcaption class="pointer-events-none absolute inset-x-0 bottom-0 bg-black/40 text-[10px] tracking-wide uppercase px-2 py-1 text-crema backdrop-blur-sm hidden sm:flex justify-between opacity-0 group-hover:opacity-100 transition-opacity">Imagen {{ $photo['i'] }}</figcaption>
          </figure>
        @endforeach
      </div>
    @endif
  </section>

  <!-- Lightbox modal - optimizado para móvil -->
  <div class="fixed inset-0 z-50 hidden items-center justify-center bg-black/90 backdrop-blur-sm p-2 sm:p-4 safe-top safe-bottom" role="dialog" aria-modal="true" aria-label="Visor de imagen" data-lightbox>
    <button class="absolute top-2 right-2 sm:top-4 sm:right-4 text-crema hover:text-oro text-2xl sm:text-xl z-10 p-2" data-lightbox-close aria-label="Cerrar">&times;</button>
    <div class="max-w-5xl w-full h-full flex items-center">
      <div class="relative w-full">
        <button class="absolute left-0 top-1/2 -translate-y-1/2 p-2 sm:p-3 text-crema/70 hover:text-crema text-2xl sm:text-base z-10" data-lightbox-prev aria-label="Anterior">&#10094;</button>
        <button class="absolute right-0 top-1/2 -translate-y-1/2 p-2 sm:p-3 text-crema/70 hover:text-crema text-2xl sm:text-base z-10" data-lightbox-next aria-label="Siguiente">&#10095;</button>
        <img src="" alt="" class="mx-auto max-h-[80vh] max-w-[calc(100%-60px)] rounded-lg shadow-lg" data-lightbox-image>
        <p class="mt-2 sm:mt-4 text-center text-xs text-crema/70" data-lightbox-caption></p>
      </div>
    </div>
  </div>
@endsection
