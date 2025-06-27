<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Module;

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
        ];

        foreach ($modules as $data) {
            Module::updateOrCreate(
                ['name' => $data['name']], // condición para evitar duplicados
                $data                      // datos a insertar o actualizar
            );
        }
    }
}
