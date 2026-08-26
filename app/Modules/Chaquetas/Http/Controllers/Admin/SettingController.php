<?php

namespace App\Modules\Chaquetas\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Chaquetas\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    /** Campos editables del sitio (clave => etiqueta) */
    public const FIELDS = [
        'brand_name'         => ['Nombre de marca', 'text'],
        'home_hero_eyebrow'  => ['Home · texto pequeño superior', 'text'],
        'home_hero_title'    => ['Home · título', 'text'],
        'home_hero_accent'   => ['Home · título (parte dorada)', 'text'],
        'home_hero_subtitle' => ['Home · subtítulo', 'textarea'],
        'home_video_url'     => ['Home · video (link de YouTube o TikTok)', 'text'],
        'contact_whatsapp'   => ['WhatsApp (solo dígitos)', 'text'],
        'contact_phone'      => ['Teléfono', 'text'],
        'contact_address'    => ['Dirección', 'text'],
        'wa_auto_reply'      => ['WhatsApp · Mensaje de bienvenida (si NO usas IA)', 'textarea'],
        'pay_nequi'          => ['Pagos · Nequi (número/nombre que ve el estudiante)', 'text'],
        'pay_bancolombia'    => ['Pagos · Bancolombia (cuenta/nombre que ve el estudiante)', 'text'],
        'footer_note'        => ['Texto del pie de página', 'text'],
    ];

    public function edit()
    {
        $fields = self::FIELDS;
        $values = Setting::map();

        return view('admin.cms.settings', compact('fields', 'values'));
    }

    public function update(Request $request)
    {
        foreach (array_keys(self::FIELDS) as $key) {
            Setting::put($key, $request->input("settings.$key"));
        }

        // Imagen principal del hero (banner)
        $request->validate([
            'hero_image'      => 'nullable|image|max:8192',
            'logo_image'      => 'nullable|image|max:4096',
            'banner_image'    => 'nullable|image|max:8192',
            'watermark_image' => 'nullable|image|max:8192',
        ]);
        if ($request->hasFile('hero_image')) {
            $path = $request->file('hero_image')->store('home', 'public');
            Setting::put('home_hero_image', Storage::disk('public')->url($path));
        }
        if ($request->hasFile('logo_image')) {
            $lp = $request->file('logo_image')->store('home', 'public');
            Setting::put('brand_logo', Storage::disk('public')->url($lp));
        }
        if ($request->hasFile('banner_image')) {
            $bp = $request->file('banner_image')->store('home', 'public');
            Setting::put('showcase_banner', Storage::disk('public')->url($bp));
        }
        if ($request->hasFile('watermark_image')) {
            $wp = $request->file('watermark_image')->store('home', 'public');
            Setting::put('designer_watermark', Storage::disk('public')->url($wp));
        }

        // Secciones del home (editor visual) → se guardan como líneas "a | b | c"
        $rows3 = function ($items, $a, $b, $c) {
            return collect((array) $items)->map(function ($r) use ($a, $b, $c) {
                return trim(($r[$a] ?? '').' | '.($r[$b] ?? '').' | '.($r[$c] ?? ''));
            })->filter(function ($l) {
                return trim(str_replace('|', '', $l)) !== '';
            })->implode("\n");
        };
        $rows2 = function ($items, $a, $b) {
            return collect((array) $items)->map(function ($r) use ($a, $b) {
                return trim(($r[$a] ?? '').' | '.($r[$b] ?? ''));
            })->filter(function ($l) {
                return trim(str_replace('|', '', $l)) !== '';
            })->implode("\n");
        };
        Setting::put('home_gar', $rows3($request->input('gar'), 'emoji', 'title', 'desc'));
        Setting::put('home_steps', $rows3($request->input('steps'), 'emoji', 'title', 'desc'));
        Setting::put('home_numbers', $rows2($request->input('numbers'), 'value', 'label'));
        Setting::put('home_trusted_label', (string) $request->input('trusted_label', ''));
        Setting::put('home_trusted', collect((array) $request->input('colegio'))->map(fn ($c) => trim((string) $c))->filter()->implode(', '));
        Setting::put('home_seo_content', (string) $request->input('home_seo_content', ''));

        // Métodos de pago habilitados (global). Efectivo apagado por defecto.
        Setting::put('pay_allow_efectivo', $request->boolean('pay_allow_efectivo') ? '1' : '0');
        Setting::put('pay_allow_nequi', $request->boolean('pay_allow_nequi') ? '1' : '0');
        Setting::put('pay_allow_bancolombia', $request->boolean('pay_allow_bancolombia') ? '1' : '0');
        // Plan de abonos: % al inicio y % al final (día de entrega)
        Setting::put('pay_initial_pct', (string) max(0, min(100, (int) $request->input('pay_initial_pct', 0))));
        Setting::put('pay_final_pct', (string) max(0, min(100, (int) $request->input('pay_final_pct', 50))));

        return back()->with('ok', 'Textos del sitio actualizados.');
    }
}
