<?php

namespace Database\Seeders;

use App\Models\User;
use App\Modules\Chaquetas\Models\Contract;
use App\Modules\Chaquetas\Models\JacketType;
use App\Modules\Chaquetas\Models\School;
use App\Modules\Chaquetas\Models\Student;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        // Cuenta de acceso al portal del colegio (demo)
        $schoolUser = User::updateOrCreate(
            ['email' => 'colegio@chaquetas.test'],
            ['name' => 'Colegio Liceo Freire', 'role' => 'colegio', 'password' => Hash::make('password')]
        );

        $school = School::updateOrCreate(
            ['slug' => 'liceo-freire'],
            ['name' => 'Colegio Liceo Freire', 'city' => 'Bogotá', 'contact_name' => 'Rectoría', 'active' => true, 'user_id' => $schoolUser->id]
        );

        $varsity = JacketType::where('slug', 'varsity')->first();

        $contract = Contract::updateOrCreate(
            ['code' => 'CTR-2026-001'],
            [
                'school_id'         => $school->id,
                'jacket_type_id'    => $varsity?->id,
                'year'              => 2026,
                'production_status' => 'en_produccion',
                'price_per_unit'    => 180000,
                'contract_status'   => 'firmado',
                'terms'             => 'Contrato de confección de chaquetas de promoción 2026 para el Colegio Liceo Freire.',
            ]
        );

        $students = [
            ['document' => '1010101010', 'name' => 'Valentina Ríos', 'gender' => 'mujer', 'nickname' => 'Vale', 'size' => 'M', 'chest' => 92, 'waist' => 74, 'hip' => 96, 'sleeve_length' => 58, 'talle_length' => 62, 'payment_status' => 'pendiente'],
            ['document' => '2020202020', 'name' => 'Juan Torres',   'gender' => 'hombre', 'nickname' => 'Juancho', 'size' => 'L', 'chest' => 104, 'waist' => 88, 'hip' => 102, 'sleeve_length' => 64, 'talle_length' => 70, 'payment_status' => 'pagado'],
        ];

        foreach ($students as $s) {
            Student::updateOrCreate(
                ['contract_id' => $contract->id, 'document' => $s['document']],
                array_merge($s, ['amount' => 180000])
            );
        }
    }
}
