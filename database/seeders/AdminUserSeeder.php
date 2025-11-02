<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('ev_users')->insert([
            'name' => 'Administrador',
            'email' => 'julian.g14@Hotmail.com',
            'password' => Hash::make('Bebe2025.'), // 🔑 cámbialo luego en producción
            'role_id' => 1, // asumiendo que en ev_roles el 1 es "admin"
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
