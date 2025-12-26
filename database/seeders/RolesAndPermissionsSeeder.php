<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permisos = [
            'usuario.ver',
            'usuario.crear',
            'usuario.editar',
            'usuario.borrar',
            'usuario.reporte',

            'rol.ver',
            'rol.crear',
            'rol.editar',
            'rol.eliminar',
            'rol.permisos',
            'rol.reporte',
            /* PERMISOS PARA SIDEBAR */
            'menu.inicio',
            'menu.usuarios',
            'menu.permisos',
        ];

        foreach ($permisos as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }

        $adminRole = Role::firstOrCreate([
            'name' => 'Admin',
            'guard_name' => 'web',
        ]);

        $asesorRole = Role::firstOrCreate([
            'name' => 'Asesor',
            'guard_name' => 'web',
        ]);


        $adminRole->givePermissionTo(Permission::all());
        $asesorRole->givePermissionTo([
            'usuario.ver',
            'menu.inicio',
        ]);
    }
}
