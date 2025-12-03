@extends('layouts.public')

@section('title', 'Horarios — '.config('app.name'))
@section('content')
<h1 class="text-3xl font-bold">Horarios</h1>
<p class="mt-4 text-[#F8F6F1]/80">Consulta los horarios de misa. (Contenido temporal)</p>
<ul class="mt-6 space-y-2 text-sm">
	<li>Diaria: 06:30</li>
	<li>Domingo: 08:00 y 11:00</li>
	<li>Miércoles (votiva): 18:00</li>
 </ul>
@endsection
