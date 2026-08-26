<?php

namespace App\Modules\Chaquetas\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Chaquetas\Models\JacketType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class JacketTypeController extends Controller
{
    public function index()
    {
        $types = JacketType::orderBy('sort')->get();

        return view('admin.types.index', compact('types'));
    }

    public function create()
    {
        $type = new JacketType(['icon' => '🧥', 'base_price' => 0, 'patch_price' => 0, 'active' => true, 'sort' => 0]);

        return view('admin.types.edit', compact('type'));
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request, true);
        $type = new JacketType();
        $type->slug = $data['slug'];
        $this->fillAndSave($type, $request, $data);

        return redirect()->route('admin.types.index')->with('ok', 'Tipo creado.');
    }

    public function edit(JacketType $type)
    {
        return view('admin.types.edit', compact('type'));
    }

    public function update(Request $request, JacketType $type)
    {
        $data = $this->validateData($request, false);
        $this->fillAndSave($type, $request, $data);

        return redirect()->route('admin.types.index')->with('ok', 'Tipo actualizado.');
    }

    /** Guarda la lista de materiales editada en la ficha de producción del contrato. */
    public function saveComponents(Request $request, JacketType $type)
    {
        $names = (array) $request->input('name', []);
        $qtys  = (array) $request->input('qty', []);
        $units = (array) $request->input('unit', []);
        $costs = (array) $request->input('cost', []);

        $comps = [];
        foreach ($names as $i => $n) {
            $n = trim((string) $n);
            if ($n === '') {
                continue;
            }
            $unit = $units[$i] ?? 'u';
            if (! in_array($unit, ['m', 'cm', 'u'], true)) {
                $unit = 'u';
            }
            $comps[] = [
                'name' => $n,
                'qty'  => (float) str_replace(',', '.', (string) ($qtys[$i] ?? 0)),
                'unit' => $unit,
                'cost' => (float) preg_replace('/[^\d.]/', '', (string) ($costs[$i] ?? 0)),
            ];
        }

        $type->components = $comps ?: null;
        $type->save();

        return back()->with('ok', 'Materiales del modelo '.$type->name.' actualizados.');
    }

    private function validateData(Request $request, bool $creating): array
    {
        $maxKb = ((int) config('chaquetas.max_model_mb', 50)) * 1024;

        return $request->validate([
            'name'        => 'required|string|max:120',
            'slug'        => $creating ? 'required|alpha_dash|max:120|unique:jacket_types,slug' : 'nullable',
            'description' => 'nullable|string|max:500',
            'category'    => 'nullable|string|max:60',
            'icon'        => 'nullable|string|max:16',
            'base_price'  => 'required|numeric|min:0',
            'patch_price' => 'required|numeric|min:0',
            'sort'            => 'nullable|integer',
            'components_text' => 'nullable|string|max:4000',
            'model'           => "nullable|file|mimes:glb,gltf|max:{$maxKb}",
            'default_view'    => 'nullable|in:modelo,parametrico',
        ]);
    }

    /** Convierte el textarea ("Nombre = cantidad unidad" por línea) en array de materiales. */
    private function parseComponents(?string $text): array
    {
        $out = [];
        foreach (preg_split('/\r\n|\r|\n/', (string) $text) as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            // "Tela cuerpo = 1.2 m @ 18000"  |  "Botones = 5 u"  |  "Nombre = 3"
            if (preg_match('/^(.+?)\s*[=:x×]?\s*(\d+(?:[.,]\d+)?)\s*([a-zA-Zª]+)?\s*(?:@\s*\$?\s*([\d.,]+))?\s*$/u', $line, $m)) {
                $out[] = [
                    'name' => trim($m[1]),
                    'qty'  => (float) str_replace(',', '.', $m[2]),
                    'unit' => $this->normalizeUnit($m[3] ?? ''),
                    'cost' => isset($m[4]) && $m[4] !== '' ? (float) preg_replace('/[^\d]/', '', $m[4]) : 0,
                ];
            } else {
                $out[] = ['name' => $line, 'qty' => 1, 'unit' => 'u', 'cost' => 0];
            }
        }

        return $out;
    }

    private function normalizeUnit(string $u): string
    {
        $u = mb_strtolower(trim($u));
        if (in_array($u, ['m', 'mt', 'mts', 'metro', 'metros'], true)) {
            return 'm';
        }
        if (in_array($u, ['cm', 'centimetro', 'centimetros'], true)) {
            return 'cm';
        }

        return 'u'; // unidades por defecto
    }

    private function fillAndSave(JacketType $type, Request $request, array $data): void
    {
        $type->fill([
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
            'category'    => $data['category'] ?? null,
            'icon'        => $data['icon'] ?: '🧥',
            'base_price'  => $data['base_price'],
            'patch_price' => $data['patch_price'],
            'active'      => $request->boolean('active'),
            'sort'        => $data['sort'] ?? $type->sort ?? 0,
            'default_view' => $data['default_view'] ?? $type->default_view ?? 'modelo',
        ]);

        if ($request->has('components_text')) {
            $type->components = $this->parseComponents($request->input('components_text')) ?: null;
        }

        if ($request->hasFile('model')) {
            $ext = $request->file('model')->getClientOriginalExtension();
            $filename = $type->slug.'.'.$ext;
            Storage::disk('public')->putFileAs('modelos', $request->file('model'), $filename);
            $type->model_path = 'modelos/'.$filename;
        }

        $type->save();
    }
}
