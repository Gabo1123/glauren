<?php

namespace App\Modules\Chaquetas\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Chaquetas\Models\Setting;
use App\Modules\Chaquetas\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function __construct(private WhatsAppService $wa) {}

    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.login');
    }

    public function login(Request $request)
    {
        $cred = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        // Verifica credenciales SIN iniciar sesión todavía
        $user = User::where('email', $cred['email'])->first();
        if (! $user || ! Hash::check($cred['password'], $user->password)) {
            return back()->withErrors(['email' => 'Credenciales incorrectas.'])->onlyInput('email');
        }

        // ¿2FA por WhatsApp activo y configurado?
        if ($this->twoFactorReady()) {
            return $this->startTwoFactor($request, $user);
        }

        // Sin 2FA: login normal
        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()->intended(route('admin.dashboard'));
    }

    /** Muestra la pantalla para ingresar el código. */
    public function showTwoFactor(Request $request)
    {
        if (! $request->session()->has('2fa.user_id')) {
            return redirect()->route('admin.login');
        }

        return view('admin.2fa', [
            'phoneHint' => $request->session()->get('2fa.phone_hint'),
        ]);
    }

    /** Verifica el código ingresado. */
    public function verifyTwoFactor(Request $request)
    {
        $data = $request->validate(['code' => 'required|string']);
        $s = $request->session();

        if (! $s->has('2fa.user_id')) {
            return redirect()->route('admin.login');
        }

        // Expiración (5 min)
        if (now()->timestamp > $s->get('2fa.expires', 0)) {
            $s->forget('2fa');

            return redirect()->route('admin.login')->withErrors(['email' => 'El código expiró. Inicia sesión de nuevo.']);
        }

        // Límite de intentos
        $tries = (int) $s->get('2fa.tries', 0);
        if ($tries >= 5) {
            $s->forget('2fa');

            return redirect()->route('admin.login')->withErrors(['email' => 'Demasiados intentos. Inicia sesión de nuevo.']);
        }

        if (! Hash::check(preg_replace('/\D/', '', $data['code']), $s->get('2fa.code'))) {
            $s->put('2fa.tries', $tries + 1);

            return back()->withErrors(['code' => 'Código incorrecto. Te quedan '.(5 - $tries - 1).' intentos.']);
        }

        // Código correcto → inicia sesión
        $user = User::find($s->get('2fa.user_id'));
        $remember = (bool) $s->get('2fa.remember');
        $s->forget('2fa');

        Auth::login($user, $remember);
        $request->session()->regenerate();

        return redirect()->intended(route('admin.dashboard'));
    }

    /** Reenvía el código. */
    public function resendTwoFactor(Request $request)
    {
        if (! $request->session()->has('2fa.user_id')) {
            return redirect()->route('admin.login');
        }
        $user = User::find($request->session()->get('2fa.user_id'));

        return $this->startTwoFactor($request, $user, true);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }

    /** ¿Está listo el 2FA por WhatsApp? */
    private function twoFactorReady(): bool
    {
        return Setting::get('admin_2fa_enabled', '0') === '1'
            && trim((string) Setting::get('admin_2fa_phone', '')) !== ''
            && $this->wa->configured();
    }

    /** Genera y envía el código, y guarda el estado pendiente en sesión. */
    private function startTwoFactor(Request $request, User $user, bool $resend = false)
    {
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $phone = $this->wa->normalize(Setting::get('admin_2fa_phone'));

        $request->session()->put('2fa', [
            'user_id'    => $user->id,
            'code'       => Hash::make($code),
            'expires'    => now()->addMinutes(5)->timestamp,
            'tries'      => 0,
            'remember'   => $request->boolean('remember'),
            'phone_hint' => $this->maskPhone($phone),
        ]);

        $brand = Setting::get('brand_name', 'Glauren');
        $msg = "🔐 *{$brand}* — Código de acceso al panel: *{$code}*\nVence en 5 minutos. Si no fuiste tú, cambia tu contraseña.";
        $this->wa->sendText($phone, $msg);

        return redirect()->route('admin.2fa')
            ->with('ok', $resend ? 'Te enviamos un nuevo código por WhatsApp.' : 'Te enviamos un código por WhatsApp.');
    }

    private function maskPhone(?string $phone): string
    {
        $p = (string) $phone;

        return strlen($p) > 4 ? str_repeat('•', max(0, strlen($p) - 4)).substr($p, -4) : $p;
    }
}
