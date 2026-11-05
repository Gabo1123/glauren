<?php

namespace App\Modules\Chaquetas\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Chaquetas\Models\JacketType;
use App\Modules\Chaquetas\Models\Page;
use App\Modules\Chaquetas\Models\Post;

class SitemapController extends Controller
{
    public function index()
    {
        $urls = [
            ['loc' => route('home'), 'priority' => '1.0', 'freq' => 'weekly'],
            ['loc' => route('personalizar'), 'priority' => '0.9', 'freq' => 'weekly'],
            ['loc' => route('news.index'), 'priority' => '0.7', 'freq' => 'weekly'],
            ['loc' => route('seguimiento'), 'priority' => '0.5', 'freq' => 'monthly'],
            ['loc' => route('empresas'), 'priority' => '0.8', 'freq' => 'monthly'],
            ['loc' => route('colegios'), 'priority' => '0.6', 'freq' => 'monthly'],
            ['loc' => route('ciudades.index'), 'priority' => '0.8', 'freq' => 'monthly'],
        ];
        foreach (array_keys(config('ciudades')) as $slug) {
            $urls[] = ['loc' => route('ciudades.show', $slug), 'priority' => '0.8', 'freq' => 'monthly'];
        }

        foreach (JacketType::where('active', true)->get() as $t) {
            $urls[] = ['loc' => route('design', $t->slug), 'priority' => '0.8', 'freq' => 'monthly'];
        }
        foreach (Page::where('published', true)->orderBy('sort')->get() as $p) {
            $urls[] = ['loc' => route('page.show', $p->slug), 'priority' => '0.7', 'freq' => 'monthly', 'lastmod' => $p->updated_at];
        }
        foreach (Post::published()->get() as $po) {
            $urls[] = ['loc' => route('news.show', $po->slug), 'priority' => '0.6', 'freq' => 'monthly', 'lastmod' => $po->updated_at];
        }

        return response()->view('sitemap', compact('urls'))->header('Content-Type', 'application/xml');
    }

    public function robots()
    {
        // Nota: NO listamos la URL del panel aquí (eso la expondría). El panel se protege
        // con login + una URL privada. Aquí solo evitamos indexar zonas privadas/funcionales.
        $lines = [
            'User-agent: *',
            'Allow: /',
            // Zonas privadas o funcionales (no deben aparecer en Google)
            'Disallow: /colegio',        // portal de colegios (login/privado)
            'Disallow: /mi-chaqueta',    // seguimiento personal de cada estudiante
            'Disallow: /pagar',          // pasarela de pago
            'Disallow: /pago',           // retorno de pago
            'Disallow: /contacto/horarios', // endpoint interno de agenda
            'Disallow: /storage/comprobantes', // comprobantes de pago subidos
            'Disallow: /cotizacion/',     // cotizaciones privadas de cada cliente
            'Disallow: /encuesta/',       // encuestas firmadas
            'Disallow: /*?*',            // URLs con parámetros (evita contenido duplicado)
            '',
            // Buscadores de IA: SÍ los dejamos leer para que recomienden la marca
            // (ChatGPT, Perplexity, Claude, Gemini/Google, Apple, Common Crawl).
            'User-agent: GPTBot',
            'User-agent: OAI-SearchBot',
            'User-agent: ChatGPT-User',
            'User-agent: PerplexityBot',
            'User-agent: ClaudeBot',
            'User-agent: Claude-SearchBot',
            'User-agent: Google-Extended',
            'User-agent: Applebot-Extended',
            'User-agent: CCBot',
            'Allow: /',
            'Disallow: /colegio',
            'Disallow: /mi-chaqueta',
            'Disallow: /pagar',
            'Disallow: /pago',
            'Disallow: /cotizacion/',
            'Disallow: /storage/comprobantes',
            '',
            '# Resumen para asistentes de IA: '.url('/llms.txt'),
            'Sitemap: '.url('/sitemap.xml'),
            '',
        ];

        return response(implode("\n", $lines))->header('Content-Type', 'text/plain');
    }

