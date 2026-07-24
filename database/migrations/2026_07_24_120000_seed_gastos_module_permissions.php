<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\{Module, Permission, PermissionRole, Role};

return new class extends Migration
{
    // El seeding de modulos/permisos normalmente vive en DatabaseSeeder, pero ese seeder no
    // corre automaticamente en las sucursales ya desplegadas (solo `migrate --force` corre en
    // cada arranque de la app nativa). Para que el modulo de gastos quede disponible sin un
    // paso manual por sucursal, se crea aqui y se asigna de una vez a TODOS los roles
    // existentes -- el gasto de caja debe estar disponible para cualquier usuario con turno
    // abierto, no solo root/admin.
    public function up(): void
    {
        Module::updateOrCreate(
            ['name' => 'gastos'],
            ['name' => 'gastos', 'description' => 'Registro de gastos de caja', 'status' => 1]
        );

        $actions = ['create' => 'crear', 'show' => 'lectura', 'destroy' => 'eliminar'];
        $roles = Role::all();

        foreach ($actions as $action => $label) {
            $permission = Permission::updateOrCreate(
                ['module' => 'gastos', 'submodule' => 'punto_venta', 'action' => $action],
                ['description' => "Permiso para {$label} en gastos"]
            );

            foreach ($roles as $role) {
                PermissionRole::updateOrCreate([
                    'role_id' => $role->id,
                    'permission_id' => $permission->id,
                ]);
            }
        }
    }

    public function down(): void
    {
        $permissionIds = Permission::where('module', 'gastos')->pluck('id');
        PermissionRole::whereIn('permission_id', $permissionIds)->delete();
        Permission::where('module', 'gastos')->delete();
        Module::where('name', 'gastos')->delete();
    }
};
