<?php

namespace App\Modules\Chaquetas\Http\Controllers\Foto;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin(Request $request)
    {
        if (Auth::check() && (Auth::user()->role ?? '') === 'foto') {
            return redirect()->route('foto.dashboard');
        }

        return view('foto.login');
    }

    public function login(Request $request)
    {
        $cred = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt([...$cred, 'role' => 'foto'], $request->boolean('remember'))) {
            $request->session()->regenerate();

            return redirect()->intended(route('foto.dashboard'));
        }

        return back()
            ->withErrors(['email' => 'Correo o contraseña incorrectos.'])
            ->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('foto.login');
    }
}
