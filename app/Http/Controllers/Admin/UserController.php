<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index()
    {
        $users = User::query()->latest()->paginate(20);
        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        $roles = Role::pluck('name','id');
        return view('admin.users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required','string','max:255'],
            'email' => ['required','email','max:255','unique:users,email'],
            'password' => ['required','string','min:8'],
            'roles' => ['array'],
            'roles.*' => ['integer','exists:roles,id'],
        ]);
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
            'email_verified_at' => now(),
        ]);
        if (!empty($data['roles'])) {
            $roleNames = Role::whereIn('id', $data['roles'])->pluck('name')->all();
            $user->syncRoles($roleNames);
        }
        return redirect()->route('admin.users.index')->with('status','Usuario creado.');
    }

    public function edit(User $user)
    {
        $roles = Role::pluck('name','id');
        $assigned = $user->roles()->pluck('id')->all();
        return view('admin.users.edit', compact('user','roles','assigned'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => ['required','string','max:255'],
            'email' => ['required','email','max:255', Rule::unique('users','email')->ignore($user->id)],
            'password' => ['nullable','string','min:8'],
            'roles' => ['array'],
            'roles.*' => ['integer','exists:roles,id'],
        ]);
        $user->name = $data['name'];
        $user->email = $data['email'];
        if (!empty($data['password'])) { $user->password = bcrypt($data['password']); }
        $user->save();
        $roleNames = !empty($data['roles']) ? Role::whereIn('id', $data['roles'])->pluck('name')->all() : [];
        $user->syncRoles($roleNames);
        return redirect()->route('admin.users.index')->with('status','Usuario actualizado.');
    }

    public function destroy(User $user)
    {
        // Evitar autodestruir superadmin actual
        if (auth()->id() === $user->id) {
            return back()->withErrors(['user' => 'No puedes eliminar tu propio usuario.']);
        }
        $user->delete();
        return redirect()->route('admin.users.index')->with('status','Usuario eliminado.');
    }
}
