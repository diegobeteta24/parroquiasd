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

  <section class="relative min-h-[32vh] sm:min-h-[42vh] md:min-h-[50vh] w-full overflow-hidden rounded-b-2xl sm:rounded-b-3xl border-b border-carbon/10">
    @if($heroImg)
      <img src="{{ $heroImg }}" alt="Templo Parroquia Santo Domingo" class="absolute inset-0 h-full w-full object-cover" loading="lazy" decoding="async" width="1600" height="600">
    @else
      <div class="absolute inset-0 bg-gradient-to-br from-oro/30 to-oroscuro/20"></div>
    @endif
    <div class="absolute inset-0 bg-gradient-to-t from-carbon/80 via-carbon/50 to-carbon/30"></div>
    <div class="relative z-10 mx-auto flex h-full max-w-5xl items-end px-4 sm:px-6 lg:px-8 pb-4 sm:pb-6">
      <div>
        <h1 class="font-serif text-2xl sm:text-3xl font-semibold text-crema drop-shadow">Contacto Pastoral</h1>
        <p class="mt-1 text-crema/80 text-xs sm:text-sm">Estamos para servirte con amor fraterno.</p>
      </div>
    </div>
  </section>

  <section class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8 py-8 sm:py-12">
    
    <!-- Acciones rápidas para móvil -->
    <div class="sm:hidden mb-6 grid grid-cols-2 gap-3">
      <a href="tel:+50222321621" class="flex items-center justify-center gap-2 p-4 rounded-xl bg-white border border-carbon/10 text-center">
        <svg class="w-5 h-5 text-oro" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
        <span class="text-sm font-medium">Llamar</span>
      </a>
      <a href="https://wa.me/50230347700" target="_blank" rel="noopener" class="flex items-center justify-center gap-2 p-4 rounded-xl bg-green-600 text-white text-center">
        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/></svg>
        <span class="text-sm font-medium">WhatsApp</span>
      </a>
    </div>
    
    <!-- Mensaje de bienvenida -->
    <div class="mb-6 sm:mb-10 rounded-xl border border-oro/30 bg-gradient-to-br from-oro/10 to-oro/5 p-4 sm:p-6 text-center">
      <p class="font-serif text-base sm:text-xl text-carbon/90">«La puerta de la Iglesia siempre está abierta. Ven como estás, Dios te espera.»</p>
      <p class="mt-1 sm:mt-2 text-xs sm:text-sm text-carbon/60">Los frailes dominicos te recibimos con los brazos abiertos.</p>
    </div>

    <div class="grid gap-6 sm:gap-10 lg:grid-cols-3 lg:items-start">
      <!-- Datos principales -->
      <div class="space-y-4 sm:space-y-5 lg:col-span-1">
        <div class="rounded-xl border border-carbon/10 bg-white/70 backdrop-blur p-4 sm:p-5">
          <p class="text-xs uppercase tracking-wide text-carbon/60 mb-1">Dirección</p>
          <p class="font-serif text-base sm:text-lg leading-snug">
            {{ $parishAddress['street'] ?? '' }}, {{ $parishAddress['zone'] ?? '' }}<br>
            {{ $parishAddress['city'] ?? 'Ciudad de Guatemala' }}
          </p>
          <a href="https://maps.app.goo.gl/FWYSYQJVRVEdJrMN8" target="_blank" rel="noopener" class="mt-2 inline-flex items-center gap-1 text-sm text-oro hover:text-oroscuro">
            Ver en Google Maps <span>→</span>
          </a>
        </div>
        <div class="rounded-xl border border-carbon/10 bg-white/70 backdrop-blur p-4 sm:p-5">
          <p class="text-xs uppercase tracking-wide text-carbon/60 mb-1">Teléfonos</p>
          <p class="text-sm"><span class="font-medium">Parroquia:</span> <a class="hover:text-oro" href="{{ $parishContact['phone_link'] ?? '#' }}">{{ $parishContact['phone_display'] ?? $parishContact['phone'] }}</a></p>
          <p class="text-sm mt-1"><span class="font-medium">WhatsApp:</span> <a class="hover:text-oro" href="{{ $parishContact['whatsapp_link'] ?? '#' }}" target="_blank" rel="noopener noreferrer">{{ $parishContact['whatsapp_display'] ?? $parishContact['whatsapp'] }}</a></p>
          <div class="hidden sm:block mt-3">
            <a href="{{ $parishContact['whatsapp_link'] ?? '#' }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 rounded-full bg-oro px-4 py-2 text-sm font-semibold text-carbon shadow hover:bg-oro/95 focus:outline-none focus-visible:ring-2 focus-visible:ring-oro/60">
              <span>Escribir por WhatsApp</span>
            </a>
          </div>
        </div>
        <div class="rounded-xl border border-carbon/10 bg-white/70 backdrop-blur p-4 sm:p-5">
          <p class="text-xs uppercase tracking-wide text-carbon/60 mb-1">Correo</p>
          <p>
            <a href="mailto:{{ $parishContact['email'] ?? '' }}" class="hover:text-oro break-all inline-block max-w-full align-top text-sm leading-snug">
              {{ $parishContact['email'] ?? '' }}
            </a>
          </p>
        </div>
        <div class="rounded-xl border border-oroscuro/30 bg-gradient-to-br from-oro/15 to-oro/5 p-4 sm:p-5">
          <p class="text-xs uppercase tracking-wide text-carbon/60 mb-1">Horario de oficina</p>
          <p class="text-sm">{{ $officeHours['weekdays'] ?? '' }}</p>
          <p class="text-sm">{{ $officeHours['saturday'] ?? '' }}</p>
        </div>
      </div>

      <!-- Mapa y mensaje -->
      <div class="lg:col-span-2 space-y-6 sm:space-y-8">
        <div class="rounded-xl border border-carbon/10 bg-white/60 backdrop-blur p-4">
          <h2 class="font-serif text-lg sm:text-xl font-semibold mb-3">Ubicación</h2>
          <div class="aspect-[4/3] sm:aspect-[16/10] overflow-hidden rounded-lg border border-carbon/10">
            <iframe title="Mapa ubicación Basílica del Rosario" src="{{ $parishContact['maps_embed'] ?? '' }}" width="600" height="450" style="border:0;" allowfullscreen loading="lazy" referrerpolicy="no-referrer-when-downgrade" class="h-full w-full"></iframe>
          </div>
        </div>

        <div class="rounded-xl border border-carbon/10 bg-white/60 backdrop-blur p-4 sm:p-5">
          <h2 class="font-serif text-lg sm:text-xl font-semibold mb-2">Escríbenos</h2>
          <p class="text-xs sm:text-sm text-carbon/70 mb-4">¿Tienes alguna consulta pastoral o necesitas información sobre sacramentos?</p>
          
          @if(session('success'))
            <div class="mb-4 rounded-lg bg-green-50 border border-green-200 p-4">
              <div class="flex items-start gap-3">
                <svg class="h-5 w-5 text-green-600 flex-shrink-0 mt-0.5" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <p class="text-green-800 text-sm">{{ session('success') }}</p>
              </div>
            </div>
          @endif

          @if(session('error'))
            <div class="mb-4 rounded-lg bg-red-50 border border-red-200 p-4">
              <div class="flex items-start gap-3">
                <svg class="h-5 w-5 text-red-600 flex-shrink-0 mt-0.5" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <p class="text-red-800 text-sm">{{ session('error') }}</p>
              </div>
            </div>
          @endif

          @if($errors->any())
            <div class="mb-4 rounded-lg bg-red-50 border border-red-200 p-4">
              <ul class="text-red-800 text-sm space-y-1">
                @foreach($errors->all() as $error)
                  <li>• {{ $error }}</li>
                @endforeach
              </ul>
            </div>
          @endif
          
          <!-- Formulario de contacto -->
          <form action="{{ route('contacto.send') }}" method="POST" class="space-y-4">
            @csrf
            <div class="grid gap-4 sm:grid-cols-2">
              <div>
                <label for="nombre" class="block text-sm font-medium text-carbon/80 mb-1">Nombre completo</label>
                <input type="text" id="nombre" name="nombre" value="{{ old('nombre') }}" required class="w-full rounded-lg border border-carbon/20 bg-white/80 px-3 sm:px-4 py-2.5 sm:py-2 text-base sm:text-sm focus:border-oro focus:ring-oro">
              </div>
              <div>
                <label for="email" class="block text-sm font-medium text-carbon/80 mb-1">Correo electrónico</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required class="w-full rounded-lg border border-carbon/20 bg-white/80 px-3 sm:px-4 py-2.5 sm:py-2 text-base sm:text-sm focus:border-oro focus:ring-oro">
              </div>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
              <div>
                <label for="telefono" class="block text-sm font-medium text-carbon/80 mb-1">Teléfono (opcional)</label>
                <input type="tel" id="telefono" name="telefono" value="{{ old('telefono') }}" class="w-full rounded-lg border border-carbon/20 bg-white/80 px-3 sm:px-4 py-2.5 sm:py-2 text-base sm:text-sm focus:border-oro focus:ring-oro">
              </div>
              <div>
                <label for="asunto" class="block text-sm font-medium text-carbon/80 mb-1">Asunto</label>
                <select id="asunto" name="asunto" required class="w-full rounded-lg border border-carbon/20 bg-white/80 px-3 sm:px-4 py-2.5 sm:py-2 text-base sm:text-sm focus:border-oro focus:ring-oro">
                  <option value="">Selecciona un tema...</option>
                  <option value="informacion_general" {{ old('asunto') == 'informacion_general' ? 'selected' : '' }}>Información general</option>
                  <option value="bautismo" {{ old('asunto') == 'bautismo' ? 'selected' : '' }}>Bautismo</option>
                  <option value="matrimonio" {{ old('asunto') == 'matrimonio' ? 'selected' : '' }}>Matrimonio</option>
                  <option value="confirmacion" {{ old('asunto') == 'confirmacion' ? 'selected' : '' }}>Confirmación / Primera Comunión</option>
                  <option value="confesion" {{ old('asunto') == 'confesion' ? 'selected' : '' }}>Confesión</option>
                  <option value="uncion" {{ old('asunto') == 'uncion' ? 'selected' : '' }}>Unción de enfermos</option>
                  <option value="intenciones" {{ old('asunto') == 'intenciones' ? 'selected' : '' }}>Intenciones de misa</option>
                  <option value="otro" {{ old('asunto') == 'otro' ? 'selected' : '' }}>Otro</option>
                </select>
              </div>
            </div>
            <div>
              <label for="mensaje" class="block text-sm font-medium text-carbon/80 mb-1">Mensaje</label>
              <textarea id="mensaje" name="mensaje" rows="3" required class="w-full rounded-lg border border-carbon/20 bg-white/80 px-3 sm:px-4 py-2.5 sm:py-2 text-base sm:text-sm focus:border-oro focus:ring-oro" placeholder="Escribe tu consulta...">{{ old('mensaje') }}</textarea>
            </div>
            <div class="flex flex-col sm:flex-row gap-3">
              <button type="submit" class="btn-primary w-full sm:w-auto">Enviar mensaje</button>
            </div>
          </form>
          <p class="mt-3 text-xs text-carbon/50">Para urgencias (enfermos graves, fallecimientos), favor llamar directamente.</p>
        </div>

        <!-- Servicios pastorales adicionales -->
        <div class="rounded-xl border border-carbon/10 bg-white/60 backdrop-blur p-4 sm:p-5">
          <h2 class="font-serif text-lg sm:text-xl font-semibold mb-3 sm:mb-4">Servicios Pastorales</h2>
          <div class="grid gap-3 sm:gap-4 grid-cols-2">
            <div class="flex items-start gap-2 sm:gap-3">
              <div class="h-7 w-7 sm:h-8 sm:w-8 rounded-full bg-oro/20 flex items-center justify-center flex-shrink-0">
                <span class="text-xs sm:text-sm">🏠</span>
              </div>
              <div>
                <p class="font-medium text-xs sm:text-sm">Visitas a enfermos</p>
                <p class="text-[10px] sm:text-xs text-carbon/60 hidden sm:block">Llevamos la Eucaristía a domicilio</p>
              </div>
            </div>
            <div class="flex items-start gap-2 sm:gap-3">
              <div class="h-7 w-7 sm:h-8 sm:w-8 rounded-full bg-oro/20 flex items-center justify-center flex-shrink-0">
                <span class="text-xs sm:text-sm">🙏</span>
              </div>
              <div>
                <p class="font-medium text-xs sm:text-sm">Dirección espiritual</p>
                <p class="text-[10px] sm:text-xs text-carbon/60 hidden sm:block">Con un fraile dominico</p>
              </div>
            </div>
            <div class="flex items-start gap-2 sm:gap-3">
              <div class="h-7 w-7 sm:h-8 sm:w-8 rounded-full bg-oro/20 flex items-center justify-center flex-shrink-0">
                <span class="text-xs sm:text-sm">🏛️</span>
              </div>
              <div>
                <p class="font-medium text-xs sm:text-sm">Bendición de hogares</p>
                <p class="text-[10px] sm:text-xs text-carbon/60 hidden sm:block">Solicitar en despacho</p>
              </div>
            </div>
            <div class="flex items-start gap-2 sm:gap-3">
              <div class="h-7 w-7 sm:h-8 sm:w-8 rounded-full bg-oro/20 flex items-center justify-center flex-shrink-0">
                <span class="text-xs sm:text-sm">📿</span>
              </div>
              <div>
                <p class="font-medium text-xs sm:text-sm">Grupos de oración</p>
                <p class="text-[10px] sm:text-xs text-carbon/60 hidden sm:block">Rosario y lectio divina</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
@endsection
