<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::create([
            'biometric_id' => '999',
            'nombre' => 'Admin Principal',
            'username' => 'admin',
            'password' => \Illuminate\Support\Facades\Hash::make('istae123A*'),
            'rol' => 'admin',
            'acceso_puerta' => 1
        ]);
    }
}