    /** Resumen de la marca para asistentes de IA (estándar llms.txt). */
    public function llms()
    {
        $p = \App\Modules\Chaquetas\Support\QuoteCatalog::products();
        $combos = \App\Modules\Chaquetas\Support\QuoteCatalog::combos();
        $money = fn ($v) => '$'.number_format((float) $v, 0, ',', '.');
        $brand = \App\Modules\Chaquetas\Support\Brand::name();
        $wa = preg_replace('/\D/', '', \App\Modules\Chaquetas\Models\Setting::get('contact_whatsapp', '573021106592'));
        $minCombo = collect($combos)->min('price');

        $lines = [
            "# {$brand} — Chaquetas de prom y busos de promoción en Colombia",
            '',
            "> {$brand} (también escrito Glauren) diseña y fabrica chaquetas de prom, busos, polos y camisetas de promoción para estudiantes de grado once y universidades en toda Colombia. Taller propio en Bogotá, más de 10 años de experiencia, contrato formal con firma en línea, portal para seguir la producción con fotos reales y pago 50/50.",
            '',
            '## Datos clave',
            '- Sitio web: '.url('/'),
            '- WhatsApp: +'.$wa.' (https://wa.me/'.$wa.')',
            '- Dirección del taller: '.\App\Modules\Chaquetas\Models\Setting::get('contact_address', 'Calle 48k 5a-27 Sur, Bogotá').', Colombia',
            '- Cobertura: toda Colombia. En Bogotá y Cundinamarca vamos al colegio a presentarnos y a tomar medidas; en otras ciudades hacemos reunión virtual y se usan tallas estándar (S, M, L, XL…) con tabla y guía de tallas, y enviamos el pedido.',
            '- Tiempo de entrega: 25 a 35 días hábiles después de aprobar el diseño y las tallas.',
            '- Pagos: 50% de anticipo al firmar el contrato y 50% contra entrega, por transferencia.',
            '- Garantía: defectos de fabricación (confección y cremalleras) durante el año escolar.',
            '- Telas: Lafayette (y Patprimo/lacoste en polos); cremalleras YES; bordados incluidos.',
            '',
            '## Precios por estudiante (COP, '.now()->year.')',
        ];
        foreach ($p as $item) {
            $lines[] = '- '.$item['name'].': '.$money($item['price']).' ('.$item['embroidery'].' bordados, tela '.$item['fabric'].')';
        }
        $lines[] = '- Combos (chaqueta + buso, con polo o camiseta): desde '.$money($minCombo).' por estudiante, con 3% de descuento ya aplicado.';
        $lines = array_merge($lines, [
            '',
            '## Beneficios',
            '- 1 chaqueta de obsequio por cada 25 unidades.',
            '- 10% de descuento para hermanos o primos.',
            '- Bandera, globos y tula o termo para la promoción.',
            '- Paquete de fotografía de ceremonia de grado (solo Bogotá y Cundinamarca): $119.000 por estudiante, con estola y toga gratis.',
            '',
            '## Cómo es el proceso',
            '1. El colegio recibe la cotización (se puede pedir en 1 minuto en la web).',
            '2. Se ajustan diseño, colores, bordados y número de estudiantes.',
            '3. Presentación en el colegio (o reunión virtual fuera de Bogotá y Cundinamarca).',
            '4. Aprobación y firma del contrato en línea; anticipo del 50%.',
            '5. Toma de medidas en el colegio o tallas estándar.',
            '6. Producción con fotos reales del avance en el portal del colegio y de cada estudiante.',
            '7. Entrega en el colegio, cada prenda empacada con el nombre del estudiante.',
            '',
            '## Páginas útiles',
            '- Cotizar en 1 minuto: '.url('/').'#cotiza',
            '- Diseñar la chaqueta en 3D: '.route('personalizar'),
            '- Chaquetas de prom por ciudad: '.route('ciudades.index'),
            '- Chaquetas y dotación para empresas: '.route('empresas'),
            '- Seguimiento del pedido: '.route('seguimiento'),
            '- Blog y guías: '.route('news.index'),
        ]);
        foreach (config('ciudades') as $slug => $c) {
            $lines[] = '- Chaquetas de prom en '.$c['name'].': '.route('ciudades.show', $slug);
        }

        return response(implode("\n", $lines)."\n")->header('Content-Type', 'text/plain; charset=UTF-8');
    }
}
