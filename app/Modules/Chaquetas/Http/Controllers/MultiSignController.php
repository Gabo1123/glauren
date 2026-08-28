<?php

namespace App\Modules\Chaquetas\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Chaquetas\Models\ContractSigner;
use App\Modules\Chaquetas\Models\Setting;
use App\Modules\Chaquetas\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class MultiSignController extends Controller
{
    public function __construct(private WhatsAppService $wa) {}

    private function signer(string $token): ContractSigner
    {
        return ContractSigner::with('contract.school', 'contract.jacketType')
            ->where('token', $token)->firstOrFail();
    }

    /** ¿Este firmante necesita verificarse con código de WhatsApp? */
    private function needsOtp(ContractSigner $s): bool
    {
        return $s->phone && $this->wa->configured();
    }

    private function verifiedInSession(Request $request, ContractSigner $s): bool
    {
        return in_array($s->id, (array) $request->session()->get('signer_verified', []), true);
    }

    public function show(Request $request, string $token)
    {
        $signer = $this->signer($token);
        $contract = $signer->contract;

        $step = 'sign';
        if (! $signer->hasSigned() && $this->needsOtp($signer) && ! $this->verifiedInSession($request, $signer)) {
            $step = 'verify';
        }

        return view('chaquetas.contrato.firmar-multi', compact('signer', 'contract', 'step'));
    }

    /** Envía el código de verificación al WhatsApp del firmante. */
    public function sendCode(Request $request, string $token)
    {
        $signer = $this->signer($token);
        if (! $this->needsOtp($signer)) {
            return back();
        }

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $signer->update(['otp' => Hash::make($code), 'otp_expires' => now()->addMinutes(10)]);

        $brand = Setting::get('brand_name', 'Glauren');
        $this->wa->sendText($this->wa->normalize($signer->phone),
            "🔐 {$brand} — Código para firmar el contrato: {$code}\nVence en 10 minutos.");

        return back()->with('ok', 'Te enviamos un código por WhatsApp.');
    }

    public function verify(Request $request, string $token)
    {
        $signer = $this->signer($token);
        $data = $request->validate(['code' => 'required|digits:6']);

        if (! $signer->otp || ! $signer->otp_expires || now()->greaterThan($signer->otp_expires)) {
            return back()->withErrors(['code' => 'El código expiró. Pide uno nuevo.']);
        }
        if (! Hash::check($data['code'], $signer->otp)) {
            return back()->withErrors(['code' => 'Código incorrecto.']);
        }

        $verified = (array) $request->session()->get('signer_verified', []);
        $verified[] = $signer->id;
        $request->session()->put('signer_verified', array_unique($verified));

        return redirect()->route('firmar.multi', $token);
    }

    public function sign(Request $request, string $token)
    {
        $signer = $this->signer($token);

        if ($signer->hasSigned()) {
            return redirect()->route('firmar.multi', $token);
        }
        // Debe estar verificado si aplica
        if ($this->needsOtp($signer) && ! $this->verifiedInSession($request, $signer)) {
            return redirect()->route('firmar.multi', $token);
        }

        $data = $request->validate([
            'signed_document' => 'required|string|max:40',
            'signature'       => 'required|string',
        ]);

        $path = $this->saveSignature($data['signature']);
        if (! $path) {
            return back()->withErrors(['signature' => 'La firma no se pudo procesar. Vuelve a firmar.']);
        }

        $signer->update([
            'signed_document' => $data['signed_document'],
            'signature_path'  => $path,
            'signed_at'       => now(),
        ]);

        // Si ya firmaron todos, marca el contrato como firmado
        $contract = $signer->contract->fresh('signers');
        if ($contract->allSigned()) {
            $first = $contract->signers->firstWhere('signed_at', '!=', null);
            $contract->update([
                'contract_status'    => 'firmado',
                'signed_by_name'     => $contract->signers->pluck('name')->implode(', '),
                'signed_by_document' => $first?->signed_document,
                'signature_path'     => $first?->signature_path,
                'signed_at'          => now(),
            ]);
        }

        return redirect()->route('firmar.multi', $token)->with('signed', true);
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
        // Debe ser una imagen real (evita guardar bytes arbitrarios como .png)
        $info = @getimagesizefromstring($binary);
        if (! $info || ! in_array($info[2], [IMAGETYPE_PNG, IMAGETYPE_JPEG, IMAGETYPE_WEBP], true)) {
            return null;
        }
        $name = 'firmas/'.date('Ymd_His').'_'.bin2hex(random_bytes(6)).'.png';
        Storage::disk('public')->put($name, $binary);

        return $name;
    }
}
