<?php

namespace App\Modules\Chaquetas\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Chaquetas\Support\QuoteCatalog;

/** Páginas "Chaquetas de prom en {ciudad}" para Google y los asistentes de IA. */
class CityController extends Controller
{
    public function index()
    {
        return view('chaquetas.ciudades', ['cities' => config('ciudades')]);
    }

    public function show(string $slug)
    {
        $cities = config('ciudades');
        abort_unless(isset($cities[$slug]), 404);
        $city = $cities[$slug] + ['slug' => $slug];

        $products = QuoteCatalog::products();
        $minCombo = (int) collect(QuoteCatalog::combos())->min('price');
        $minPrice = (int) collect($products)->min('price');
        $n = $city['name'];

        $faqs = collect([
            ["¿Cuánto cuesta una chaqueta de prom en {$n}?",
                'Una chaqueta beisbolera o un faz cuesta $'.number_format($products['beisbolera']['price'], 0, ',', '.').' por estudiante y una chaqueta estructurada $'.number_format($products['estructurada']['price'], 0, ',', '.').', con 5 bordados incluidos. Los combos con buso empiezan en $'.number_format($minCombo, 0, ',', '.').' por estudiante y ya traen 3% de descuento.'],
            ["¿Cuánto se demoran las chaquetas de promoción en {$n}?",
                'La producción toma de 25 a 35 días hábiles después de aprobar el diseño y las tallas.'.($city['local'] ? '' : ' A eso se suman los días del envío a '.$n.'.').' Recomendamos empezar el proceso 2 o 3 meses antes de la fecha en que las necesitan.'],
            ["¿Van al colegio en {$n} a tomar medidas?",
                $city['local']
                    ? 'Sí. En '.$n.' vamos al colegio a presentarnos con muestras y a tomar las medidas de cada estudiante. También se puede trabajar con tallas estándar (S, M, L, XL…) si lo prefieren.'
                    : 'En '.$n.' trabajamos con tallas estándar (S, M, L, XL…): te enviamos la tabla y la guía de tallas, y cada estudiante elige la suya. La presentación y los ajustes del diseño se hacen por videollamada.'],
            ['¿Cómo se paga?',
                '50% de anticipo al firmar el contrato (se firma en línea, desde el celular) y 50% al finalizar. Cada estudiante puede pagar su parte y subir el comprobante en su portal.'],
            ['¿Qué incluye el pedido?',
                'Bordados según el modelo, bandera, globos y tula o termo para la promoción, 1 chaqueta de obsequio por cada 25 unidades y 10% de descuento para hermanos o primos.'.($city['local'] ? ' En '.$n.' la entrega es en el colegio con globos y fotografía.' : '')],
        ])->map(fn ($f) => (object) ['question' => $f[0], 'answer' => $f[1]]);

        $others = collect($cities)->except($slug)->map(fn ($c, $s) => ['slug' => $s, 'name' => $c['name']])->values();

        return view('chaquetas.ciudad', compact('city', 'products', 'minCombo', 'minPrice', 'faqs', 'others'));
    }
}
