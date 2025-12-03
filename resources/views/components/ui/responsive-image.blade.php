@props([
  'src',      // ruta absoluta (asset()) o relativa
  'alt' => '',
  'width' => null,
  'height' => null,
  'class' => '',
  'lazy' => true,
  'object' => 'cover', // cover | contain | fill
  'ratio' => null, // e.g. '4/3'
  'priority' => false,
  'loading' => null,
  'decoding' => 'async',
])
@php
    $loading = $priority ? 'eager' : ($loading ?? ($lazy ? 'lazy' : 'eager'));
    $fetch = $priority ? 'high' : 'auto';
    $style = '';
    if($ratio){
        // Aspect-ratio fallback container
        [$w,$h] = array_pad(explode('/', $ratio), 2, null);
    }
@endphp
@php
    // Muy simple srcset: si la imagen termina en .jpg|.png|.webp y tiene width conocido, generamos variantes escaladas
    $srcset = null;
    if($width && preg_match('/\.(jpe?g|png|webp)$/i', $src)){
        $w = (int)$width;
        $sizes = collect([480, 768, 1024, 1280])->filter(fn($s)=>$s < $w)->push($w)->unique()->values();
        // No generamos archivos reales aquí; solo sugerimos el tamaño base como hint (mejora leve de selección). Para real optimization habría que generar variantes.
        $srcset = $sizes->map(fn($s)=> $src.' '.$s.'w')->join(', ');
    }
@endphp
<img
  src="{{ $src }}"
  @if($srcset) srcset="{{ $srcset }}" sizes="(max-width: 768px) 100vw, 1600px" @endif
  @if($alt) alt="{{ $alt }}" @endif
  @if($width) width="{{ $width }}" @endif
  @if($height) height="{{ $height }}" @endif
  loading="{{ $loading }}"
  decoding="{{ $decoding }}"
  fetchpriority="{{ $fetch }}"
  class="{{ $class }} object-{{ $object }}"
/>