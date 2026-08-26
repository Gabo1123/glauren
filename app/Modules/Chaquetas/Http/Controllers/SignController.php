<?php

namespace App\Modules\Chaquetas\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Chaquetas\Models\Contract;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SignController extends Controller
{
    public function show(string $token)
    {
        $contract = Contract::with('school')->where('sign_token', $token)->firstOrFail();

        return view('chaquetas.contrato.firmar', compact('contract'));
    }

    public function sign(Request $request, string $token)
    {
        $contract = Contract::where('sign_token', $token)->firstOrFail();

        if ($contract->contract_status === 'firmado') {
            return redirect()->route('contrato.firmar', $token);
        }

        $data = $request->validate([
            'signed_by_name'     => 'required|string|max:160',
            'signed_by_document' => 'required|string|max:40',
            'signature'          => 'required|string',
        ]);

        $path = $this->saveSignature($data['signature']);
        if (! $path) {
            return back()->withErrors(['signature' => 'La firma no se pudo procesar. Vuelve a firmar.']);
        }

        $contract->update([
            'signed_by_name'     => $data['signed_by_name'],
            'signed_by_document' => $data['signed_by_document'],
            'signature_path'     => $path,
            'signed_at'          => now(),
            'contract_status'    => 'firmado',
        ]);

        return redirect()->route('contrato.firmar', $token)->with('signed', true);
    }

    private function saveSignature(string $dataUrl): ?string
    {
        if (! str_starts_with($dataUrl, 'data:image')) {
            return null;
        }
        $parts = explode(',', $dataUrl, 2);
        if (count($parts) !== 2) {
            return null;
        }
        $binary = base64_decode($parts[1], true);
        if ($binary === false) {
            return null;
        }
        $name = 'firmas/'.date('Ymd_His').'_'.uniqid().'.png';
        Storage::disk('public')->put($name, $binary);

        return $name;
    }
}
