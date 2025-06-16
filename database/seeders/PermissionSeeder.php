<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run()
    {
        // Define los guards de tu aplicación
        $clienteGuard = 'cliente_api';
        $recepcionistaGuard = 'recepcionista_api';
        $asistenteGuard = 'asistente_api';
        $especialistaGuard = 'especialista_api';

        // --- Permisos y roles para Cliente ---
        $clientePermissions = [
            'ver_pedidos_propios',
            'registrar_pedido_propio',
            'buscar_pedido_propio',
            'modificar_pedido_propio',
            'eliminar_pedido_propio',
            'ver_citas_propias',
            'registrar_cita_propia',
            'buscar_cita_propia',
            'modificar_cita_propia',
            'eliminar_cita_propia',
            'ver_info_empresa',
        ];
        $clienteRole = Role::firstOrCreate(['name' => 'cliente', 'guard_name' => $clienteGuard]);
        foreach ($clientePermissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => $clienteGuard]);
        }
        $clienteRole->syncPermissions(Permission::where('guard_name', $clienteGuard)->pluck('name')->toArray());

        // --- Permisos y roles para Recepcionista ---
        $recepcionistaPermissions = [
            'ver_todas_citas',
            'registrar_cita',
            'buscar_cita',
            'modificar_cita',
            'eliminar_cita',
            'ver_todos_clientes',
            'registrar_cliente',
            'buscar_cliente',
            'modificar_cliente',
            'eliminar_cliente',
            'ver_todos_servicios',
            'registrar_servicio',
            'buscar_servicio',
            'modificar_servicio',
            'eliminar_servicio'
        ];
        $recepcionistaRole = Role::firstOrCreate(['name' => 'recepcionista', 'guard_name' => $recepcionistaGuard]);
        foreach ($recepcionistaPermissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => $recepcionistaGuard]);
        }
        $recepcionistaRole->syncPermissions(Permission::where('guard_name', $recepcionistaGuard)->pluck('name')->toArray());

        // Permisos y roles para Asistente de Ventas
        $asistenteGuard = 'asistente_api';
        $asistentePermissions = [
            'ver_todos_pedidos',
            'registrar_pedido',
            'buscar_pedido',
            'modificar_pedido',
            'eliminar_pedido',
            'ver_todos_productos',
            'registrar_producto',
            'buscar_producto',
            'modificar_producto',
            'eliminar_producto',
        ];
        $asistenteRole = Role::firstOrCreate(['name' => 'asistente_ventas', 'guard_name' => $asistenteGuard]);
        foreach ($asistentePermissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => $asistenteGuard]);
        }
        $asistenteRole->syncPermissions(Permission::where('guard_name', $asistenteGuard)->pluck('name')->toArray());

        // Permisos y roles para Especialista
        $especialistaGuard = 'especialista_api';
        $especialistaPermissions = [
            'registrar_informe_propio',
            'buscar_informe_propio',
            'modificar_informe_propio',
            'eliminar_informe_propio',
            // Puedes añadir permisos específicos como 'ver_citas_asignadas', etc.
        ];
        $especialistaRole = Role::firstOrCreate(['name' => 'especialista', 'guard_name' => $especialistaGuard]);
        foreach ($especialistaPermissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => $especialistaGuard]);
        }
        $especialistaRole->syncPermissions(Permission::where('guard_name', $especialistaGuard)->pluck('name')->toArray());
    }
}
