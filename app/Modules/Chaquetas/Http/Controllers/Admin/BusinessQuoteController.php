<?php

namespace App\Modules\Chaquetas\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Chaquetas\Models\Quote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Cotizaciones para empresas: varias prendas, cada una con foto, características,
 * cantidad y valor unitario al por mayor. Obsequio: tula + 1 chaqueta por cada 25 unidades.
 *
 * Se guardan como Quote con source = 'empresa' (sin cambios en la base de datos):
 *  - school           → nombre de la empresa
 *  - customer_*       → persona de contacto
 *  - config.company   → NIT, ciudad, cargo, validez
 *  - items.lines      → prendas
 */
class BusinessQuoteController extends Controller
{
    /** source de la cotización y prefijo de rutas de este apartado. */
    protected string $kind = 'empresa';

    protected string $routes = 'admin.business';

    /** Textos que cambian entre empresas y colegios. */
    protected function labels(): array
    {
        return [
            'title'       => 'Cotizaciones para empresas',
            'subtitle'    => 'Dotación y prendas corporativas: varias prendas con foto, características y valor por unidad al por mayor.',
            'new'         => 'Nueva cotización empresa',
            'new_title'   => 'Nueva cotización para empresa',
            'entity'      => 'Empresa',
            'entity_ph'   => 'Ej.: Constructora Andes S.A.S.',
            'role_ph'     => 'Compras, RR. HH.…',
            'qty'         => 'Cantidad',
            'units'       => 'Unidades',
            'price'       => 'Valor x unidad',
            'price_ph'    => 'Al por mayor',
            'empty'       => 'Aún no hay cotizaciones para empresas.',
            'icon'        => 'bi-briefcase',
            'garment'     => 'Chaqueta',
            'default_qty' => 25,
            'gifts'       => ['Tula de obsequio', '1 chaqueta adicional por cada 25 unidades'],
            'school'      => false,
        ];
    }

    protected function viewData(array $extra = []): array
    {
        return $extra + ['kind' => $this->kind, 'rp' => $this->routes, 'L' => $this->labels()];
    }

    public function index(Request $request)
    {
        $q = trim((string) $request->get('q'));
        $quotes = Quote::kind($this->kind)
            ->when($q, fn ($w) => $w->where(fn ($x) => $x->where('school', 'like', "%{$q}%")
                ->orWhere('customer_name', 'like', "%{$q}%")
                ->orWhere('customer_phone', 'like', "%{$q}%")
                ->orWhere('number', 'like', "%{$q}%")))
            ->latest()->paginate(25)->withQueryString();


        $stats = [
            'abiertas' => Quote::kind($this->kind)->whereIn('status', Quote::OPEN_STATUSES)->count(),
            'ganadas'  => Quote::kind($this->kind)->where('status', 'cerrado')->count(),
            'valor'    => (float) Quote::kind($this->kind)->whereIn('status', Quote::OPEN_STATUSES)->sum('estimated_price'),
        ];

        return view('admin.business.index', $this->viewData(compact('quotes', 'q', 'stats')));
    }

    public function create()
    {
        return view('admin.business.form', $this->viewData(['quote' => new Quote(['quantity' => 1]), 'lines' => [], 'company' => (new Quote)->company()]));
    }

    public function store(Request $request)
    {
        [$data, $lines] = $this->validated($request);

        $quote = Quote::create([
            'source'          => $this->kind,
            'school'          => $data['company_name'],
            'customer_name'   => $data['contact_name'],
            'customer_phone'  => $data['contact_phone'],
            'customer_email'  => $data['contact_email'] ?? null,
            'config'          => ['company' => $this->companyData($data)],
            'items'           => ['type' => $this->kind, 'lines' => $lines],
            'quantity'        => $this->quantity($lines),
            'estimated_price' => $this->total($lines),
            'status'          => 'nuevo',
            'follow_up_at'    => $data['follow_up_at'] ?? today()->addDays(2),
            'notes'           => $data['notes'] ?? null,
        ]);

        $msg = 'Cotización '.$quote->code().' para '.$quote->school.' creada.';
        if ($quote->customer_email && $request->boolean('send_email')) {
            $msg .= $quote->emailToCustomer() ? ' Se envió a '.$quote->customer_email.'.' : ' No se pudo enviar el correo.';
        }

        return redirect()->route('admin.quotes.show', $quote)->with('ok', $msg.' Ya puedes enviarla por WhatsApp.');
    }

    public function edit(Quote $quote)
    {
        abort_unless($quote->source === $this->kind, 404);

        return view('admin.business.form', $this->viewData(['quote' => $quote, 'lines' => $quote->businessLines(), 'company' => $quote->company()]));
    }

