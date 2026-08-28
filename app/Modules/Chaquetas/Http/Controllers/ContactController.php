<?php

namespace App\Modules\Chaquetas\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Chaquetas\Mail\AdminLeadMail;
use App\Modules\Chaquetas\Mail\CustomerReplyMail;
use App\Modules\Chaquetas\Models\Appointment;
use App\Modules\Chaquetas\Models\Lead;
use App\Modules\Chaquetas\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function show()
    {
        return view('chaquetas.contacto');
    }

    /** Franjas disponibles para una fecha (AJAX para el calendario). */
    public function slots(Request $request)
    {
        $date = $request->query('date');
        if (! $date || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) || $date < now()->toDateString()) {
            return response()->json(['slots' => []]);
        }

        return response()->json(['slots' => Appointment::availableSlots($date)]);
    }

    public function submit(Request $request)
    {
        $data = $request->validate([
            'name'    => 'required|string|max:120',
            'phone'   => 'required|string|max:40',
            'email'   => 'nullable|email|max:160',
            'school'  => 'nullable|string|max:160',
            'message' => 'nullable|string|max:1000',
            'date'    => 'nullable|date_format:Y-m-d|after_or_equal:today',
            'time'    => 'nullable|date_format:H:i',
        ]);

        $lead = Lead::create([
            'name'           => $data['name'],
            'phone'          => $data['phone'],
            'email'          => $data['email'] ?? null,
            'school'         => $data['school'] ?? null,
            'message'        => $data['message'] ?? null,
            'source'         => 'contacto',
            'accepted_terms' => true,
        ]);

        // ¿Agendó reunión? Crea la cita si la franja sigue libre
        $appt = null;
        if (! empty($data['date']) && ! empty($data['time'])) {
            if (in_array($data['time'], Appointment::availableSlots($data['date']), true)) {
                $appt = Appointment::create([
                    'lead_id' => $lead->id,
                    'name'    => $data['name'],
                    'phone'   => $data['phone'],
                    'email'   => $data['email'] ?? null,
                    'school'  => $data['school'] ?? null,
                    'date'    => $data['date'],
                    'time'    => $data['time'],
                ]);
            } else {
                return back()->withInput()->withErrors(['time' => 'Ese horario se acaba de ocupar. Elige otro, por favor.']);
            }
        }

        $this->sendEmails($lead, $appt);

        return back()->with('sent', true)->with('appt', $appt?->id);
    }

    /** Envía el aviso interno y el correo de respuesta al cliente (no rompe si el SMTP falla). */
    private function sendEmails(Lead $lead, ?Appointment $appt): void
    {
        // Aviso al dueño
        $adminTo = Setting::get('contact_notify_email') ?: config('mail.from.address');
        if ($adminTo && filter_var($adminTo, FILTER_VALIDATE_EMAIL)) {
            try {
                Mail::to($adminTo)->send(new AdminLeadMail($lead, $appt));
            } catch (\Throwable $e) {
                Log::warning('AdminLeadMail error', ['msg' => $e->getMessage()]);
            }
        }

        // Respuesta automática al cliente (con PDF si está cargado)
        if ($lead->email && filter_var($lead->email, FILTER_VALIDATE_EMAIL)) {
            try {
                Mail::to($lead->email)->send(new CustomerReplyMail($lead->name, $appt));
            } catch (\Throwable $e) {
                Log::warning('CustomerReplyMail error', ['msg' => $e->getMessage()]);
            }
        }
    }

    /** Guarda el lead del popup "chaqueta gratis" (AJAX). */
    public function popup(Request $request)
    {
        $data = $request->validate([
            'name'  => 'required|string|max:120',
            'phone' => 'required|string|max:40',
        ]);

        $phone = preg_replace('/\D/', '', $data['phone']);
        $ip    = $request->ip();

        // Evita duplicados: si ya se registró este teléfono o esta IP, no lo repite.
        $already = Lead::where('source', 'popup-gratis')
            ->where(function ($q) use ($phone, $ip) {
                if ($phone !== '') {
                    $q->whereRaw("REPLACE(REPLACE(REPLACE(phone,' ',''),'-',''),'+','') LIKE ?", ['%'.$phone]);
                }
                if ($ip) {
                    $q->orWhere('ip', $ip);
                }
            })
            ->exists();

        if (! $already) {
            Lead::create([
                'name'           => $data['name'],
                'phone'          => $data['phone'],
                'source'         => 'popup-gratis',
                'ip'             => $ip,
                'accepted_terms' => true,
            ]);
        }

        return response()->json(['ok' => true, 'already' => $already]);
    }
}
