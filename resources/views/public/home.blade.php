@extends('layouts.public')

@section('title', 'Inicio — '.config('app.name'))

@section('content')
<section class="mt-6 grid md:grid-cols-2 gap-8 items-center">
    <div>
        <h1 class="text-3xl md:text-4xl font-bold tracking-tight">Intenciones de misa</h1>
        <p class="mt-4 text-[#F8F6F1]/80">Solicita una intención de misa o consulta su estado por folio.</p>
        <div class="mt-6 flex gap-3">
            <a href="#" class="inline-flex items-center px-5 py-3 rounded-md bg-[#BFA24E] text-[#1F2937] font-medium">Solicitar intención</a>
            <a href="{{ route('horarios') }}" class="inline-flex items-center px-5 py-3 rounded-md border border-[#BFA24E]/40">Ver horarios</a>
        </div>
    </div>
    <div class="rounded-lg border border-[#BFA24E]/30 p-6">
        <p class="text-sm text-[#F8F6F1]/70">Portada temporal. Construiremos componentes Livewire en siguientes pasos.</p>
    </div>
</section>
@endsection
