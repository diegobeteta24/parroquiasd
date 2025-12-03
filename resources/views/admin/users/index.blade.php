@extends('layouts.admin')
@section('content')
<div class="max-w-5xl mx-auto space-y-6">
  <div class="flex items-center justify-between">
    <h2 class="text-xl font-semibold">Usuarios</h2>
    <a href="{{ route('admin.users.create') }}" class="px-3 py-2 rounded bg-emerald-600 text-white hover:bg-emerald-700">Nuevo</a>
  </div>
  @if (session('status'))
    <div class="p-3 rounded bg-emerald-50 text-emerald-700">{{ session('status') }}</div>
  @endif
  <div class="overflow-x-auto bg-white rounded border">
    <table class="min-w-full text-sm">
      <thead class="bg-gray-50">
        <tr>
          <th class="px-3 py-2 text-left">Nombre</th>
          <th class="px-3 py-2 text-left">Email</th>
          <th class="px-3 py-2 text-left">Roles</th>
          <th class="px-3 py-2 text-right">Acciones</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($users as $u)
        <tr class="border-t">
          <td class="px-3 py-2">{{ $u->name }}</td>
          <td class="px-3 py-2">{{ $u->email }}</td>
          <td class="px-3 py-2">{{ $u->getRoleNames()->join(', ') }}</td>
          <td class="px-3 py-2 text-right space-x-2">
            <a href="{{ route('admin.users.edit',$u) }}" class="px-2 py-1 rounded bg-indigo-600 text-white">Editar</a>
            <form action="{{ route('admin.users.destroy',$u) }}" method="POST" class="inline" onsubmit="return confirm('¿Eliminar usuario?')">
              @csrf @method('DELETE')
              <button class="px-2 py-1 rounded bg-red-600 text-white">Eliminar</button>
            </form>
          </td>
        </tr>
        @empty
        <tr><td class="px-3 py-6 text-gray-500" colspan="4">Sin usuarios.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div>{{ $users->links() }}</div>
  <p class="text-xs text-gray-500">Solo visible para superadmin.</p>
  </div>
@endsection
