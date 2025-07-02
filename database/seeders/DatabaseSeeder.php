<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\{Module, Permission, PermissionRole, Role};

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {  
        $modules = [
            ['name' => 'ventas',      'description' => 'Acceso a ventas',      'status' => 1],
            ['name' => 'inventarios', 'description' => 'Gestión de productos','status' => 1],
            ['name' => 'clientes',    'description' => 'Administrar clientes', 'status' => 1],
            ['name' => 'proveedores', 'description' => 'Administrar de proveedores','status' => 1],
            ['name' => 'compras',    'description' => 'Acceso a compras',  'status' => 1],
            ['name' => 'cuentas_por_pagar',    'description' => 'Acceso a cuentas por pagar',  'status' => 1],
            ['name' => 'devoluciones',    'description' => 'Acceso a devoluciones',  'status' => 1],
            ['name' => 'usuarios',    'description' => 'Gestion de usuarios',  'status' => 1],
            ['name' => 'sucursales',    'description' => 'Acceso a sucursales',  'status' => 1],
            ['name' => 'roles',    'description' => 'Acceso a roles',  'status' => 1],
            ['name' => 'empresa',    'description' => 'Acceso a datos empresa',  'status' => 1],
            ['name' => 'cierre_caja',    'description' => 'Acceso a datos cierre de caja',  'status' => 1],
            ['name' => 'listado_cierre_caja',    'description' => 'Acceso a listado de cierres de caja',  'status' => 1],
            ['name' => 'turnos',    'description' => 'Acceso a turnos',  'status' => 1],
        ]; 

        $actions = ['create', 'show', 'update', 'destroy', 'auth']; //acciones de permisos
        $actions_ = ['crear', 'lectura', 'actualizar', 'eliminar', 'auth']; //acciones de permisos

        foreach ($modules as $data) {
            Module::updateOrCreate(
                ['name' => $data['name']], // condición para evitar duplicados
                $data                      // datos a insertar o actualizar
            );

            foreach($actions as $index => $item){
                Permission::updateOrCreate(
                    [
                        'module'    => $data['name'],
                        'submodule' => 'punto_venta',
                        'action'    => $item,
                    ],
                    [
                        'description' => "Permiso para {$actions_[$index]} en {$data['name']}",
                    ]
                );
            }
        }

        $permissions = Permission::get();
        $role = Role::where('name', 'root')->first();
        if(is_object($role)){
            foreach($permissions as $item){
                PermissionRole::updateOrCreate(
                    [
                        'role_id' => $role->id,
                        'permission_id' => $item->id,
                    ]
                );
            }
        }
    }
}
