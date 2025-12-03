@extends('layouts.app')
@section('title', 'Horarios — Parroquia Santo Domingo')
@section('meta_description','Horarios de misas, confesiones y atención parroquial en la Basílica del Rosario, Zona 1, Ciudad de Guatemala.')
@section('content')

  @php
    $parish = $portalParish ?? config('portal.parish');
    $schedules = $parish['schedules'] ?? [];
    $officeHours = $parish['office_hours'] ?? [];
    $heroAssets = \App\Support\HeroImage::resolve();
    $heroWebp = $heroAssets['hero_webp'];
    $heroJpg = $heroAssets['hero_jpg'];
    $heroImg = $heroAssets['final'];
  @endphp

  <section class="relative min-h-[42vh] sm:min-h-[48vh] md:min-h-[50vh] w-full overflow-hidden rounded-b-3xl border-b border-carbon/10">
    @if($heroImg)
  <img src="{{ $heroImg }}" alt="Interior del templo" class="absolute inset-0 h-full w-full object-cover" loading="lazy" decoding="async" width="1600" height="600">
    @else
      <div class="absolute inset-0 bg-gradient-to-br from-oro/30 to-oroscuro/20"></div>
    @endif
    <div class="absolute inset-0 bg-carbon/60 mix-blend-multiply"></div>
    <div class="relative z-10 mx-auto flex h-full max-w-5xl items-end px-4 sm:px-6 lg:px-8 pb-6">
      <div>
        <h1 class="font-serif text-3xl font-semibold text-crema drop-shadow">Horarios</h1>
        <p class="mt-1 text-crema/80 text-sm">Consulta las celebraciones y servicios pastorales.</p>
      </div>
    </div>
  </section>

  <section class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8 py-12">
    <div class="grid gap-10 lg:grid-cols-3 lg:items-start">
      <!-- Tarjetas rápidas -->
      <div class="space-y-5 lg:col-span-1">
        <div class="rounded-xl border border-carbon/10 bg-white/70 backdrop-blur p-5">
          <p class="text-xs uppercase tracking-wide text-carbon/60 mb-1">Misas de lunes a viernes</p>
          <p class="font-serif text-lg leading-snug">{{ $schedules['weekday_masses'] ?? '' }} horas</p>
        </div>
        <div class="rounded-xl border border-carbon/10 bg-white/70 backdrop-blur p-5">
          <p class="text-xs uppercase tracking-wide text-carbon/60 mb-1">Sábado</p>
          <p class="font-serif text-lg leading-snug">{{ $schedules['saturday_mass'] ?? '' }} horas</p>
        </div>
        <div class="rounded-xl border border-carbon/10 bg-white/70 backdrop-blur p-5">
          <p class="text-xs uppercase tracking-wide text-carbon/60 mb-1">Domingo</p>
          <p class="font-serif text-lg leading-snug">{{ $schedules['sunday_masses'] ?? '' }} horas</p>
        </div>
        <div class="rounded-xl border border-carbon/10 bg-white/70 backdrop-blur p-5">
          <p class="text-xs uppercase tracking-wide text-carbon/60 mb-1">Rezo del Rosario</p>
          <p class="font-serif text-lg leading-snug">{{ $schedules['rosary'] ?? '' }}</p>
        </div>
        <div class="rounded-xl border border-carbon/10 bg-white/70 backdrop-blur p-5">
          <p class="text-xs uppercase tracking-wide text-carbon/60 mb-1">Hora Santa</p>
          <p class="font-serif text-lg leading-snug">{{ $schedules['holy_hour'] ?? '' }}</p>
        </div>
        <div class="rounded-xl border border-carbon/10 bg-white/70 backdrop-blur p-5">
          <p class="text-xs uppercase tracking-wide text-carbon/60 mb-1">Confesiones</p>
          <p class="font-serif text-lg leading-snug">{{ $schedules['confessions_week'] ?? '' }} · {{ $schedules['confessions_sunday'] ?? '' }} (interior de la basílica)</p>
        </div>
        <div class="rounded-xl border border-oroscuro/30 bg-gradient-to-br from-oro/15 to-oro/5 p-5">
          <p class="text-xs uppercase tracking-wide text-carbon/60 mb-1">Nota</p>
          <p class="text-sm text-carbon/80">En solemnidades o disposiciones pastorales los horarios pueden ajustarse.</p>
        </div>
      </div>

      <!-- Tabla detallada -->
      <div class="lg:col-span-2 space-y-10">
        <div>
          <h2 class="section-heading mb-4">Detalle semanal</h2>
          <div class="overflow-x-auto rounded-xl border border-carbon/10 bg-white/60 backdrop-blur shadow-sm">
            <table class="w-full text-sm">
              <thead class="bg-carbon/5 text-carbon/70">
                <tr>
                  <th class="px-4 py-2 text-left font-medium">Día</th>
                  <th class="px-4 py-2 text-left font-medium">Misas</th>
                  <th class="px-4 py-2 text-left font-medium">Confesiones</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-carbon/10">
                @php
                  $rows = [
                    ['Lunes','7:00 · 12:00 · 18:30','—'],
                    ['Martes','7:00 · 12:00 · 18:30','15:00–17:00'],
                    ['Miércoles','7:00 · 12:00 · 18:30','15:00–17:00'],
                    ['Jueves','7:00 · 12:00 · 18:30','15:00–17:00'],
                    ['Viernes','7:00 · 12:00 · 18:30','15:00–17:00'],
                    ['Sábado','18:30','—'],
                    ['Domingo','6:30 · 8:00 · 12:00 · 16:30 · 18:30','9:30–11:30 (interior de la basílica)'],
                  ];
                @endphp
                @foreach($rows as [$dia,$misas,$conf])
                  <tr class="hover:bg-oro/10/50">
                    <td class="px-4 py-2 font-medium">{{ $dia }}</td>
                    <td class="px-4 py-2">{{ $misas }}</td>
                    <td class="px-4 py-2 text-carbon/70">{{ $conf }}</td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>

        <div class="grid gap-6 sm:grid-cols-2">
          <div class="rounded-xl border border-carbon/10 bg-white/60 backdrop-blur p-5">
            <h3 class="font-serif text-lg font-semibold">Horario de oficina</h3>
            <p class="mt-1 text-sm text-carbon/70">{{ $officeHours['weekdays'] ?? '' }} · {{ $officeHours['saturday'] ?? '' }}</p>
          </div>
          <div class="rounded-xl border border-carbon/10 bg-white/60 backdrop-blur p-5">
            <h3 class="font-serif text-lg font-semibold">Intenciones de misa</h3>
            <p class="mt-1 text-sm text-carbon/70">Próximamente formulario en línea. Actualmente en despacho.</p>
          </div>
        </div>

        <div class="rounded-xl border border-oro/30 bg-oro/10 p-5 text-xs text-carbon/70">
          Estos horarios pueden variar en solemnidades nacionales, fiestas patronales o disposiciones del Arzobispado.
        </div>

        <div class="pt-4">
          <a href="{{ url('/contacto') }}" class="btn-secondary">Contacto y ubicación</a>
        </div>
      </div>
    </div>
  </section>
@endsection
