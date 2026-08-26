<?php

namespace App\Modules\Chaquetas\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Chaquetas\Models\JacketType;
use App\Modules\Chaquetas\Models\Quote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ConfiguradorController extends Controller
{
    /** Landing: elegir tipo de chaqueta */
    public function index()
    {
        $types = JacketType::where('active', true)
            ->orderByRaw("(model_path is not null and model_path != '') desc") // con 3D primero
            ->orderBy('sort')
            ->get();
        $categories = $types->pluck('category')->filter()->unique()->sort()->values();

        return view('chaquetas.index', compact('types', 'categories'));
    }

    /** Configurador 3D de un tipo */
    public function design(string $slug)
    {
        $type = JacketType::where('slug', $slug)->where('active', true)->firstOrFail();

        return view('chaquetas.design', compact('type'));
    }

    /** Guardar cotización + generar enlace de WhatsApp */
    public function storeQuote(Request $request)
    {
        $data = $request->validate([
            'jacket_type_id'  => 'required|exists:jacket_types,id',
            'customer_name'   => 'required|string|max:120',
            'customer_phone'  => 'required|string|max:40',
            'customer_email'  => 'nullable|email|max:160',
            'school'          => 'nullable|string|max:160',
            'quantity'        => 'required|integer|min:1|max:100000',
            'config'          => 'nullable|array',
            'estimated_price' => 'required|numeric|min:0',
            'image'           => 'nullable|string',
            'images'          => 'nullable|array',
            'images.*'        => 'nullable|string',
        ]);

        // Guarda todas las vistas (frente, derecha, espalda, izquierda)
        $images = [];
        foreach ((array) ($data['images'] ?? []) as $label => $dataUrl) {
            if (is_string($dataUrl) && str_starts_with($dataUrl, 'data:image')) {
                if ($p = $this->saveImage($dataUrl)) {
                    $images[$label] = $p;
                }
            }
        }

        // Compatibilidad: image_path = frente (o la imagen suelta)
        $imagePath = $images['frente'] ?? null;
        if (! $imagePath && ! empty($data['image']) && str_starts_with($data['image'], 'data:image')) {
            $imagePath = $this->saveImage($data['image']);
        }

        $quote = Quote::create([
            'jacket_type_id'  => $data['jacket_type_id'],
            'customer_name'   => $data['customer_name'],
            'customer_phone'  => $data['customer_phone'],
            'customer_email'  => $data['customer_email'] ?? null,
            'school'          => $data['school'] ?? null,
            'quantity'        => $data['quantity'],
            'config'          => $data['config'] ?? [],
            'estimated_price' => $data['estimated_price'],
            'image_path'      => $imagePath,
            'images'          => $images ?: null,
            'status'          => 'nuevo',
        ]);

        $type = JacketType::find($data['jacket_type_id']);

        return response()->json([
            'ok'           => true,
            'quote_id'     => $quote->id,
            'whatsapp_url' => $this->whatsappUrl($quote, $type),
        ]);
    }

    private function saveImage(string $dataUrl): ?string
    {
        $parts = explode(',', $dataUrl, 2);
        if (count($parts) !== 2) {
            return null;
        }
        $binary = base64_decode($parts[1], true);
        if ($binary === false) {
            return null;
        }
        $name = 'quotes/'.date('Ymd_His').'_'.uniqid().'.png';
        Storage::disk('public')->put($name, $binary);

        return $name;
    }

    private function whatsappUrl(Quote $quote, JacketType $type): string
    {
        // Mensaje CORTO con la foto del diseño (sin volcar los nombres internos de colores).
        $lines = [];
        $lines[] = '¡Hola! 👋 Quiero cotizar mi chaqueta de promoción:';
        $lines[] = '• Prenda: '.$type->name;
        $lines[] = '• Cantidad: '.$quote->quantity;
        if ($quote->school) {
            $lines[] = '• Colegio: '.$quote->school;
        }
        $lines[] = '• Cliente: '.$quote->customer_name;
        $lines[] = '';

        $labels = ['frente' => '📸 Frente', 'derecha' => '➡️ Lado derecho', 'espalda' => '🔙 Espalda', 'izquierda' => '⬅️ Lado izquierdo'];
        $urls = $quote->imageUrls();
        if ($urls) {
            $lines[] = 'Mi diseño (varias vistas):';
            foreach ($labels as $key => $lbl) {
                if (! empty($urls[$key])) {
                    $lines[] = $lbl.': '.$urls[$key];
                }
            }
        }
        $lines[] = '(Cotización #'.$quote->id.')';

        $text = rawurlencode(implode("\n", $lines));

        // Va SIEMPRE al WhatsApp del negocio
        $num = preg_replace('/\D/', '', (string) (\App\Modules\Chaquetas\Models\Setting::get('contact_whatsapp') ?: config('chaquetas.whatsapp')));

        return $num
            ? "https://wa.me/{$num}?text={$text}"
            : "https://api.whatsapp.com/send?text={$text}";
    }
}
