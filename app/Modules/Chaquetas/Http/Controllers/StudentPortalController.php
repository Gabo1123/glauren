<?php

namespace App\Modules\Chaquetas\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Chaquetas\Models\Setting;
use App\Modules\Chaquetas\Models\Student;
use App\Modules\Chaquetas\Models\StudentPost;
use App\Modules\Chaquetas\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class StudentPortalController extends Controller
{
    public function __construct(private WhatsAppService $wa) {}

    /** Estudiante en sesión, o null. */
    private function current(Request $request): ?Student
    {
        $id = $request->session()->get('student_id');

        return $id ? Student::with('contract.school')->find($id) : null;
    }

    /** Pantalla de acceso (paso 1: documento). */
    public function showLogin(Request $request)
    {
        if ($this->current($request)) {
            return redirect()->route('student.home');
        }

        return view('chaquetas.portal.login');
    }

    /** Si alguien entra por GET a un paso (o refresca), lo retomamos sin error 405. */
    public function resume(Request $request)
    {
        if ($this->current($request)) {
            return redirect()->route('student.home');
        }
        $doc = $request->session()->get('login_document');
        $student = $doc ? Student::where('document', $doc)->latest()->first() : null;
        if ($student) {
            return view('chaquetas.portal.login', ['step' => $student->pin ? 'pin' : 'create', 'student' => $student]);
        }

        return redirect()->route('student.login');
    }

    /** Paso 1: valida documento y decide si crear PIN o pedirlo. */
    public function checkDocument(Request $request)
    {
        $data = $request->validate(['document' => 'required|string|max:40']);
        $student = Student::where('document', trim($data['document']))->latest()->first();

        if (! $student) {
            return back()->withErrors(['document' => 'No encontramos ese documento. Verifica el número o pregunta a tu colegio.'])->withInput();
        }

        $request->session()->put('login_document', $student->document);

        return view('chaquetas.portal.login', [
            'step'    => $student->pin ? 'pin' : 'create',
            'student' => $student,
        ]);
    }

    /** Paso 2: crea el PIN la primera vez, o verifica el existente. */
    public function auth(Request $request)
    {
        $doc = $request->session()->get('login_document');
        $student = $doc ? Student::where('document', $doc)->latest()->first() : null;
        if (! $student) {
            return redirect()->route('student.login');
        }

        if (! $student->pin) {
            $data = $request->validate(['pin' => 'required|digits:4', 'pin2' => 'required|same:pin'], [
                'pin.digits' => 'El PIN debe ser de 4 números.',
                'pin2.same'  => 'Los PIN no coinciden.',
            ]);
            $student->update(['pin' => Hash::make($data['pin'])]);
        } else {
            $data = $request->validate(['pin' => 'required|digits:4']);
            if (! Hash::check($data['pin'], $student->pin)) {
                return view('chaquetas.portal.login', ['step' => 'pin', 'student' => $student])
                    ->withErrors(['pin' => 'PIN incorrecto.']);
            }
        }

        $request->session()->forget('login_document');
        $request->session()->put('student_id', $student->id);

        return redirect()->route('student.home');
    }

    public function logout(Request $request)
    {
        $request->session()->forget('student_id');

        return redirect()->route('student.login');
    }

    /** Olvidé mi PIN → envía un código al WhatsApp registrado. */
    public function forgotPin(Request $request)
    {
        $doc = $request->session()->get('login_document');
        $student = $doc ? Student::where('document', $doc)->latest()->first() : null;
        if (! $student) {
            return redirect()->route('student.login');
        }

        $phone = $this->wa->normalize($student->phone);
        if (! $phone || ! $this->wa->configured()) {
            return view('chaquetas.portal.login', ['step' => 'pin', 'student' => $student])
                ->withErrors(['pin' => 'No tienes un WhatsApp registrado. Pídele a tu colegio o asesor que lo registre para recuperar tu PIN.']);
        }

        $code = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        $request->session()->put('pin_reset', [
            'code' => Hash::make($code), 'expires' => now()->addMinutes(10)->timestamp, 'tries' => 0,
        ]);

        $brand = Setting::get('brand_name', 'Glauren');
        $this->wa->sendText($phone, "🔐 {$brand} — Tu código para restablecer el PIN es: {$code}\nVence en 10 minutos.");

        $hint = strlen($phone) > 4 ? str_repeat('•', strlen($phone) - 4).substr($phone, -4) : $phone;

        return view('chaquetas.portal.login', ['step' => 'reset', 'student' => $student, 'phoneHint' => $hint])
            ->with('ok', 'Te enviamos un código por WhatsApp.');
    }

    /** Verifica el código y define un nuevo PIN. */
    public function resetPin(Request $request)
    {
        $doc = $request->session()->get('login_document');
        $student = $doc ? Student::where('document', $doc)->latest()->first() : null;
        $reset = $request->session()->get('pin_reset');
        if (! $student || ! $reset) {
            return redirect()->route('student.login');
        }

        if (now()->timestamp > ($reset['expires'] ?? 0) || ($reset['tries'] ?? 0) >= 5) {
            $request->session()->forget('pin_reset');

            return redirect()->route('student.login')->withErrors(['document' => 'El código expiró. Inténtalo de nuevo.']);
        }

        $data = $request->validate([
            'code' => 'required|digits:4',
            'pin'  => 'required|digits:4',
            'pin2' => 'required|same:pin',
        ], ['pin.digits' => 'El PIN debe ser de 4 números.', 'pin2.same' => 'Los PIN no coinciden.']);

        if (! Hash::check($data['code'], $reset['code'])) {
            $reset['tries'] = ($reset['tries'] ?? 0) + 1;
            $request->session()->put('pin_reset', $reset);

            return view('chaquetas.portal.login', ['step' => 'reset', 'student' => $student])
                ->withErrors(['code' => 'Código incorrecto.']);
        }

        $student->update(['pin' => Hash::make($data['pin'])]);
        $request->session()->forget(['pin_reset', 'login_document']);
        $request->session()->put('student_id', $student->id);

        return redirect()->route('student.home')->with('ok', 'PIN actualizado. ¡Bienvenido!');
    }

    /** Muro del curso + perfil. */
    public function home(Request $request)
    {
        $student = $this->current($request);
        if (! $student) {
            return redirect()->route('student.login');
        }

        $student->loadMissing('contract.school', 'contract.jacketType', 'contract.students');

        // Muro: publicaciones del mismo contrato (curso) + likes + comentarios
        $posts = StudentPost::with('student', 'comments')
            ->withCount('likes')
            ->where('contract_id', $student->contract_id)
            ->where('hidden', false)
            ->latest()->limit(80)->get();

        // Qué publicaciones ya likeó este estudiante
        $likedIds = \App\Modules\Chaquetas\Models\StudentPostLike::where('student_id', $student->id)
            ->pluck('student_post_id')->all();

        // Compañeros del curso
        $classmates = Student::where('contract_id', $student->contract_id)
            ->where('id', '!=', $student->id)->get();

        // Votación de diseño: se muestra si está activa O si se decide mostrar resultados
        $canVote = (bool) ($student->contract?->voting_enabled);
        $showResults = (bool) ($student->contract?->voting_show_results);
        $designOptions = ($canVote || $showResults)
            ? \App\Modules\Chaquetas\Models\DesignOption::where('contract_id', $student->contract_id)
                ->where('active', true)->withCount('votes')->orderBy('sort')->orderBy('id')->get()
            : collect();
        $myVote = \App\Modules\Chaquetas\Models\DesignVote::where('contract_id', $student->contract_id)
            ->where('student_id', $student->id)->value('design_option_id');
        $totalDesignVotes = $designOptions->sum('votes_count');

        // Diseño final aprobado que sube el equipo (fotos con etapa 'diseno')
        $finalDesign = \App\Modules\Chaquetas\Models\ContractPhoto::where('contract_id', $student->contract_id)
            ->where('phase', 'diseno')->latest()->get();

        return view('chaquetas.portal.home', compact('student', 'posts', 'classmates', 'likedIds', 'designOptions', 'myVote', 'totalDesignVotes', 'showResults', 'canVote', 'finalDesign'));
    }

    /** Comentar una publicación del muro. */
    public function comment(Request $request, StudentPost $post)
    {
        $student = $this->current($request);
        if (! $student || $post->contract_id !== $student->contract_id) {
            abort(403);
        }
        $data = $request->validate(['body' => 'required|string|max:400']);
        \App\Modules\Chaquetas\Models\StudentPostComment::create([
            'student_post_id' => $post->id, 'student_id' => $student->id, 'body' => $data['body'],
        ]);

        return back()->with('ok', 'Comentario publicado.');
    }

    /** Anuario digital del curso. */
    public function yearbook(Request $request)
    {
        $student = $this->current($request);
        if (! $student) {
            return redirect()->route('student.login');
        }
        $student->loadMissing('contract.school');

        $classmates = Student::where('contract_id', $student->contract_id)->orderBy('name')->get();
        // Álbum: todas las fotos publicadas en el muro del curso
        $albumPhotos = StudentPost::where('contract_id', $student->contract_id)
            ->where('hidden', false)->whereNotNull('photo')->latest()->get();

        return view('chaquetas.portal.yearbook', compact('student', 'classmates', 'albumPhotos'));
    }

    /** El estudiante vota por un diseño. */
    public function voteDesign(Request $request, \App\Modules\Chaquetas\Models\DesignOption $option)
    {
        $student = $this->current($request);
        if (! $student || $option->contract_id !== $student->contract_id) {
            abort(403);
        }
        if (! $student->contract?->voting_enabled) {
            return back()->withErrors(['vote' => 'La votación está cerrada.']);
        }

        \App\Modules\Chaquetas\Models\DesignVote::updateOrCreate(
            ['contract_id' => $student->contract_id, 'student_id' => $student->id],
            ['design_option_id' => $option->id]
        );

        return back()->with('ok', '¡Tu voto quedó registrado! 🗳️');
    }

    /** Dar / quitar "me gusta" a una publicación. */
    public function toggleLike(Request $request, StudentPost $post)
    {
        $student = $this->current($request);
        if (! $student || $post->contract_id !== $student->contract_id) {
            abort(403);
        }

        $like = \App\Modules\Chaquetas\Models\StudentPostLike::where('student_post_id', $post->id)
            ->where('student_id', $student->id)->first();

        if ($like) {
            $like->delete();
        } else {
            \App\Modules\Chaquetas\Models\StudentPostLike::create([
                'student_post_id' => $post->id, 'student_id' => $student->id,
            ]);
        }

        return back();
    }

    /** Actualiza perfil (apodo, bio, foto). */
    public function updateProfile(Request $request)
    {
        $student = $this->current($request);
        if (! $student) {
            return redirect()->route('student.login');
        }

        $data = $request->validate([
            'nickname' => 'nullable|string|max:60',
            'bio'      => 'nullable|string|max:200',
            'photo'    => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'mimetypes:image/jpeg,image/png,image/webp', 'max:4096', 'dimensions:max_width=6000,max_height=6000'],
        ]);

        $update = ['nickname' => $data['nickname'] ?? $student->nickname, 'bio' => $data['bio'] ?? null];
        if ($request->hasFile('photo')) {
            $safe = $this->storeSafeImage($request->file('photo'), 'estudiantes');
            if (! $safe) {
                return back()->withErrors(['photo' => 'La imagen no es válida. Sube una foto JPG, PNG o WEBP.']);
            }
            if ($student->photo) {
                Storage::disk('public')->delete($student->photo);
            }
            $update['photo'] = $safe;
        }
        $student->update($update);

        return back()->with('ok', 'Perfil actualizado.');
    }

    /** Publica en el muro (texto y/o foto). */
    public function post(Request $request)
    {
        $student = $this->current($request);
        if (! $student) {
            return redirect()->route('student.login');
        }

        $data = $request->validate([
            'body'  => 'nullable|string|max:500',
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'mimetypes:image/jpeg,image/png,image/webp', 'max:4096', 'dimensions:max_width=6000,max_height=6000'],
        ]);
        if (empty($data['body']) && ! $request->hasFile('photo')) {
            return back()->withErrors(['body' => 'Escribe algo o sube una foto.']);
        }

        $photo = null;
        if ($request->hasFile('photo')) {
            $photo = $this->storeSafeImage($request->file('photo'), 'muro');
            if (! $photo) {
                return back()->withErrors(['photo' => 'La imagen no es válida. Sube una foto JPG, PNG o WEBP.']);
            }
        }

        StudentPost::create([
            'student_id'  => $student->id,
            'contract_id' => $student->contract_id,
            'body'        => $data['body'] ?? null,
            'photo'       => $photo,
        ]);

        return back()->with('ok', '¡Publicado!');
    }

    /**
     * Guarda una imagen de forma SEGURA: verifica que sea imagen real, la RE-GENERA
     * con GD (elimina metadatos EXIF y cualquier payload/script oculto), la reduce si
     * es enorme y la guarda con un nombre aleatorio. Devuelve la ruta o null si falla.
     */
    private function storeSafeImage(\Illuminate\Http\UploadedFile $file, string $dir): ?string
    {
        $path = $file->getRealPath();
        $info = @getimagesize($path);
        // Debe ser un tipo de imagen permitido de verdad (no solo por extensión)
        if (! $info || ! in_array($info[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP], true)) {
            return null;
        }

        $img = @imagecreatefromstring((string) file_get_contents($path));
        if (! $img) {
            return null;
        }

        // Reduce si supera 1600px (evita imágenes gigantes)
        $w = imagesx($img);
        $h = imagesy($img);
        $max = 1600;
        if ($w > $max || $h > $max) {
            $r = min($max / $w, $max / $h);
            $nw = max(1, (int) ($w * $r));
            $nh = max(1, (int) ($h * $r));
            $dst = imagecreatetruecolor($nw, $nh);
            imagealphablending($dst, false);
            imagesavealpha($dst, true);
            imagecopyresampled($dst, $img, 0, 0, 0, 0, $nw, $nh, $w, $h);
            imagedestroy($img);
            $img = $dst;
        }

        $hasAlpha = in_array($info[2], [IMAGETYPE_PNG, IMAGETYPE_WEBP], true);
        $ext = $hasAlpha ? 'png' : 'jpg';
        $name = trim($dir, '/').'/'.date('Ymd_His').'_'.bin2hex(random_bytes(8)).'.'.$ext;

        Storage::disk('public')->makeDirectory(trim($dir, '/'));
        $full = Storage::disk('public')->path($name);

        $ok = $hasAlpha
            ? (imagealphablending($img, false) && imagesavealpha($img, true) && imagepng($img, $full, 6))
            : imagejpeg($img, $full, 82);
        imagedestroy($img);

        return $ok ? $name : null;
    }

    /** Borra una publicación propia. */
    public function deletePost(Request $request, StudentPost $post)
    {
        $student = $this->current($request);
        if (! $student || $post->student_id !== $student->id) {
            abort(403);
        }
        if ($post->photo) {
            Storage::disk('public')->delete($post->photo);
        }
        $post->delete();

        return back()->with('ok', 'Publicación eliminada.');
    }
}
