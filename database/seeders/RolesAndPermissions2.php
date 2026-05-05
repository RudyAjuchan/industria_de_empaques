<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\Models\Permission;
use App\Models\Role;

class RolesAndPermissions2 extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permisos = [
            /* PERMISOS PARA CREACIÓN DE PAGOS */
            'pago.ver',
            'pago.crear',
            'pago.editar',
            'pago.borrar',
            'pago.reporte',
            /* PERMISOS PARA SIDEBAR */
            'menu.pagos',
            'menu.contabilidad',
        ];

        foreach ($permisos as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }

        $admin = Role::where('name', 'Admin')->first();

        if ($admin) {
            $admin->givePermissionTo($permisos);
        }
    }
}
