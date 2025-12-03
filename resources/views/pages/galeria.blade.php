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
  <section class="relative min-h-[42vh] sm:min-h-[48vh] md:min-h-[50vh] w-full overflow-hidden rounded-b-3xl border-b border-carbon/10">
    @if($heroImg)
  <img src="{{ $heroImg }}" alt="Galería Basílica del Rosario" class="absolute inset-0 h-full w-full object-cover" loading="lazy" decoding="async" width="1600" height="600">
    @else
      <div class="absolute inset-0 bg-gradient-to-br from-oro/30 to-oroscuro/20"></div>
    @endif
    <div class="absolute inset-0 bg-carbon/60 mix-blend-multiply"></div>
    <div class="relative z-10 mx-auto flex h-full max-w-5xl items-end px-4 sm:px-6 lg:px-8 pb-6">
      <div>
        <h1 class="font-serif text-3xl font-semibold text-crema drop-shadow">Galería</h1>
        <p class="mt-1 text-crema/80 text-sm">Patrimonio, devoción y vida parroquial.</p>
      </div>
    </div>
  </section>

  <section class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8 py-12">
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
      <div class="mb-6 flex justify-end">
        <span class="text-xs text-carbon/50">{{ $items->count() }} imágenes</span>
      </div>

      <!-- Masonry usando columns -->
  <div class="[column-fill:_balance] columns-1 sm:columns-2 md:columns-3 lg:columns-4 gap-3" data-gallery-grid>
        @foreach($items as $photo)
          <figure class="mb-3 break-inside-avoid relative group cursor-zoom-in overflow-hidden rounded-lg border border-carbon/10 bg-carbon/5" data-gallery-item data-index="{{ $loop->index }}">
              <button type="button" class="relative block w-full focus:outline-none" data-gallery-trigger="{{ $loop->index }}">
                  <x-ui.responsive-image :src="$photo['src']" :alt="$photo['alt']" width="800" height="600" class="w-full transition-transform duration-300 group-hover:scale-[1.04]" />
              </button>
              <figcaption class="pointer-events-none absolute inset-x-0 bottom-0 bg-black/40 text-[10px] tracking-wide uppercase px-2 py-1 text-crema backdrop-blur-sm flex justify-between opacity-0 group-hover:opacity-100 transition-opacity">Imagen {{ $photo['i'] }}</figcaption>
          </figure>
        @endforeach
      </div>
    @endif
  </section>

  <!-- Lightbox modal -->
  <div class="fixed inset-0 z-50 hidden items-center justify-center bg-black/80 backdrop-blur-sm p-4" role="dialog" aria-modal="true" aria-label="Visor de imagen" data-lightbox>
    <button class="absolute top-4 right-4 text-crema hover:text-oro text-xl" data-lightbox-close aria-label="Cerrar">&times;</button>
    <div class="max-w-5xl w-full">
      <div class="relative">
        <button class="absolute left-2 top-1/2 -translate-y-1/2 p-3 text-crema/70 hover:text-crema" data-lightbox-prev aria-label="Anterior">&#10094;</button>
        <button class="absolute right-2 top-1/2 -translate-y-1/2 p-3 text-crema/70 hover:text-crema" data-lightbox-next aria-label="Siguiente">&#10095;</button>
        <img src="" alt="" class="mx-auto max-h-[70vh] rounded-lg shadow-lg" data-lightbox-image>
        <p class="mt-4 text-center text-xs text-crema/70" data-lightbox-caption></p>
      </div>
    </div>
  </div>
@endsection
