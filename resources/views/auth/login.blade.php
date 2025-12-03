@extends('layouts.auth-minimal')
@section('content')
<div class="bg-white/95 backdrop-blur rounded-3xl shadow-2xl border border-white/30 p-10 text-gray-800">
    <div class="text-center mb-8">
        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-indigo-100 text-indigo-600 mb-4">
            <x-authentication-card-logo class="w-10 h-10" />
        </div>
        <p class="text-sm uppercase tracking-[0.3em] text-indigo-500 font-semibold">Acceso privado</p>
        <h1 class="text-2xl font-bold text-gray-900 mt-2">Panel Parroquia Santo Domingo</h1>
        <p class="text-gray-500 mt-2">Ingresa con tu usuario para administrar misas e intenciones.</p>
    </div>

    <x-validation-errors class="mb-4" />

    @session('status')
        <div class="mb-4 font-medium text-sm text-emerald-600">
            {{ $value }}
        </div>
    @endsession

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <div>
            <x-label for="email" value="{{ __('Email institucional') }}" class="text-sm font-semibold text-gray-700" />
            <x-input id="email" class="block mt-2 w-full rounded-2xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
        </div>

        <div>
            <div class="flex items-center justify-between">
                <x-label for="password" value="{{ __('Contraseña') }}" class="text-sm font-semibold text-gray-700" />
                @if (Route::has('password.request'))
                    <a class="text-sm font-medium text-indigo-600 hover:text-indigo-500" href="{{ route('password.request') }}">
                        {{ __('¿Olvidaste tu contraseña?') }}
                    </a>
                @endif
            </div>
            <x-input id="password" class="block mt-2 w-full rounded-2xl border-gray-200 focus:border-indigo-500 focus:ring-indigo-500" type="password" name="password" required autocomplete="current-password" />
        </div>

        <label class="flex items-center gap-2 text-sm text-gray-600">
            <x-checkbox id="remember_me" name="remember" />
            <span>Recordarme en este dispositivo</span>
        </label>

        <x-button class="w-full justify-center rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-base py-3">
            {{ __('Ingresar al panel') }}
        </x-button>
    </form>

    <div class="mt-8 text-center text-xs text-gray-400">
        ¿Necesitas ayuda? Escríbenos a <a href="mailto:soporte@parroquiasantodomingo.gt" class="text-indigo-500 font-medium">soporte@parroquiasantodomingo.gt</a>
    </div>
</div>
@endsection
