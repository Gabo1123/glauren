<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Usuario administrador (cámbialo luego)
        User::updateOrCreate(
            ['email' => 'admin@chaquetas.test'],
            ['name' => 'Administrador', 'password' => Hash::make('password')]
        );

        $this->call([
            JacketTypeSeeder::class,
            DemoSeeder::class,
            CmsSeeder::class,
            SeoSeeder::class,
            CitiesSeeder::class,
            CollectionsSeeder::class,
        ]);
    }
}
