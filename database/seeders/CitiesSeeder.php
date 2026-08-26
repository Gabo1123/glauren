<?php

namespace Database\Seeders;

use App\Modules\Chaquetas\Models\Page;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CitiesSeeder extends Seeder
{
    public function run(): void
    {
        $cities = ['Bogotá', 'Medellín', 'Cali', 'Bucaramanga', 'Manizales', 'Cundinamarca'];

        foreach ($cities as $i => $city) {
            $slug = Str::slug('chaquetas de prom en '.$city);
            $body = '<p>En <strong>Glauren</strong> fabricamos las mejores <strong>chaquetas de prom en '.$city.'</strong> para la promoción de grado once. '
                .'Con más de 10 años de experiencia, creamos chaquetas y busos de promoción con bordados de alta calidad: nombre, número, escudo y el logo de tu curso.</p>'
                .'<p>Ahora puedes <strong>diseñar tu chaqueta de prom online</strong> en 3D, elegir los colores por zona (cuerpo, mangas, pretinas y puños), agregar tus parches y pedir tu cotización al instante. '
                .'Atendemos colegios de '.$city.' y alrededores con asesoría personalizada.</p>'
                .'<p>Sigue la fabricación de tus chaquetas con fotos reales del proceso y paga en línea de forma segura.</p>'
                .'<h2>¿Por qué elegir Glauren en '.$city.'?</h2>'
                .'<ul><li>Diseños exclusivos y bordados de alta calidad</li><li>Materiales premium (Patprimo, Lafayette)</li>'
                .'<li>Garantía de un año escolar por defectos de fábrica</li><li>Entrega en tiempo récord</li></ul>'
                .'<p><a href="/personalizar">Diseña tu chaqueta de prom en 3D →</a></p>';

            Page::updateOrCreate(
                ['slug' => $slug],
                ['title' => 'Chaquetas de Prom en '.$city.' 2026', 'body' => $body, 'published' => true, 'sort' => 100 + $i]
            );
        }
    }
}