    public function update(Request $request, Quote $quote)
    {
        abort_unless($quote->source === $this->kind, 404);
        [$data, $lines] = $this->validated($request, $quote);

        // Fotos que se quitaron o reemplazaron: se borran del disco.
        $keep = array_filter(array_column($lines, 'photo'));
        foreach ($quote->businessLines() as $old) {
            if ($old['photo'] && ! in_array($old['photo'], $keep, true)) {
                Storage::disk('public')->delete($old['photo']);
            }
        }

        $config = (array) $quote->config;
        $config['company'] = $this->companyData($data);
        $quote->update([
            'school'          => $data['company_name'],
            'customer_name'   => $data['contact_name'],
            'customer_phone'  => $data['contact_phone'],
            'customer_email'  => $data['contact_email'] ?? null,
            'config'          => $config,
            'items'           => ['type' => $this->kind, 'lines' => $lines],
            'quantity'        => $this->quantity($lines),
            'estimated_price' => $this->total($lines),
            'follow_up_at'    => $data['follow_up_at'] ?? $quote->follow_up_at,
            'notes'           => $data['notes'] ?? null,
        ]);
        $quote->log('nota', 'Cotización actualizada: '.count($lines).' prenda(s), '.$quote->businessTotals()['units'].' unidades');

        return redirect()->route('admin.quotes.show', $quote)->with('ok', 'Cotización '.$quote->code().' actualizada.');
    }

    /** @return array{0: array, 1: array<int, array>} */
    private function validated(Request $request, ?Quote $quote = null): array
    {
        $data = $request->validate([
            'company_name'         => 'required|string|max:160',
            'company_nit'          => 'nullable|string|max:40',
            'company_course'       => 'nullable|string|max:80',
            'company_city'         => 'nullable|string|max:120',
            'contact_name'         => 'required|string|max:120',
            'contact_role'         => 'nullable|string|max:120',
            'contact_phone'        => 'required|string|max:40',
            'contact_email'        => 'nullable|email|max:160',
            'validity'             => 'nullable|integer|min:1|max:365',
            'follow_up_at'         => 'nullable|date',
            'notes'                => 'nullable|string|max:5000',
            'lines'                => 'required|array|min:1|max:30',
            'lines.*.garment'      => 'nullable|string|max:60',
            'lines.*.name'         => 'required|string|max:120',
            'lines.*.features'     => 'nullable|string|max:3000',
            'lines.*.qty'          => 'required|integer|min:1|max:100000',
            'lines.*.unit_price'   => ['required', 'regex:/^[\d.,\s$]+$/', 'max:20'],
            'lines.*.photo'        => 'nullable|image|mimes:jpg,jpeg,png,webp|max:8192',
            'lines.*.photo_path'   => 'nullable|string|max:255',
            'lines.*.remove_photo' => 'nullable|boolean',
        ], [
            'lines.required'        => 'Agrega al menos una prenda.',
            'lines.*.name.required' => 'Cada prenda necesita un nombre.',
            'lines.*.photo.image'   => 'La foto debe ser una imagen (JPG, PNG o WEBP).',
            'lines.*.photo.max'     => 'Cada foto puede pesar máximo 8 MB.',
        ]);

        $existing = $quote ? array_filter(array_column($quote->businessLines(), 'photo')) : [];
        $lines = [];
        foreach ($data['lines'] as $key => $l) {
            $photo = null;
            // Solo se conserva una foto que ya era de esta cotización (evita rutas inventadas).
            if (! empty($l['photo_path']) && in_array($l['photo_path'], $existing, true) && empty($l['remove_photo'])) {
                $photo = $l['photo_path'];
            }
            $file = $request->file("lines.{$key}.photo");
            if ($file) {
                $photo = $file->store('cotizaciones-empresa', 'public');
            }

            $lines[] = [
                'garment'    => $l['garment'] ?? '',
                'name'       => $l['name'],
                'features'   => array_values(array_filter(array_map(fn ($f) => trim($f, " \t-•*"), preg_split('/\r\n|\r|\n/', (string) ($l['features'] ?? ''))))),
                'qty'        => (int) $l['qty'],
                'unit_price' => (int) preg_replace('/\D/', '', $l['unit_price']),
                'photo'      => $photo,
            ];
        }

        return [$data, $lines];
    }

    private function companyData(array $data): array
    {
        return [
            'nit'      => $data['company_nit'] ?? null,
            'course'   => $data['company_course'] ?? null,
            'city'     => $data['company_city'] ?? null,
            'role'     => $data['contact_role'] ?? null,
            'validity' => (int) ($data['validity'] ?? 30),
        ];
    }

    /** Empresas: total de unidades. Colegios: número de estudiantes (la prenda con más unidades). */
    private function quantity(array $lines): int
    {
        $qty = array_column($lines, 'qty');

        return max(1, $this->labels()['school'] ? (int) max($qty ?: [1]) : array_sum($qty));
    }

    private function total(array $lines): int
    {
        return array_sum(array_map(fn ($l) => $l['qty'] * $l['unit_price'], $lines));
    }
}
