<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\{Role, Customer, PresentationProduct};

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Crear el rol
        $role = Role::create([
            'name' => 'root',
            'description' => 'acceso maestro',
            'status' => 1,
        ]);

        // Asignar el rol al usuario en la tabla users_role
        DB::table('role_user')->insert([
            'user_id' => 1,
            'role_id' => $role->id,
        ]);

        // Crear el cliente
        $role = Customer::create([
            'name' => 'PUBLICO EN GENERAL',
            'razon_social' => '',
            'rfc' => '',
            'postal_code' => '',
            'regimen_fiscal' => '',
            'status' => 1,
        ]);

    }
}
