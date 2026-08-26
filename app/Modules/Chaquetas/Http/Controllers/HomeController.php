<?php

namespace App\Modules\Chaquetas\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Chaquetas\Models\Collection;
use App\Modules\Chaquetas\Models\Faq;
use App\Modules\Chaquetas\Models\JacketType;
use App\Modules\Chaquetas\Models\Setting;

class HomeController extends Controller
{
    public function index()
    {
        $types = JacketType::where('active', true)->orderBy('sort')->get();
        $collections = Collection::where('active', true)->orderBy('sort')->get();
        $faqs = Faq::published('home');
        $seoContent = Setting::get('home_seo_content', '');

        return view('chaquetas.home', compact('types', 'collections', 'faqs', 'seoContent'));
    }

    public function colegios()
    {
        return view('chaquetas.colegios');
    }

    public function soon(string $seccion)
    {
        $titles = [
            'mi-chaqueta' => ['Busca tu chaqueta', 'Encuentra tu pedido con tu tarjeta de identidad, mira el avance de fabricación y paga en línea.'],
            'colegios'    => ['Portal para colegios', 'Cada colegio tendrá su cuenta con el contrato, el avance, las fotos del proceso y su lista de alumnos.'],
        ];
        [$title, $desc] = $titles[$seccion] ?? ['Próximamente', 'Estamos construyendo esta sección.'];

        return view('chaquetas.soon', compact('title', 'desc'));
    }
}
