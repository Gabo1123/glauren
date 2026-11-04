<?php

namespace App\Modules\Chaquetas\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Chaquetas\Mail\AdminLeadMail;
use App\Modules\Chaquetas\Models\Lead;
use App\Modules\Chaquetas\Models\Quote;
use App\Modules\Chaquetas\Models\Setting;
use App\Modules\Chaquetas\Support\ImageOptimizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Formularios públicos que crean cotizaciones solas:
 *  - "Cotiza en 1 minuto" (home): el cliente ve su cotización completa al instante.
 *  - Página /empresas: solicitud de dotación; el asesor arma la cotización con fotos y valores.
 */
class QuickQuoteController extends Controller
{
    public const INTERESTS = [
        'chaqueta'   => 'Chaqueta con cortes',
        'beisbolera' => 'Beisbolera',
        'doble_faz'  => 'Chaqueta doble faz',
        'buso'       => 'Buso',
        'combo'      => 'Combo (chaqueta + buso)',
        'no_se'      => 'Aún no sé, quiero asesoría',
    ];

    public const BUSINESS_GARMENTS = ['Chaquetas', 'Busos', 'Chalecos', 'Camisetas polo', 'Camisetas deportivas', 'Gorras', 'Otro'];

    /** Cotiza en 1 minuto (colegios). */
    public function school(Request $request)
    {
        if ($request->filled('website')) {       // trampa para bots
            return redirect()->route('home');
        }

        $data = $request->validate([
            'name'     => 'required|string|max:120',
            'phone'    => ['required', 'string', 'max:20', 'regex:/^[\d\s+()-]{7,20}$/'],
            'email'    => 'nullable|email|max:160',
            'school'   => 'required|string|max:160',
            'students' => 'required|integer|min:1|max:2000',
            'interest' => 'nullable|in:'.implode(',', array_keys(self::INTERESTS)),
            'accept'   => 'accepted',
        ], [
            'phone.regex'     => 'Escribe un celular válido.',
            'accept.accepted' => 'Acepta el tratamiento de datos para enviarte la cotización.',
            'students.min'    => 'Escribe cuántos estudiantes son.',
        ]);

        $interest = self::INTERESTS[$data['interest'] ?? 'no_se'] ?? null;
        $lead = $this->findLead($data['phone']) ?? new Lead(['source' => 'cotiza-1min', 'accepted_terms' => true]);
        $lead->fill([
            'name'    => $data['name'],
            'phone'   => $data['phone'],
            'email'   => $data['email'] ?? $lead->email,
            'school'  => $data['school'],
            'message' => 'Cotiza en 1 minuto: '.$data['students'].' estudiantes'.($interest ? ' · Interés: '.$interest : ''),
            'ip'      => $request->ip(),
        ])->save();

        $quote = Quote::forLead($lead);
        $quote->update([
            'customer_name'  => $lead->name,
            'customer_phone' => $lead->phone,
            'customer_email' => $lead->email,
            'school'         => $lead->school,
            'quantity'       => (int) $data['students'],
            'follow_up_at'   => today(),
        ]);
        $quote->log('nota', 'Pidió la cotización en la web (Cotiza en 1 minuto)'.($interest ? ' · '.$interest : ''));

        if ($quote->customer_email) {
            $quote->emailToCustomer();
        }
        $this->notifyAdmin($lead);

        return redirect()->to($quote->publicUrl().'?nueva=1');
    }

    /** Página pública para empresas. */
    public function businessPage()
    {
        return view('chaquetas.empresas', ['garments' => self::BUSINESS_GARMENTS]);
    }

    /** Solicitud de cotización de empresa: queda en Cotizaciones empresas para que el asesor la arme. */
    public function business(Request $request)
    {
        if ($request->filled('website')) {
            return redirect()->route('empresas');
        }

        $data = $request->validate([
            'company'    => 'required|string|max:160',
            'nit'        => 'nullable|string|max:40',
            'city'       => 'nullable|string|max:120',
            'name'       => 'required|string|max:120',
            'role'       => 'nullable|string|max:120',
            'phone'      => ['required', 'string', 'max:20', 'regex:/^[\d\s+()-]{7,20}$/'],
            'email'      => 'nullable|email|max:160',
            'garments'   => 'nullable|array',
            'garments.*' => 'in:'.implode(',', self::BUSINESS_GARMENTS),
            'units'      => 'required|integer|min:1|max:100000',
            'message'    => 'nullable|string|max:2000',
            'logo'       => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf,svg|max:8192',
            'accept'     => 'accepted',
        ], [
            'phone.regex'     => 'Escribe un celular válido.',
            'accept.accepted' => 'Acepta el tratamiento de datos para enviarte la cotización.',
            'logo.max'        => 'El logo puede pesar máximo 8 MB.',
        ]);

        $logo = null;
        if ($file = $request->file('logo')) {
            $logo = $file->store('cotizaciones-empresa/logos', 'public');
            ImageOptimizer::optimizeStored($logo);
        }

        $garments = $data['garments'] ?? [];
        $summary = 'Solicitud web: '.($garments ? implode(', ', $garments) : 'prendas por definir').' · ~'.$data['units'].' unidades'
            .($data['city'] ?? null ? ' · '.$data['city'] : '')
            .(! empty($data['message']) ? "\n".$data['message'] : '')
            .($logo ? "\nLogo: ".\Illuminate\Support\Facades\Storage::disk('public')->url($logo) : '');

        $lead = Lead::create([
            'name'           => $data['name'],
            'phone'          => $data['phone'],
            'email'          => $data['email'] ?? null,
            'school'         => $data['company'],
            'message'        => $summary,
            'source'         => 'empresa-web',
            'ip'             => $request->ip(),
            'accepted_terms' => true,
        ]);

        $quote = Quote::create([
            'source'          => 'empresa',
            'lead_id'         => $lead->id,
            'school'          => $data['company'],
            'customer_name'   => $data['name'],
            'customer_phone'  => $data['phone'],
            'customer_email'  => $data['email'] ?? null,
            'config'          => ['company' => ['nit' => $data['nit'] ?? null, 'city' => $data['city'] ?? null, 'role' => $data['role'] ?? null, 'validity' => 30],
                'request' => ['garments' => $garments, 'units' => (int) $data['units'], 'logo' => $logo]],
            'items'           => ['type' => 'empresa', 'lines' => []],
            'quantity'        => (int) $data['units'],
            'estimated_price' => 0,
            'status'          => 'nuevo',
            'follow_up_at'    => today(),
            'notes'           => $summary,
        ]);
        $quote->log('nota', 'Solicitud desde la página de empresas: arma la cotización con fotos y valores y envíala.');
        $this->notifyAdmin($lead);

        return redirect()->route('empresas')->with('sent', $quote->code());
    }

    private function findLead(string $phone): ?Lead
    {
        $digits = substr(preg_replace('/\D/', '', $phone), -10);
        if (strlen($digits) < 7) {
            return null;
        }

        return Lead::whereNotNull('phone')->where('phone', 'like', '%'.substr($digits, -4))->latest()->get()
            ->first(fn (Lead $l) => substr(preg_replace('/\D/', '', (string) $l->phone), -10) === $digits);
    }

    private function notifyAdmin(Lead $lead): void
    {
        $to = Setting::get('contact_notify_email') ?: config('mail.from.address');
        if ($to && filter_var($to, FILTER_VALIDATE_EMAIL)) {
            try {
                Mail::to($to)->send(new AdminLeadMail($lead));
            } catch (\Throwable $e) {
                Log::warning('AdminLeadMail error', ['msg' => $e->getMessage()]);
            }
        }
    }
}
