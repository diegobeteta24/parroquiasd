@extends('layouts.app')
@section('title', 'Contacto — Parroquia Santo Domingo')
@section('meta_description','Contacto de la Parroquia Santo Domingo: dirección, teléfonos, WhatsApp pastoral, correo y horarios de oficina en Ciudad de Guatemala.')
@section('content')

  @php
    $parish = $portalParish ?? config('portal.parish');
    $parishContact = $parish['contact'] ?? [];
    $parishAddress = $parish['address'] ?? [];
    $officeHours = $parish['office_hours'] ?? [];
    $heroAssets = \App\Support\HeroImage::resolve();
    $heroWebp = $heroAssets['hero_webp'];
    $heroJpg = $heroAssets['hero_jpg'];
    $heroImg = $heroAssets['final'];
  @endphp

  <section class="relative min-h-[42vh] sm:min-h-[48vh] md:min-h-[50vh] w-full overflow-hidden rounded-b-3xl border-b border-carbon/10">
    @if($heroImg)
  <img src="{{ $heroImg }}" alt="Templo Parroquia Santo Domingo" class="absolute inset-0 h-full w-full object-cover" loading="lazy" decoding="async" width="1600" height="600">
    @else
      <div class="absolute inset-0 bg-gradient-to-br from-oro/30 to-oroscuro/20"></div>
    @endif
    <div class="absolute inset-0 bg-carbon/60 mix-blend-multiply"></div>
    <div class="relative z-10 mx-auto flex h-full max-w-5xl items-end px-4 sm:px-6 lg:px-8 pb-6">
      <div>
        <h1 class="font-serif text-3xl font-semibold text-crema drop-shadow">Contacto</h1>
        <p class="mt-1 text-crema/80 text-sm">Ubicación y medios de comunicación pastoral.</p>
      </div>
    </div>
  </section>

  <section class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8 py-12">
    <div class="grid gap-10 lg:grid-cols-3 lg:items-start">
      <!-- Datos principales -->
      <div class="space-y-5 lg:col-span-1">
        <div class="rounded-xl border border-carbon/10 bg-white/70 backdrop-blur p-5">
          <p class="text-xs uppercase tracking-wide text-carbon/60 mb-1">Dirección</p>
          <p class="font-serif text-lg leading-snug">
            {{ $parishAddress['street'] ?? '' }}, {{ $parishAddress['zone'] ?? '' }}<br>
            {{ $parishAddress['city'] ?? 'Ciudad de Guatemala' }}, {{ $parishAddress['region'] ?? 'Guatemala' }}
          </p>
        </div>
        <div class="rounded-xl border border-carbon/10 bg-white/70 backdrop-blur p-5">
          <p class="text-xs uppercase tracking-wide text-carbon/60 mb-1">Teléfonos</p>
          <p class="text-sm"><span class="font-medium">Parroquia:</span> <a class="hover:text-oro" href="{{ $parishContact['phone_link'] ?? '#' }}">{{ $parishContact['phone_display'] ?? $parishContact['phone'] }}</a></p>
          <p class="text-sm mt-1"><span class="font-medium">WhatsApp para intenciones:</span> <a class="hover:text-oro" href="{{ $parishContact['whatsapp_link'] ?? '#' }}" target="_blank" rel="noopener noreferrer">{{ $parishContact['whatsapp_display'] ?? $parishContact['whatsapp'] }}</a></p>
          <div class="mt-3">
            <a href="{{ $parishContact['whatsapp_link'] ?? '#' }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 rounded-full bg-oro px-4 py-2 text-sm font-semibold text-carbon shadow hover:bg-oro/95 focus:outline-none focus-visible:ring-2 focus-visible:ring-oro/60">
              <span>Escribir por WhatsApp</span>
            </a>
          </div>
        </div>
        <div class="rounded-xl border border-carbon/10 bg-white/70 backdrop-blur p-5">
          <p class="text-xs uppercase tracking-wide text-carbon/60 mb-1">Correo</p>
          <p>
            <a href="mailto:{{ $parishContact['email'] ?? '' }}" class="hover:text-oro break-all inline-block max-w-full align-top text-sm leading-snug">
              {{ $parishContact['email'] ?? '' }}
            </a>
          </p>
        </div>
        <div class="rounded-xl border border-oroscuro/30 bg-gradient-to-br from-oro/15 to-oro/5 p-5">
          <p class="text-xs uppercase tracking-wide text-carbon/60 mb-1">Horario de oficina</p>
          <p class="text-sm">{{ $officeHours['weekdays'] ?? '' }} · {{ $officeHours['saturday'] ?? '' }}</p>
          <p class="mt-2 text-xs text-carbon/70">{{ $officeHours['notes'] ?? '' }}</p>
        </div>
      </div>

      <!-- Mapa y mensaje -->
      <div class="lg:col-span-2 space-y-8">
        <div class="rounded-xl border border-carbon/10 bg-white/60 backdrop-blur p-4">
          <h2 class="font-serif text-xl font-semibold mb-3">Ubicación</h2>
          <div class="aspect-[4/3] overflow-hidden rounded-lg border border-carbon/10">
            <iframe title="Mapa ubicación Basílica del Rosario" src="{{ $parishContact['maps_embed'] ?? '' }}" width="600" height="450" style="border:0;" allowfullscreen loading="lazy" referrerpolicy="no-referrer-when-downgrade" class="h-full w-full"></iframe>
          </div>
        </div>

        <div class="rounded-xl border border-carbon/10 bg-white/60 backdrop-blur p-5">
          <h2 class="font-serif text-xl font-semibold mb-2">Escríbenos</h2>
          <p class="text-sm text-carbon/70">Pronto habilitaremos un formulario en línea. Mientras tanto puedes comunicarte por teléfono o WhatsApp.</p>
          <div class="mt-4 flex flex-wrap gap-3">
            <a href="{{ $parishContact['phone_link'] ?? '#' }}" class="btn-secondary">Llamar</a>
            <a href="{{ $parishContact['whatsapp_link'] ?? '#' }}" target="_blank" rel="noopener noreferrer" class="btn-primary">WhatsApp</a>
            <a href="mailto:{{ $parishContact['email'] ?? '' }}" class="btn-secondary">Correo</a>
          </div>
        </div>
      </div>
    </div>
  </section>
@endsection
