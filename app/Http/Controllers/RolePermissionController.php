<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class RolePermissionController extends Controller
{
    public function show(Role $role)
    {
        return $role->permissions->pluck('name');
    }

    public function sync(Request $request, Role $role)
    {
        $data = $request->validate([
            'permissions' => 'array'
        ]);

        $role->syncPermissions($data['permissions']);

        return response()->json(['success' => true]);
    }
}
