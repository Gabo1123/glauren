<?php

namespace Database\Seeders;

use App\Modules\Chaquetas\Models\ShowcasePhoto;
use Illuminate\Database\Seeder;

class ShowcaseSeeder extends Seeder
{
    public function run(): void
    {
        // Fotos reales de promociones/colegios (chaquetasdeprom.com) vía shortpixel optimizado
        $sp = 'https://sp-ao.shortpixel.ai/client/to_auto,q_glossy,ret_img,w_1024/https://chaquetasdeprom.com/wp-content/uploads/';

        $items = [
            [$sp.'2025/03/Colegio-universidad-catolica-1.jpg', 'Universidad Católica'],
            [$sp.'2025/03/Colegio-La-Giralda-Prom-2025-1.jpg', 'Colegio La Giralda'],
            [$sp.'2025/11/Grupo-de-Jovenes-en-Bogota-con-chaquetas-de-prom-e1764182590197.jpeg', 'Bogotá · 2025'],
            [$sp.'2025/01/Grupo-de-estudiantes-con-chaquetas-azules-de-prom-con-globos-metalicos.jpg', 'Promoción azul'],
            [$sp.'2025/01/Grupo-de-estudiantes-con-chaquetas-negras.jpg', 'Chaquetas negras'],
            [$sp.'2025/01/Estudiantes-con-chaquetas-de-prom-bordo-con-blanco-sosteniendo-globos.jpg', 'Bordó & blanco'],
            [$sp.'2023/09/Generacion-Enoc-2024-Chaqueta-de-prom.jpg', 'Generación Enoc'],
            [$sp.'2025/01/Grupo-de-estudiantes-con-chaquetas-de-prom.jpg', 'Promoción negra'],
            [$sp.'2023/09/Prom-2024.jpg', 'Prom 2024'],
            [$sp.'2023/09/Beisboleras-Originales.jpg', 'Beisboleras originales'],
            [$sp.'2023/09/Colegio-George-Williams.jpg', 'Colegio George Williams'],
            [$sp.'2023/09/Montreal-Medellin-Bogota-Colombia.jpg', 'Montreal'],
            [$sp.'2025/03/chaqueta-prom-madrid-cundinamarca.jpg', 'Madrid, Cundinamarca'],
            [$sp.'2023/09/Chaqueta-de-prom-para-el-2024-y-2025.jpg', 'Prom 2024–2025'],
            [$sp.'2025/11/IMG_7393-e1764181958999.jpeg', 'Busos de prom'],
        ];

        // Reemplaza el set por el completo y organizado
        ShowcasePhoto::query()->delete();
        foreach ($items as $i => [$url, $label]) {
            ShowcasePhoto::create(['image_path' => $url, 'label' => $label, 'sort' => $i + 1, 'active' => true]);
        }
    }
}
