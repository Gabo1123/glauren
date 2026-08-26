<?php

namespace App\Modules\Chaquetas\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Chaquetas\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SchoolController extends Controller
{
    public function index()
    {
        $schools = School::withCount('contracts')->orderBy('name')->get();

        return view('admin.schools.index', compact('schools'));
    }

    public function create()
    {
        $school = new School(['active' => true]);

        return view('admin.schools.form', compact('school'));
    }

    public function store(Request $request)
    {
        $school = School::create($this->validated($request, null));
        $this->syncAccount($request, $school);

        return redirect()->route('admin.schools.index')->with('ok', 'Colegio creado.');
    }

    public function edit(School $school)
    {
        $school->load('user');

        return view('admin.schools.form', compact('school'));
    }

    public function update(Request $request, School $school)
    {
        $school->update($this->validated($request, $school->id));
        $this->syncAccount($request, $school);

        return redirect()->route('admin.schools.index')->with('ok', 'Colegio actualizado.');
    }

    /**
     * Crea o actualiza la cuenta de acceso (usuario rol "colegio") del colegio.
     * Si se escribe un correo de acceso, crea/actualiza el usuario; la contraseña
     * solo se cambia si se escribe una nueva.
     */
    private function syncAccount(Request $request, School $school): void
    {
        $request->validate([
            'login_email'    => 'nullable|email|max:160',
            'login_password' => 'nullable|string|min:6',
        ]);

        $email = $request->input('login_email');
        if (! $email) {
            return; // sin correo de acceso no se toca la cuenta
        }

        $user = $school->user; // relación School->belongsTo(User)

        if (! $user) {
            $password = $request->input('login_password') ?: Str::random(10);
            $user = User::create([
                'name'     => $school->name,
                'email'    => $email,
                'role'     => 'colegio',
                'password' => Hash::make($password),
            ]);
            $school->update(['user_id' => $user->id]);

            return;
        }

        $user->email = $email;
        $user->name = $school->name;
        $user->role = 'colegio';
        if ($request->filled('login_password')) {
            $user->password = Hash::make($request->input('login_password'));
        }
        $user->save();
    }

    public function destroy(School $school)
    {
        $school->delete();

        return back()->with('ok', 'Colegio eliminado.');
    }

    private function validated(Request $request, ?int $id): array
    {
        $data = $request->validate([
            'name'          => 'required|string|max:160',
            'city'          => 'nullable|string|max:120',
            'contact_name'  => 'nullable|string|max:160',
            'contact_phone' => 'nullable|string|max:40',
            'contact_email' => 'nullable|email|max:160',
            'active'        => 'nullable|boolean',
            'payment_mode'  => 'nullable|in:estudiante,colegio',
        ]);
        $data['payment_mode'] = $request->input('payment_mode', 'estudiante');
        $data['slug'] = Str::slug($data['name']).($id ? '' : '-'.Str::random(4));
        $data['active'] = $request->boolean('active');

        return $data;
    }
}
