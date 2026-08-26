<?php

namespace Database\Seeders;

use App\Modules\Chaquetas\Models\JacketType;
use Illuminate\Database\Seeder;

class JacketTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            [
                'name' => 'Varsity', 'slug' => 'varsity', 'icon' => '🧥',
                'description' => 'La clásica de promoción, con mangas de cuero.',
                'model_path' => 'modelos/varsity.glb',
                'base_price' => 120000, 'patch_price' => 8000, 'active' => true, 'sort' => 1,
            ],
            [
                'name' => 'Hoodie / Buzo', 'slug' => 'hoodie', 'icon' => '👕',
                'description' => 'Con capucha, cómodo y juvenil.',
                'model_path' => null,
                'base_price' => 95000, 'patch_price' => 8000, 'active' => true, 'sort' => 2,
            ],
            [
                'name' => 'Chaqueta estructurada', 'slug' => 'estructurada', 'icon' => '🥼',
                'description' => 'Formal y entallada.',
                'model_path' => null,
                'base_price' => 150000, 'patch_price' => 8000, 'active' => true, 'sort' => 3,
            ],
        ];

        foreach ($types as $t) {
            JacketType::updateOrCreate(['slug' => $t['slug']], $t);
        }
    }
}
