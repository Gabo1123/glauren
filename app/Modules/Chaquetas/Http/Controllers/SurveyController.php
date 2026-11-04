<?php

namespace App\Modules\Chaquetas\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Chaquetas\Models\Contract;
use App\Modules\Chaquetas\Models\Testimonial;
use App\Modules\Chaquetas\Support\DeliverySurvey;
use App\Modules\Chaquetas\Support\ImageOptimizer;
use Illuminate\Http\Request;

/** Encuesta pública de satisfacción después de la entrega (enlace firmado). */
class SurveyController extends Controller
{
    public function show(Contract $contract)
    {
        $contract->load('school');

        return view('chaquetas.encuesta', [
            'contract' => $contract,
            'google'   => DeliverySurvey::googleUrl(),
            'action'   => DeliverySurvey::url($contract),
            'done'     => session('survey_done'),
        ]);
    }

    public function store(Request $request, Contract $contract)
    {
        if ($request->filled('website')) {      // honeypot anti-spam
            return back();
        }
        $data = $request->validate([
            'rating'   => 'required|integer|min:1|max:5',
            'name'     => 'required|string|max:80',
            'role'     => 'nullable|string|max:40',
            'text'     => 'nullable|string|max:1200',
            'photos'   => 'nullable|array|max:6',
            'photos.*' => 'image|max:8192',
            'publish'  => 'nullable|boolean',
        ], ['rating.required' => 'Elige de 1 a 5 estrellas.', 'name.required' => 'Escribe tu nombre.']);

        $paths = [];
        foreach ((array) $request->file('photos', []) as $file) {
            $p = $file->store('encuestas/'.$contract->id, 'public');
            ImageOptimizer::optimizeStored($p);
            $paths[] = $p;
        }

        $school = $contract->school?->name;
        $extra = [];
        if (count($paths) > 1) {
            $extra[] = 'Fotos adicionales: '.implode(', ', array_slice($paths, 1));
        }
        if (! $request->boolean('publish')) {
            $extra[] = '(No autorizó publicar)';
        }

        // Queda como testimonio INACTIVO: tú decides en Testimonios si se publica en la web
        Testimonial::create([
            'text'       => trim(($data['text'] ?? '') ?: '⭐ '.$data['rating'].' estrellas').($extra ? "\n\n".implode("\n", $extra) : ''),
            'name'       => $data['name'],
            'role'       => trim(($data['role'] ?? '') ? $data['role'].' · ' : '').($school ?: 'Contrato '.$contract->code),
            'photo_path' => $paths[0] ?? null,
            'rating'     => (int) $data['rating'],
            'sort'       => 999,
            'active'     => false,
        ]);

        return redirect(DeliverySurvey::url($contract))->with('survey_done', (int) $data['rating']);
    }
}
