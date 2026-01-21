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
            /* PERMISOS PARA CREACIÓN DE USUARIOS */
            'usuario.ver',
            'usuario.crear',
            'usuario.editar',
            'usuario.borrar',
            'usuario.reporte',
            /* PERMISOS PARA ASIGNACIÓN DE PERMISOS Y CREACIÓN DE ROLES  */
            'rol.ver',
            'rol.crear',
            'rol.editar',
            'rol.eliminar',
            'rol.permisos',
            'rol.reporte',
            /* PERMISOS PARA TIPO DE PAPELES */
            'tipo_papel.ver',
            'tipo_papel.crear',
            'tipo_papel.editar',
            'tipo_papel.borrar',
            'tipo_papel.reporte',
            /* PERMISOS PARA TIPO DE AGARRADOR */
            'agarrador.ver',
            'agarrador.crear',
            'agarrador.editar',
            'agarrador.borrar',
            'agarrador.reporte',
            /* PERMISOS PARA PÁGINAS */
            'pagina.ver',
            'pagina.crear',
            'pagina.editar',
            'pagina.borrar',
            'pagina.reporte',
            /* PERMISOS PARA BANCOS */
            'banco.ver',
            'banco.crear',
            'banco.editar',
            'banco.borrar',
            'banco.reporte',
            /* PERMISOS PARA PRODUCTOS */
            'producto.ver',
            'producto.crear',
            'producto.editar',
            'producto.borrar',
            'producto.reporte',
            /* PERMISOS PARA CLIENTES */
            'cliente.ver',
            'cliente.crear',
            'cliente.editar',
            'cliente.borrar',
            'cliente.reporte',
            /* PERMISOS PARA VENTAS */
            'venta.ver',
            'venta.crear',
            'venta.editar',
            'venta.borrar',
            'venta.reporte',
            /* PERMISOS PARA ESTADOS */
            'estado.ver',
            'estado.crear',
            'estado.editar',
            'estado.borrar',
            'estado.reporte',
            /* PERMISOS PARA SUBESTADOS */
            'subestado.ver',
            'subestado.crear',
            'subestado.editar',
            'subestado.borrar',
            'subestado.reporte',
            /* PERMISOS PARA SIDEBAR */
            'menu.inicio',
            'menu.usuarios',
            'menu.permisos',
            'menu.tipo_papel',
            'menu.agarrador',
            'menu.pagina',
            'menu.banco',
            'menu.producto',
            'menu.cliente',
            'menu.venta',
            'menu.estado',
            'menu.subestado',
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

        $produccion = Role::firstOrCreate([
            'name' => 'Producción',
            'guard_name' => 'web',
        ]);


        $adminRole->givePermissionTo(Permission::all());
        $asesorRole->givePermissionTo([
            'usuario.ver',
            'menu.inicio',
        ]);

        $produccion->givePermissionTo([
            'usuario.ver',
            'menu.inicio',
        ]);
    }
}
