<?php

namespace App\Modules\Chaquetas\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Chaquetas\Models\Setting;
use Illuminate\Http\Request;

class HomeDesignController extends Controller
{
    /** Secciones reordenables del home (deben coincidir con las de home.blade.php). */
    public const SECTIONS = [
        'garantias'    => ['Garantías', '🛡️', 'Textos / Home'],
        'proceso'      => ['Proceso "De la idea a tus hombros"', '🪜', 'Textos / Home'],
        'numbers'      => ['Números + franja de colegios', '🔢', 'Textos / Home'],
        'video'        => ['Video "Míranos en acción"', '🎬', 'Textos / Home'],
        'gallery'      => ['Galería "Ya visten Glauren"', '🖼️', 'Promociones'],
        'tipos'        => ['Modelos "Elige tu estilo"', '🧥', '—'],
        'collections'  => ['Colecciones "Nuestros estilos"', '🎨', 'Colecciones'],
        'testimonios'  => ['Testimonios', '💬', 'Testimonios'],
        'colegios'     => ['Para colegios "La cuenta de tu colegio"', '🏫', '—'],
        'portales'     => ['Portales "Dos portales, toda una experiencia"', '📱', '—'],
        'faq'          => ['Preguntas frecuentes', '❓', 'SEO y FAQ'],
        'seo'          => ['Bloque de texto SEO', '📝', 'Textos / Home'],
        'cta_final'    => ['CTA final "Tu promoción a un clic"', '🚀', '—'],
    ];

    public function index()
    {
        $saved = json_decode((string) Setting::get('home_layout', ''), true);
        $order = (is_array($saved) && $saved) ? $saved : array_map(fn ($k) => ['key' => $k, 'on' => true], array_keys(self::SECTIONS));

        // Asegura que todas las secciones estén (y descarta claves desconocidas)
        $seen = array_column($order, 'key');
        foreach (array_keys(self::SECTIONS) as $k) {
            if (! in_array($k, $seen, true)) {
                $order[] = ['key' => $k, 'on' => true];
            }
        }
        $order = array_values(array_filter($order, fn ($o) => isset(self::SECTIONS[$o['key']])));

        return view('admin.cms.homedesign', ['order' => $order, 'meta' => self::SECTIONS]);
    }

    public function save(Request $request)
    {
        $items = (array) $request->input('layout', []);
        $clean = [];
        foreach ($items as $it) {
            $key = $it['key'] ?? null;
            if (isset(self::SECTIONS[$key])) {
                $clean[] = ['key' => $key, 'on' => filter_var($it['on'] ?? false, FILTER_VALIDATE_BOOL)];
            }
        }
        Setting::put('home_layout', json_encode(array_values($clean)));

        return response()->json(['ok' => true]);
    }
}
