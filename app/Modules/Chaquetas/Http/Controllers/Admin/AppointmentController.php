<?php

namespace App\Modules\Chaquetas\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Chaquetas\Models\Appointment;
use App\Modules\Chaquetas\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AppointmentController extends Controller
{
    public function index()
    {
        $upcoming = Appointment::where('date', '>=', now()->toDateString())
            ->where('status', 'agendada')
            ->orderBy('date')->orderBy('time')->get();
        $past = Appointment::where(function ($q) {
            $q->where('date', '<', now()->toDateString())->orWhere('status', '!=', 'agendada');
        })->orderByDesc('date')->orderByDesc('time')->limit(50)->get();

        $cfg = [
            'notify_email' => Setting::get('contact_notify_email', ''),
            'subject'      => Setting::get('contact_reply_subject', ''),
            'message'      => Setting::get('contact_reply_message', ''),
            'pdf'          => Setting::get('contact_reply_pdf'),
        ];
        $mailReady = ! empty(config('mail.mailers.smtp.username'));

        return view('admin.contracts.appointments', compact('upcoming', 'past', 'cfg', 'mailReady'));
    }

    public function saveConfig(Request $request)
    {
        $request->validate([
            'contact_notify_email'  => 'nullable|email',
            'contact_reply_subject' => 'nullable|string|max:160',
            'contact_reply_message' => 'nullable|string|max:3000',
            'contact_pdf'           => 'nullable|file|mimes:pdf|max:8192',
        ]);

        Setting::put('contact_notify_email', (string) $request->input('contact_notify_email', ''));
        Setting::put('contact_reply_subject', (string) $request->input('contact_reply_subject', ''));
        Setting::put('contact_reply_message', (string) $request->input('contact_reply_message', ''));

        if ($request->hasFile('contact_pdf')) {
            // borra el anterior
            $old = Setting::get('contact_reply_pdf');
            if ($old && Storage::disk('public')->exists($old)) {
                Storage::disk('public')->delete($old);
            }
            $path = $request->file('contact_pdf')->store('contacto', 'public');
            Setting::put('contact_reply_pdf', $path);
        }

        return back()->with('ok', 'Configuración del correo de contacto guardada.');
    }

    public function removePdf()
    {
        $old = Setting::get('contact_reply_pdf');
        if ($old && Storage::disk('public')->exists($old)) {
            Storage::disk('public')->delete($old);
        }
        Setting::put('contact_reply_pdf', '');

        return back()->with('ok', 'PDF eliminado.');
    }

    public function updateStatus(Request $request, Appointment $appointment)
    {
        $data = $request->validate(['status' => 'required|in:agendada,atendida,cancelada']);
        $appointment->update($data);

        return back()->with('ok', 'Cita actualizada.');
    }

    public function destroy(Appointment $appointment)
    {
        $appointment->delete();

        return back()->with('ok', 'Cita eliminada.');
    }
}
