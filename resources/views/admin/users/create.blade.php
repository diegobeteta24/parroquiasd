@extends('layouts.admin')
@section('content')
<div class="max-w-2xl mx-auto space-y-6">
  <h2 class="text-xl font-semibold">Nuevo usuario</h2>
  <form action="{{ route('admin.users.store') }}" method="POST" class="space-y-4 bg-white p-6 rounded border">
    @csrf
    <div>
      <label class="block text-sm">Nombre</label>
      <input name="name" class="mt-1 w-full rounded border-gray-300" required value="{{ old('name') }}">
      @error('name')<div class="text-sm text-red-600">{{ $message }}</div>@enderror
    </div>
    <div>
      <label class="block text-sm">Email</label>
      <input type="email" name="email" class="mt-1 w-full rounded border-gray-300" required value="{{ old('email') }}">
      @error('email')<div class="text-sm text-red-600">{{ $message }}</div>@enderror
    </div>
    <div>
      <label class="block text-sm">Contraseña</label>
      <input type="password" name="password" class="mt-1 w-full rounded border-gray-300" required>
      @error('password')<div class="text-sm text-red-600">{{ $message }}</div>@enderror
    </div>
    <div>
      <label class="block text-sm mb-1">Roles</label>
      <div class="grid grid-cols-2 gap-2">
        @foreach($roles as $id => $name)
          <label class="inline-flex items-center gap-2">
            <input type="checkbox" name="roles[]" value="{{ $id }}" class="rounded border-gray-300">
            <span>{{ $name }}</span>
          </label>
        @endforeach
      </div>
      @error('roles')<div class="text-sm text-red-600">{{ $message }}</div>@enderror
    </div>
    <div class="flex justify-end gap-2">
      <a href="{{ route('admin.users.index') }}" class="px-3 py-2 rounded bg-gray-200">Cancelar</a>
      <button class="px-3 py-2 rounded bg-emerald-600 text-white">Guardar</button>
    </div>
  </form>
</div>
@endsection
