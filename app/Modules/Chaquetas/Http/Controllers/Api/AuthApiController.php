<?php

namespace App\Modules\Chaquetas\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Chaquetas\Models\ApiToken;
use App\Modules\Chaquetas\Models\Setting;
use App\Modules\Chaquetas\Models\Student;
use App\Modules\Chaquetas\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/** Inicio de sesión de la app: equipo y colegios con correo; estudiantes con documento y PIN. */
class AuthApiController extends Controller
{
    public function __construct(private WhatsAppService $wa) {}

    public function login(Request $request)
    {
        $data = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
            'device'   => 'nullable|string|max:120',
        ]);
        $user = User::where('email', mb_strtolower(trim($data['email'])))->first();
        if (! $user || ! Hash::check($data['password'], $user->password) || ! in_array($user->role, ['admin', 'colegio'], true)) {
            return response()->json(['message' => 'Correo o contraseña incorrectos.'], 422);
        }
        if ($user->role === 'colegio' && ! $user->school) {
            return response()->json(['message' => 'Tu cuenta no tiene un colegio asociado. Escríbenos.'], 422);
        }

        // Equipo con verificación en dos pasos por WhatsApp (igual que el panel web)
        if ($user->role === 'admin' && $this->twoFactorReady()) {
            $challenge = Str::random(40);
            $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            Cache::put('app2fa:'.$challenge, ['user' => $user->id, 'code' => Hash::make($code), 'tries' => 0, 'device' => $data['device'] ?? null], now()->addMinutes(5));
            $phone = $this->wa->normalize(Setting::get('admin_2fa_phone'));
            $this->wa->sendText($phone, "🔐 *G'Lauren* — Código para la app: *{$code}*\nVence en 5 minutos.");

            return response()->json(['needs_2fa' => true, 'challenge' => $challenge, 'phone_hint' => '••••'.substr((string) $phone, -4)]);
        }

        return $this->issue($user, $data['device'] ?? null);
    }

    public function verify(Request $request)
    {
        $data = $request->validate(['challenge' => 'required|string', 'code' => 'required|string']);
        $key = 'app2fa:'.$data['challenge'];
        $state = Cache::get($key);
        if (! $state) {
            return response()->json(['message' => 'El código venció. Inicia sesión de nuevo.'], 422);
        }
        if ($state['tries'] >= 5) {
            Cache::forget($key);

            return response()->json(['message' => 'Demasiados intentos. Inicia sesión de nuevo.'], 422);
        }
        if (! Hash::check(preg_replace('/\D/', '', $data['code']), $state['code'])) {
            $state['tries']++;
            Cache::put($key, $state, now()->addMinutes(5));

            return response()->json(['message' => 'Código incorrecto.'], 422);
        }
        Cache::forget($key);

        return $this->issue(User::findOrFail($state['user']), $state['device']);
    }

    /** Estudiante: documento + PIN de 4 números. La primera vez crea el PIN (pin + pin2). */
    public function student(Request $request)
    {
        $data = $request->validate([
            'document' => 'required|string|max:40',
            'pin'      => 'nullable|digits:4',
            'pin2'     => 'nullable|digits:4',
            'device'   => 'nullable|string|max:120',
        ], ['pin.digits' => 'El PIN debe ser de 4 números.']);
        $student = Student::where('document', preg_replace('/\s+/', '', $data['document']))->latest()->first();
        if (! $student) {
            return response()->json(['message' => 'No encontramos ese documento. Verifica el número o pregunta a tu colegio.'], 422);
        }
        if (! $student->pin) {
            if (empty($data['pin'])) {
                return response()->json(['needs_pin_create' => true, 'name' => $student->name]);
            }
            if (($data['pin2'] ?? null) !== $data['pin']) {
                return response()->json(['message' => 'Los PIN no coinciden.'], 422);
            }
            $student->update(['pin' => Hash::make($data['pin'])]);
        } elseif (empty($data['pin'])) {
            return response()->json(['needs_pin' => true, 'name' => $student->name]);
        } elseif (! Hash::check($data['pin'], $student->pin)) {
            return response()->json(['message' => 'PIN incorrecto. Si lo olvidaste, recupéralo en chaquetasdeprom.com/estudiante/olvide-pin'], 422);
        }

        return $this->issue($student, $data['device'] ?? null);
    }

    public function me(Request $request)
    {
        return response()->json(['user' => $this->profile($request->attributes->get('api_owner'))]);
    }

    public function logout(Request $request)
    {
        $request->attributes->get('api_token')?->delete();

        return response()->json(['ok' => true]);
    }

    private function issue(User|Student $owner, ?string $device)
    {
        return response()->json(['token' => ApiToken::issue($owner, $device), 'user' => $this->profile($owner)]);
    }

    private function profile(User|Student $o): array
    {
        if ($o instanceof Student) {
            return ['role' => 'estudiante', 'id' => $o->id, 'name' => $o->nickname ?: $o->name, 'school' => $o->contract?->school?->name];
        }

        return ['role' => $o->role, 'id' => $o->id, 'name' => $o->name, 'email' => $o->email, 'school' => $o->role === 'colegio' ? $o->school?->name : null];
    }

    private function twoFactorReady(): bool
    {
        return Setting::get('admin_2fa_enabled', '0') === '1'
            && trim((string) Setting::get('admin_2fa_phone', '')) !== ''
            && $this->wa->configured();
    }
}
