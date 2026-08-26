<?php

namespace Database\Seeders;

use App\Modules\Chaquetas\Models\Collection;
use Illuminate\Database\Seeder;

class CollectionsSeeder extends Seeder
{
    public function run(): void
    {
        // Fotos reales de chaquetasdeprom.com (sitio del usuario)
        $u = 'https://chaquetasdeprom.com/wp-content/uploads/';

        $items = [
            [
                'Beisbolera Paño',
                'La clásica beisbolera en paño: cuerpo en lana y mangas en cuero sintético. Estructura firme y acabados premium, 100% personalizable.',
                $u.'2022/08/Beisbolera-de-pano.jpeg',
                [$u.'2022/08/Beisbolera-de-pano.jpeg', $u.'2022/08/Chaquetas-beisboleras.jpeg', $u.'2019/08/Chaqueta-tipo-beisbolera-para-prom.jpg'],
            ],
            [
                'Beisbolera Algodón Perchado',
                'Suave, liviana y cómoda, en algodón perchado por dentro. Ideal para climas templados sin perder el estilo varsity.',
                $u.'2022/08/Chaquetas-beisboleras.jpeg',
                [$u.'2022/08/Chaquetas-beisboleras.jpeg', $u.'2022/08/beisboleras-de-prom-para-once.jpeg', $u.'2019/08/Chaquetas-beisbolera-de-mujer.jpg'],
            ],
            [
                'Beisbolera Impermeable',
                'Resistente al agua y al viento, perfecta para ciudades frías y lluviosas. Mantiene el diseño varsity con protección extra.',
                $u.'2022/08/beisboleras-de-prom-para-once.jpeg',
                [$u.'2022/08/beisboleras-de-prom-para-once.jpeg', $u.'2022/08/Beisbolera-de-pano.jpeg'],
            ],
            [
                'Chaqueta Estructurada',
                'Corte moderno y estructurado con caída impecable. Un paso más elegante para promociones que buscan algo distinto.',
                $u.'2019/08/Elegantes-chaquetas-para-promocion.jpg',
                [$u.'2019/08/Elegantes-chaquetas-para-promocion.jpg', $u.'2019/08/Chaquetas-de-promocion-para-hombres.jpg', $u.'2019/08/Chaqueta-once-2020.jpg'],
            ],
            [
                'Chaqueta de Cortes',
                'Diseño con cortes y bloques de color que resaltan tu escudo. Máxima personalización para un estilo único.',
                $u.'2022/08/chaqueta-con-cortes.jpeg',
                [$u.'2022/08/chaqueta-con-cortes.jpeg', $u.'2022/08/chaquetas-de-once-con-cortes.jpeg'],
            ],
            [
                'Hoodies',
                'La comodidad de un buzo con capota, con el sello de tu promoción bordado o estampado. Un básico que todos van a querer usar.',
                $u.'2025/11/Buso-de-Prom.png',
                [$u.'2025/11/Buso-de-Prom.png', $u.'2022/08/Buso-de-prom.jpeg', $u.'2022/08/buzos-para-prom.jpeg'],
            ],
            [
                'Camisa Polo',
                'Complementa tu chaqueta con la polo oficial de la promoción. Tela fresca, bordado personalizado y ajuste cómodo.',
                '/storage/colecciones/polo-prom.png',
                ['/storage/colecciones/polo-prom.png'],
            ],
        ];

        foreach ($items as $i => [$title, $desc, $cover, $gallery]) {
            $c = Collection::updateOrCreate(
                ['title' => $title],
                ['image_path' => $cover, 'description' => $desc, 'link' => null, 'sort' => $i + 1, 'active' => true]
            );

            $c->images()->delete();
            foreach (array_values(array_unique($gallery)) as $s => $g) {
                $c->images()->create(['path' => $g, 'sort' => $s]);
            }
        }
    }
}
