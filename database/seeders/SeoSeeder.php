<?php

namespace Database\Seeders;

use App\Modules\Chaquetas\Models\Faq;
use App\Modules\Chaquetas\Models\SeoPage;
use App\Modules\Chaquetas\Models\Setting;
use Illuminate\Database\Seeder;

class SeoSeeder extends Seeder
{
    public function run(): void
    {
        // Defaults globales
        $settings = [
            'seo_default_title'       => 'Chaquetas de Prom 2026 en Colombia · Glauren',
            'seo_default_description' => 'Diseña tu chaqueta de prom en 3D, sigue su fabricación con fotos reales y págala en línea. Más de 10 años vistiendo promociones de grado once en toda Colombia.',
            'seo_keywords'            => 'chaquetas de prom, chaquetas prom, chaquetas prom 2026, diseñar chaquetas prom online, diseños de chaquetas de prom, modelos de chaquetas de prom, logos para chaquetas de prom, chaquetas prom grado 11',
            'seo_og_image'            => 'https://chaquetasdeprom.com/wp-content/uploads/2025/11/chaqueta-de-promocion-1.jpeg',
            'social_facebook'         => 'https://www.facebook.com/glaurenztienda',
            'social_tiktok'           => 'https://www.tiktok.com/@chaquetaprom',
            'social_instagram'        => 'https://www.instagram.com/chaquetaprom_/',
            'home_seo_content'        => '<h2>Chaquetas de prom 2026 con diseño exclusivo</h2>'
                .'<p>En <strong>Glauren</strong> somos fabricantes de las mejores <strong>chaquetas de prom</strong> y busos de promoción en Colombia. Con más de 10 años de experiencia, diseñamos <strong>chaquetas de prom para grado once</strong> con materiales de alta calidad (Patprimo, Lafayette) y bordados personalizados: nombre, número, escudo y el logo de tu promoción.</p>'
                .'<p>Ahora puedes <strong>diseñar tu chaqueta de prom online</strong> en 3D, elegir colores por zona (cuerpo, mangas, pretinas y puños), agregar tus parches y pedir tu cotización. Atendemos promociones en Bogotá, Medellín, Cali, Bucaramanga, Manizales y toda Colombia.</p>'
                .'<p>Explora <strong>diseños y modelos de chaquetas de prom</strong>, sigue la fabricación con fotos reales y paga en línea de forma segura. Tu promoción, hecha a tu medida.</p>',
        ];
        foreach ($settings as $k => $v) {
            Setting::updateOrCreate(['key' => $k], ['value' => $v]);
        }

        // Meta por página (keyword-rich)
        $pages = [
            ['home', 'Inicio', 'Chaquetas de Prom 2026 en Colombia · Diseña en 3D | Glauren', 'Diseña tu chaqueta de prom en 3D, síguela con fotos reales y págala en línea. Fabricantes de chaquetas de prom para grado once en toda Colombia.', 'chaquetas de prom, chaquetas prom 2026, diseñar chaquetas prom online'],
            ['personalizar', 'Diseñador', 'Diseña tu Chaqueta de Prom Online en 3D | Glauren', 'Diseña gratis tu chaqueta de prom online en 3D: elige modelo, colores y agrega escudos y nombres. Cotiza al instante por WhatsApp.', 'diseñar chaquetas prom online gratis, diseños de chaquetas de prom, modelos de chaquetas de prom'],
            ['seguimiento', 'Seguimiento', 'Sigue tu Chaqueta de Prom · Estado y Fotos | Glauren', 'Consulta con tu tarjeta de identidad el avance de fabricación de tu chaqueta de prom, mira las fotos del proceso y tu estado de pago.', 'seguimiento chaqueta de prom, estado de fabricación'],
            ['colegios', 'Colegios', 'Chaquetas de Prom para Colegios · Contrato en Línea | Glauren', 'Portal para colegios: contrato y firma en línea, avance con fotos y lista de alumnos por tarjeta de identidad para tu promoción.', 'chaquetas de prom para colegios, contrato chaquetas de prom'],
            ['noticias', 'Noticias', 'Ideas y Diseños de Chaquetas de Prom · Blog | Glauren', 'Ideas, diseños y frases para chaquetas de prom. Inspírate para la chaqueta de tu promoción de grado once.', 'ideas de chaquetas de prom, frases para chaquetas de prom, diseños chaquetas prom'],
        ];
        foreach ($pages as [$key, $label, $title, $desc, $kw]) {
            SeoPage::updateOrCreate(['page_key' => $key], ['label' => $label, 'title' => $title, 'description' => $desc, 'keywords' => $kw]);
        }

        // FAQ (home) — apuntando a "People also ask"
        $faqs = [
            ['¿Puedo diseñar mi chaqueta de prom online?', 'Sí. Con nuestro diseñador 3D eliges el modelo, los colores por zona (cuerpo, mangas, pretinas y puños), agregas tus escudos, logos y nombres, y pides la cotización al instante.'],
            ['¿Hacen chaquetas de prom en toda Colombia?', 'Sí. Atendemos promociones de grado once en Bogotá, Medellín, Cali, Bucaramanga, Manizales y todo el país, con asesoría personalizada para tu colegio.'],
            ['¿Cuánto tarda la fabricación?', 'El tiempo de producción es de aproximadamente 35 días hábiles a partir de la firma del contrato, el primer pago y la entrega de los diseños y nombres.'],
            ['¿Qué materiales usan?', 'Trabajamos telas de alta calidad como Patprimo y Lafayette. En la asesoría te explicamos las diferencias para que elijas la mejor para tu promoción.'],
            ['¿Las chaquetas tienen garantía?', 'Sí. La garantía cubre defectos de fábrica en broches, cremalleras y costuras durante el año escolar. No cubre mal uso ni desgaste del material.'],
            ['¿Cómo sigo el proceso de mi chaqueta?', 'Con tu tarjeta de identidad ingresas a “Seguimiento” y ves el avance de fabricación etapa por etapa, con fotos reales del proceso y tu estado de pago.'],
            ['¿Puedo poner el logo o el animal de mi promoción?', 'Claro. Personalizamos escudos, logos y animales para chaquetas de prom, además del nombre y el número de cada estudiante.'],
        ];
        foreach ($faqs as $i => [$q, $a]) {
            Faq::updateOrCreate(['question' => $q], ['answer' => $a, 'page_key' => 'home', 'sort' => $i + 1, 'active' => true]);
        }
    }
}
