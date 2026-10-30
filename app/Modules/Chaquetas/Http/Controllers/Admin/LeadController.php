<?php

namespace App\Modules\Chaquetas\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Chaquetas\Models\Lead;
use App\Modules\Chaquetas\Models\WaMessage;

class LeadController extends Controller
{
    public function index()
    {
        $leads = Lead::with('quote')->orderByDesc('ai_paused')->latest()->paginate(30);
        $counts = WaMessage::selectRaw('phone, COUNT(*) as c')->groupBy('phone')->pluck('c', 'phone');
        $needHuman = Lead::needsHuman()->count();

        return view('admin.leads.index', compact('leads', 'counts', 'needHuman'));
    }

    public function show(Lead $lead)
    {
        $messages = WaMessage::where('phone', $lead->phone)->orderBy('id')->get();
        $lead->load('quote');

        return view('admin.leads.show', compact('lead', 'messages'));
    }

    /** Pausa/reactiva la IA para este contacto (handoff a humano). */
    public function toggleAi(Lead $lead)
    {
        $lead->update([
            'ai_paused'          => ! $lead->ai_paused,
            'human_requested_at' => $lead->ai_paused ? null : now(),
        ]);

        return back()->with('ok', $lead->ai_paused
            ? 'IA pausada: ahora respondes tú por WhatsApp.'
            : 'IA reactivada: vuelve a responder automáticamente.');
    }

    public function destroy(Lead $lead)
    {
        $lead->delete();

        return back()->with('ok', 'Contacto eliminado.');
    }
}
