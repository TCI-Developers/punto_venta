<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\{Module, Permission, PermissionRole, Role};

return new class extends Migration
{
    // El modulo/permisos de "facturas" solo vivian en DatabaseSeeder, que no corre solo en
    // instalaciones ya desplegadas (solo `migrate --force` corre en cada arranque de la app
    // nativa). Si nadie ejecuto `db:seed` a mano en una sucursal, la tarjeta "Facturas" nunca
    // aparece en la pantalla de Roles -- mismo problema ya resuelto para el modulo de gastos.
    public function up(): void
    {
        Module::updateOrCreate(
            ['name' => 'facturas'],
            ['name' => 'facturas', 'description' => 'Acceso a facturación CFDI', 'status' => 1]
        );

        // facturas no usa update ni auth (un CFDI ya timbrado es inmutable)
        $actions = ['create' => 'crear', 'show' => 'lectura', 'destroy' => 'eliminar'];

        $root = Role::where('name', 'root')->first();

        foreach ($actions as $action => $label) {
            $permission = Permission::updateOrCreate(
                ['module' => 'facturas', 'submodule' => 'punto_venta', 'action' => $action],
                ['description' => "Permiso para {$label} en facturas"]
            );

            // root siempre tiene todos los permisos, igual que hace DatabaseSeeder. El resto de
            // los roles NO se tocan aqui -- se asignan a mano desde la pantalla de Roles, como
            // cualquier otro permiso nuevo.
            if ($root) {
                PermissionRole::updateOrCreate([
                    'role_id' => $root->id,
                    'permission_id' => $permission->id,
                ]);
            }
        }
    }

    public function down(): void
    {
        $permissionIds = Permission::where('module', 'facturas')->pluck('id');
        PermissionRole::whereIn('permission_id', $permissionIds)->delete();
        Permission::where('module', 'facturas')->delete();
        Module::where('name', 'facturas')->delete();
    }
};
