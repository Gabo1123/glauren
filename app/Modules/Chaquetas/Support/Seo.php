<?php

namespace App\Modules\Chaquetas\Support;

class Seo
{
    /**
     * Añade atributos SEO a las imágenes de un bloque HTML:
     *  - alt descriptivo cuando falta o está vacío (usa $alt como base)
     *  - loading="lazy" y decoding="async" para rendimiento
     * Estilo "auto-add ALT" de Rank Math.
     */
    public static function imageAlts(?string $html, string $alt): string
    {
        if (! $html) {
            return '';
        }

        $altText = htmlspecialchars(trim($alt), ENT_QUOTES);

        return preg_replace_callback('/<img\b([^>]*)>/i', function ($m) use ($altText) {
            $attrs = $m[1];

            // alt: rellena si falta o está vacío
            if (preg_match('/\balt\s*=\s*("|\')(.*?)\1/i', $attrs, $a)) {
                if (trim($a[2]) === '') {
                    $attrs = preg_replace('/\balt\s*=\s*("|\').*?\1/i', 'alt="'.$altText.'"', $attrs, 1);
                }
            } else {
                $attrs .= ' alt="'.$altText.'"';
            }

            // loading lazy
            if (! preg_match('/\bloading\s*=/i', $attrs)) {
                $attrs .= ' loading="lazy"';
            }
            if (! preg_match('/\bdecoding\s*=/i', $attrs)) {
                $attrs .= ' decoding="async"';
            }

            return '<img'.$attrs.'>';
        }, $html);
    }

    /**
     * Genera una tabla de contenido a partir de los <h2>/<h3> del HTML.
     * Devuelve ['html' => htmlConIds, 'toc' => [['level'=>2,'id'=>'...','text'=>'...'], ...]].
     */
    public static function tableOfContents(?string $html): array
    {
        if (! $html) {
            return ['html' => '', 'toc' => []];
        }

        $toc = [];
        $used = [];

        $html = preg_replace_callback('/<(h[23])\b([^>]*)>(.*?)<\/\1>/is', function ($m) use (&$toc, &$used) {
            $tag = strtolower($m[1]);
            $attrs = $m[2];
            $text = trim(strip_tags($m[3]));
            if ($text === '') {
                return $m[0];
            }

            // slug del encabezado (único)
            $base = self::slugify($text);
            $id = $base;
            $i = 2;
            while (in_array($id, $used, true)) {
                $id = $base.'-'.$i++;
            }
            $used[] = $id;

            $toc[] = ['level' => $tag === 'h2' ? 2 : 3, 'id' => $id, 'text' => $text];

            // si ya tiene id, respétalo; si no, agrégalo
            if (preg_match('/\bid\s*=/i', $attrs)) {
                return '<'.$tag.$attrs.'>'.$m[3].'</'.$tag.'>';
            }

            return '<'.$tag.$attrs.' id="'.$id.'">'.$m[3].'</'.$tag.'>';
        }, $html);

        return ['html' => $html, 'toc' => $toc];
    }

    private static function slugify(string $text): string
    {
        $text = \Illuminate\Support\Str::ascii($text);
        $text = preg_replace('/[^a-zA-Z0-9]+/', '-', $text);

        return trim(strtolower($text), '-') ?: 'seccion';
    }
}
