<?php

namespace App\Modules\Chaquetas\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Chaquetas\Models\Contract;
use Illuminate\Http\Request;

class JoinController extends Controller
{
    private function contract(string $token): Contract
    {
        return Contract::with('school', 'jacketType')->where('join_token', $token)->firstOrFail();
    }

    /** Formulario público para que el estudiante se registre solo (por QR). */
    public function show(string $token)
    {
        $contract = $this->contract($token);

        return view('chaquetas.join', compact('contract'));
    }

    public function store(Request $request, string $token)
    {
        $contract = $this->contract($token);

        $data = $request->validate([
            'name'     => 'required|string|max:160',
            'document' => 'required|string|max:40',
            'gender'   => 'nullable|in:hombre,mujer',
            'phone'    => 'nullable|string|max:40',
            'size'     => 'nullable|string|max:20',
        ]);

        // Evita duplicados por documento en el mismo contrato
        $exists = $contract->students()->where('document', trim($data['document']))->exists();
        if ($exists) {
            return back()->withInput()->withErrors(['document' => 'Ya estás registrado en este curso con ese documento.']);
        }

        $contract->students()->create($data);

        return view('chaquetas.join', ['contract' => $contract, 'done' => true, 'name' => $data['name']]);
    }
}
