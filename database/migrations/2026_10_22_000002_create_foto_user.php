<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    public function up(): void
    {
        $email = 'foto@glauren.co';
        if (! DB::table('users')->where('email', $email)->exists()) {
            DB::table('users')->insert([
                'name'       => 'Fotografía de Grados',
                'email'      => $email,
                'password'   => Hash::make('foto1234'),  // TEMPORAL: cambiar tras el primer ingreso
                'role'       => 'foto',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('users')->where('email', 'foto@glauren.co')->where('role', 'foto')->delete();
    }
};
