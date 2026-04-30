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

  <section class="relative min-h-[35vh] sm:min-h-[42vh] md:min-h-[50vh] w-full overflow-hidden rounded-b-2xl sm:rounded-b-3xl border-b border-carbon/10">
    @if($heroImg)
      <img src="{{ $heroImg }}" alt="Interior del templo" class="absolute inset-0 h-full w-full object-cover" loading="lazy" decoding="async" width="1600" height="600">
    @else
      <div class="absolute inset-0 bg-gradient-to-br from-oro/30 to-oroscuro/20"></div>
    @endif
    <div class="absolute inset-0 bg-gradient-to-t from-carbon/80 via-carbon/50 to-carbon/30"></div>
    <div class="relative z-10 mx-auto flex h-full max-w-5xl items-end px-4 sm:px-6 lg:px-8 pb-4 sm:pb-6">
      <div>
        <h1 class="font-serif text-2xl sm:text-3xl font-semibold text-crema drop-shadow">Horarios y Sacramentos</h1>
        <p class="mt-1 text-crema/80 text-xs sm:text-sm max-w-xl">Consulta las celebraciones litúrgicas y servicios pastorales. «Venid a mí todos los que estáis cansados y agobiados.»</p>
      </div>
    </div>
  </section>

  <section class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8 py-8 sm:py-12">
    
    <!-- Horarios rápidos para móvil -->
    <div class="sm:hidden mb-8">
      <h2 class="text-lg font-serif font-semibold text-carbon mb-4">Horarios de Misas</h2>
      <div class="space-y-3">
        <div class="flex items-center justify-between p-4 rounded-xl bg-gradient-to-r from-oro/20 to-oro/10 border border-oro/30">
          <div>
            <p class="text-xs text-carbon/60 uppercase tracking-wide">Lunes a Viernes</p>
            <p class="font-serif text-base font-medium">7:00 · 12:00 · 18:30</p>
          </div>
          <svg class="w-5 h-5 text-oro" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a8 8 0 100 16 8 8 0 000-16zm1 11H9v-1h2v1zm0-3H9V6h2v4z"/></svg>
        </div>
        <div class="flex items-center justify-between p-4 rounded-xl bg-white border border-carbon/10">
          <div>
            <p class="text-xs text-carbon/60 uppercase tracking-wide">Sábado</p>
            <p class="font-serif text-base font-medium">{{ $schedules['saturday_mass'] ?? '18:30' }}</p>
          </div>
        </div>
        <div class="flex items-center justify-between p-4 rounded-xl bg-gradient-to-r from-oroscuro/10 to-oro/10 border border-oroscuro/20">
          <div>
            <p class="text-xs text-carbon/60 uppercase tracking-wide">Domingo</p>
            <p class="font-serif text-base font-medium">6:30 · 8:00 · 12:00 · 16:30 · 18:30</p>
          </div>
        </div>
      </div>
      
      <div class="mt-6 space-y-3">
        <div class="p-4 rounded-xl bg-white border border-carbon/10">
          <p class="text-xs text-carbon/60 uppercase tracking-wide mb-1">Rosario</p>
          <p class="font-serif text-sm">{{ $schedules['rosary'] ?? 'Todos los días antes de la Santa Misa' }}</p>
        </div>
        <div class="p-4 rounded-xl bg-white border border-carbon/10">
          <p class="text-xs text-carbon/60 uppercase tracking-wide mb-1">Confesiones</p>
          <p class="font-serif text-sm">Mar-Vie 15:00–17:00 · Dom 9:30–11:30</p>
        </div>
      </div>
    </div>
    
    <div class="grid gap-8 sm:gap-10 lg:grid-cols-3 lg:items-start">
      <!-- Tarjetas rápidas (desktop) -->
      <div class="hidden sm:block space-y-5 lg:col-span-1">
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
          <p class="text-xs uppercase tracking-wide text-carbon/60 mb-1">Preparación</p>
          <p class="text-sm text-carbon/80">Te invitamos a llegar 10 minutos antes de la Santa Misa para disponerte en silencio y oración. El Santísimo Sacramento está expuesto para tu adoración.</p>
        </div>
        <div class="rounded-xl border border-oroscuro/30 bg-gradient-to-br from-oro/15 to-oro/5 p-5">
          <p class="text-xs uppercase tracking-wide text-carbon/60 mb-1">Nota</p>
          <p class="text-sm text-carbon/80">En solemnidades, fiestas patronales o disposiciones pastorales del Arzobispado, los horarios pueden ajustarse.</p>
        </div>
      </div>

      <!-- Tabla detallada -->
      <div class="lg:col-span-2 space-y-8 sm:space-y-10">
        <div class="hidden sm:block">
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

        <div class="grid gap-4 sm:gap-6 grid-cols-1 sm:grid-cols-2">
          <div class="rounded-xl border border-carbon/10 bg-white/60 backdrop-blur p-4 sm:p-5">
            <h3 class="font-serif text-base sm:text-lg font-semibold">Horario de oficina</h3>
            <p class="mt-1 text-sm text-carbon/70">{{ $officeHours['weekdays'] ?? '' }} · {{ $officeHours['saturday'] ?? '' }}</p>
          </div>
          <div class="rounded-xl border border-carbon/10 bg-white/60 backdrop-blur p-4 sm:p-5">
            <h3 class="font-serif text-base sm:text-lg font-semibold">Intenciones de misa</h3>
            <p class="mt-1 text-sm text-carbon/70">Solicita tu intención en línea o directamente en el despacho parroquial.</p>
            <a href="{{ url('/intenciones') }}" class="mt-2 inline-flex items-center gap-1 text-sm text-oro hover:text-oroscuro font-medium">
                Solicitar intención <span aria-hidden="true">→</span>
            </a>
          </div>
        </div>

        <!-- Sección de Sacramentos -->
        <div class="mt-6 sm:mt-10">
          <h2 class="section-heading mb-4 sm:mb-6 text-lg sm:text-xl">Sacramentos y Vida Cristiana</h2>
          <div class="grid gap-4 sm:gap-6 grid-cols-2 lg:grid-cols-3">
            <div class="rounded-xl border border-carbon/10 bg-white/60 backdrop-blur p-4 sm:p-5">
              <div class="h-8 w-8 sm:h-10 sm:w-10 rounded-full bg-oro/20 flex items-center justify-center mb-2 sm:mb-3">
                <span class="text-lg sm:text-xl">💧</span>
              </div>
              <h3 class="font-serif text-sm sm:text-lg font-semibold">Bautismo</h3>
              <p class="mt-1 sm:mt-2 text-xs sm:text-sm text-carbon/70 hidden sm:block">Sacramento de iniciación cristiana. Se requiere plática pre-bautismal para padres y padrinos. Consultar fechas en despacho.</p>
              <p class="mt-1 text-xs text-carbon/70 sm:hidden">Plática pre-bautismal requerida.</p>
            </div>
            <div class="rounded-xl border border-carbon/10 bg-white/60 backdrop-blur p-4 sm:p-5">
              <div class="h-8 w-8 sm:h-10 sm:w-10 rounded-full bg-oro/20 flex items-center justify-center mb-2 sm:mb-3">
                <span class="text-lg sm:text-xl">🕊️</span>
              </div>
              <h3 class="font-serif text-sm sm:text-lg font-semibold">Confirmación</h3>
              <p class="mt-1 sm:mt-2 text-xs sm:text-sm text-carbon/70 hidden sm:block">Fortalecimiento en el Espíritu Santo. Preparación catequética durante el año. Inscripciones en despacho parroquial.</p>
              <p class="mt-1 text-xs text-carbon/70 sm:hidden">Preparación catequética anual.</p>
            </div>
            <div class="rounded-xl border border-carbon/10 bg-white/60 backdrop-blur p-4 sm:p-5">
              <div class="h-8 w-8 sm:h-10 sm:w-10 rounded-full bg-oro/20 flex items-center justify-center mb-2 sm:mb-3">
                <span class="text-lg sm:text-xl">🍞</span>
              </div>
              <h3 class="font-serif text-sm sm:text-lg font-semibold">Primera Comunión</h3>
              <p class="mt-1 sm:mt-2 text-xs sm:text-sm text-carbon/70 hidden sm:block">Encuentro con Cristo Eucaristía. Catequesis para niños con preparación de dos años. Informes en despacho.</p>
              <p class="mt-1 text-xs text-carbon/70 sm:hidden">Catequesis de 2 años.</p>
            </div>
            <div class="rounded-xl border border-carbon/10 bg-white/60 backdrop-blur p-4 sm:p-5">
              <div class="h-8 w-8 sm:h-10 sm:w-10 rounded-full bg-oro/20 flex items-center justify-center mb-2 sm:mb-3">
                <span class="text-lg sm:text-xl">💍</span>
              </div>
              <h3 class="font-serif text-sm sm:text-lg font-semibold">Matrimonio</h3>
              <p class="mt-1 sm:mt-2 text-xs sm:text-sm text-carbon/70 hidden sm:block">Sacramento del amor conyugal. Requiere curso pre-matrimonial y documentación. Reservar con 3 meses de anticipación.</p>
              <p class="mt-1 text-xs text-carbon/70 sm:hidden">Reservar 3 meses antes.</p>
            </div>
            <div class="rounded-xl border border-carbon/10 bg-white/60 backdrop-blur p-4 sm:p-5">
              <div class="h-8 w-8 sm:h-10 sm:w-10 rounded-full bg-oro/20 flex items-center justify-center mb-2 sm:mb-3">
                <span class="text-lg sm:text-xl">✝️</span>
              </div>
              <h3 class="font-serif text-sm sm:text-lg font-semibold">Confesión</h3>
              <p class="mt-1 sm:mt-2 text-xs sm:text-sm text-carbon/70 hidden sm:block">Sacramento de la reconciliación. Disponible en horarios indicados o previa cita con un sacerdote. ¡Dios te espera!</p>
              <p class="mt-1 text-xs text-carbon/70 sm:hidden">Horarios indicados o cita.</p>
            </div>
            <div class="rounded-xl border border-carbon/10 bg-white/60 backdrop-blur p-4 sm:p-5">
              <div class="h-8 w-8 sm:h-10 sm:w-10 rounded-full bg-oro/20 flex items-center justify-center mb-2 sm:mb-3">
                <span class="text-lg sm:text-xl">🙏</span>
              </div>
              <h3 class="font-serif text-sm sm:text-lg font-semibold">Unción de Enfermos</h3>
              <p class="mt-1 sm:mt-2 text-xs sm:text-sm text-carbon/70 hidden sm:block">Consuelo y fortaleza para los enfermos. Llamar al despacho para visitas a domicilio u hospitales.</p>
              <p class="mt-1 text-xs text-carbon/70 sm:hidden">Visitas a domicilio.</p>
            </div>
          </div>
        </div>

        <div class="rounded-xl border border-oro/30 bg-oro/10 p-4 sm:p-5 text-xs text-carbon/70 mt-4 sm:mt-6">
          Estos horarios pueden variar en solemnidades nacionales, fiestas patronales o disposiciones del Arzobispado.
        </div>

        <div class="pt-4 flex flex-col sm:flex-row gap-3">
          <a href="{{ url('/contacto') }}" class="btn-secondary text-center">Contacto y ubicación</a>
          <a href="https://wa.me/50230347700" target="_blank" rel="noopener" class="sm:hidden inline-flex items-center justify-center gap-2 px-4 py-3 rounded-xl bg-green-600 text-white font-medium text-sm">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M12 0C5.373 0 0 5.373 0 12c0 2.625.846 5.059 2.284 7.034L.789 23.789l4.905-1.563A11.943 11.943 0 0012 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 22c-2.137 0-4.146-.68-5.798-1.834l-.416-.286-2.91.928.872-2.783-.292-.426A9.936 9.936 0 012 12C2 6.486 6.486 2 12 2s10 4.486 10 10-4.486 10-10 10z"/></svg>
            Escribir al WhatsApp
          </a>
        </div>
      </div>
    </div>
  </section>
@endsection
