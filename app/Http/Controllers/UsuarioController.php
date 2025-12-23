<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UsuarioController extends Controller
{
    public function index(Request $request)
    {
        $users = User::query()
            ->with('roles:name')
            ->where('estado', 1)
            ->orderBy('name')
            ->get()
            ->map(function ($u) {
                return [
                    'id' => $u->id,
                    'name' => $u->name,
                    'email' => $u->email,
                    'status' => $u->active ? 'active' : 'inactive',
                    'roles' => $u->roles->pluck('name')->values(),
                ];
            });

        return response()->json($users);
    }

    public function roles()
    {
        return response()->json(
            Role::query()->orderBy('name')->pluck('name')->values()
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['nullable', 'string', 'exists:roles,name'],
            'active' => ['nullable', 'boolean'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            // Si no tienes active, quita esto
            'active' => $validated['active'] ?? true,
            'force_password_change' => true,
        ]);

        if (!empty($validated['role'])) {
            $user->syncRoles([$validated['role']]); // Spatie
        }

        return response()->json(['message' => 'Usuario creado'], 201);
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'password' => ['nullable', 'string', 'min:8'],
            'role' => ['nullable', 'string', 'exists:roles,name'],
            'active' => ['nullable', 'boolean'],
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];

        // password solo si viene
        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
            $user->force_password_change = true;
        }

        // active solo si existe el campo
        if (array_key_exists('active', $validated)) {
            $user->active = (bool) $validated['active'];
        }

        $user->save();

        // rol (si viene null, puedes decidir si borrar roles o no)
        if (array_key_exists('role', $validated)) {
            if (!empty($validated['role'])) {
                $user->syncRoles([$validated['role']]);
            } else {
                $user->syncRoles([]); // sin rol
            }
        }

        return response()->json(['message' => 'Usuario actualizado']);
    }

    public function destroy(User $user)
    {
        // Evitar borrar tu propio usuario (opcional)
        if (auth()->id() === $user->id) {
            return response()->json(['message' => 'No puedes eliminar tu propio usuario'], 422);
        }

        $user->estado = 0;
        $user->updated_at = now();
        $user->save();

        return response()->json(['message' => 'Usuario eliminado']);
    }
}
