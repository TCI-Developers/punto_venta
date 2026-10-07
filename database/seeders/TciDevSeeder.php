<?php

namespace Database\Seeders;

use App\Models\{Role, User};
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{DB, Hash};

class TciDevSeeder extends Seeder
{
    public function run(): void
    {
        $name     = env('NAME_ROOT', 'TCI_DEV');
        $email    = env('EMAIL_ROOT', 'root@tci.local');
        $password = env('PASSWORD_ROOT', 'tcidev2024');

        $user = User::firstOrCreate(
            ['name' => $name],
            [
                'email'             => $email,
                'phone'             => '',
                'password'          => Hash::make($password),
                'email_verified_at' => now(),
            ]
        );

        $rootRole = Role::firstOrCreate(
            ['name' => 'root'],
            ['description' => 'Administrador TCI']
        );

        $hasRole = DB::table('role_user')
            ->where('user_id', $user->id)
            ->where('role_id', $rootRole->id)
            ->exists();

        if (!$hasRole) {
            DB::table('role_user')->insert([
                'user_id'    => $user->id,
                'role_id'    => $rootRole->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
