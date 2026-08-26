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
            ['loc' => route('colegios'), 'priority' => '0.6', 'freq' => 'monthly'],
        ];

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
            'Disallow: /*?*',            // URLs con parámetros (evita contenido duplicado)
            '',
            // Bloquea rastreadores de IA/scrapers agresivos
            'User-agent: GPTBot',
            'Disallow: /',
            'User-agent: CCBot',
            'Disallow: /',
            '',
            'Sitemap: '.url('/sitemap.xml'),
            '',
        ];

        return response(implode("\n", $lines))->header('Content-Type', 'text/plain');
    }
}
