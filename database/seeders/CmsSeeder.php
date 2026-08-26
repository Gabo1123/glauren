<?php

namespace Database\Seeders;

use App\Modules\Chaquetas\Models\MenuItem;
use App\Modules\Chaquetas\Models\Page;
use App\Modules\Chaquetas\Models\Setting;
use Illuminate\Database\Seeder;

class CmsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            'brand_name'          => 'Glauren',
            'home_hero_eyebrow'   => 'Chaquetas de promoción · hechas a tu medida',
            'home_hero_title'     => 'Tu promoción,',
            'home_hero_accent'    => 'tejida a mano.',
            'home_hero_subtitle'  => 'Diseña tu chaqueta en 3D, apruébala con tu curso y sigue su fabricación paso a paso — con fotos reales — hasta que llega a tus manos.',
            'contact_whatsapp'    => '573246750207',
            'contact_phone'       => '3246750207',
            'contact_address'     => 'Calle 48k 5a-27 Sur, Bogotá',
            'footer_note'         => 'Glauren · Chaquetas de promoción en Colombia',
        ];
        foreach ($settings as $k => $v) {
            Setting::updateOrCreate(['key' => $k], ['value' => $v]);
        }

        $menu = [
            ['label' => 'Inicio', 'url' => '/', 'sort' => 1],
            ['label' => 'Diseñar', 'url' => '/personalizar', 'sort' => 2],
            ['label' => 'Seguimiento', 'url' => '/mi-chaqueta', 'sort' => 3],
            ['label' => 'Colegios', 'url' => '/colegios', 'sort' => 4],
            ['label' => 'Noticias', 'url' => '/noticias', 'sort' => 5],
        ];
        foreach ($menu as $m) {
            MenuItem::updateOrCreate(['label' => $m['label']], array_merge($m, ['active' => true]));
        }

        Page::updateOrCreate(
            ['slug' => 'terminos-y-servicios'],
            [
                'title'     => 'Términos y servicios',
                'published' => true,
                'body'      => '<p>Al usar el diseñador y solicitar una cotización aceptas nuestros términos y el tratamiento de tus datos para contactarte sobre tu pedido. Edita este texto desde el panel: Contenido → Páginas.</p>',
            ]
        );
    }
}
