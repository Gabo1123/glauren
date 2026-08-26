<?php

namespace App\Modules\Chaquetas\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Chaquetas\Models\Contract;
use App\Modules\Chaquetas\Models\Student;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function store(Request $request, Contract $contract)
    {
        $data = $this->validated($request, $contract, null);
        $contract->students()->create($data);

        return back()->with('ok', 'Alumno agregado.');
    }

    public function edit(Contract $contract, Student $student)
    {
        abort_unless($student->contract_id === $contract->id, 404);

        return view('admin.contracts.student', compact('contract', 'student'));
    }

    public function update(Request $request, Contract $contract, Student $student)
    {
        abort_unless($student->contract_id === $contract->id, 404);
        $student->update($this->validated($request, $contract, $student->id));

        return redirect()->route('admin.contracts.show', $contract)->with('ok', 'Alumno actualizado.');
    }

    public function destroy(Contract $contract, Student $student)
    {
        abort_unless($student->contract_id === $contract->id, 404);
        $student->delete();

        return back()->with('ok', 'Alumno eliminado.');
    }

    /** Importa alumnos desde un archivo CSV (documento, nombre, sexo, apodo, talla). */
    public function import(Request $request, Contract $contract)
    {
        $request->validate(['file' => 'required|file|max:4096']);

        $lines = file($request->file('file')->getRealPath(), FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (! $lines) {
            return back()->withErrors(['file' => 'El archivo está vacío.']);
        }

        // Detecta separador (Excel Colombia suele usar ;)
        $delim = substr_count($lines[0], ';') > substr_count($lines[0], ',') ? ';' : ',';

        $created = 0;
        $skipped = 0;
        foreach ($lines as $i => $line) {
            $c = array_map('trim', str_getcsv($line, $delim));
            $doc = $c[0] ?? '';

            // Salta encabezado o filas sin documento válido
            if ($doc === '' || ! preg_match('/^\d{5,}$/', preg_replace('/\D/', '', $doc))) {
                continue;
            }
            $doc = preg_replace('/\D/', '', $doc);

            if ($contract->students()->where('document', $doc)->exists()) {
                $skipped++;
                continue;
            }

            $contract->students()->create([
                'document' => $doc,
                'name'     => $c[1] ?? 'Sin nombre',
                'gender'   => $this->normalizeGender($c[2] ?? ''),
                'nickname' => $c[3] ?? null,
                'size'     => $c[4] ?? null,
            ]);
            $created++;
        }

        return back()->with('ok', "Importación lista: $created alumno(s) agregado(s), $skipped ya existían.");
    }

    private function normalizeGender(string $g): ?string
    {
        $g = mb_strtolower(trim($g));
        if (in_array($g, ['m', 'masculino', 'hombre', 'h'], true)) {
            return 'hombre';
        }
        if (in_array($g, ['f', 'femenino', 'mujer'], true)) {
            return 'mujer';
        }

        return null;
    }

    /** Descarga una plantilla CSV de ejemplo. */
    public function template(Contract $contract)
    {
        $csv = "documento,nombre,sexo,apodo,talla\n"
            ."1010101010,Valentina Ríos,mujer,Vale,M\n"
            ."2020202020,Juan Torres,hombre,Juancho,L\n";

        return response("\xEF\xBB\xBF".$csv, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="plantilla-alumnos.csv"',
        ]);
    }

    /** Exporta los alumnos del contrato a CSV (abre en Excel). */
    public function export(Contract $contract)
    {
        $rows = [['Documento', 'Nombre', 'Sexo', 'Apodo', 'Talla', 'Pecho', 'Cintura', 'Cadera', 'Manga', 'Talle', 'Pago']];
        foreach ($contract->students()->orderBy('name')->get() as $s) {
            $rows[] = [
                $s->document, $s->name, $s->gender, $s->nickname, $s->size,
                $s->chest, $s->waist, $s->hip, $s->sleeve_length, $s->talle_length,
                $s->isPaid() ? 'Pagado' : 'Pendiente',
            ];
        }

        $csv = collect($rows)->map(fn ($r) => implode(',', array_map(fn ($v) => '"'.str_replace('"', '""', (string) $v).'"', $r)))->implode("\n");

        return response("\xEF\xBB\xBF".$csv, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="alumnos-'.$contract->code.'.csv"',
        ]);
    }

    /** Vista tipo Excel: toma de medidas de todo el curso. */
    public function measures(Contract $contract)
    {
        $contract->load(['school', 'jacketType']);
        $students = $contract->students()->orderBy('name')->get();

        return view('admin.contracts.measures', compact('contract', 'students'));
    }

    /** Guarda de una vez las medidas de todos los alumnos (grid editable). */
    public function saveMeasures(Request $request, Contract $contract)
    {
        $rows = (array) $request->input('students', []);
        $num = fn ($v) => ($v === null || $v === '') ? null : (float) str_replace(',', '.', $v);
        $count = 0;

        foreach ($rows as $id => $d) {
            $s = $contract->students()->find($id);
            if (! $s) {
                continue;
            }
            $paid = ! empty($d['paid']);

            $s->update([
                'name'           => trim($d['name'] ?? '') ?: $s->name,
                'document'       => trim($d['document'] ?? '') ?: $s->document,
                'gender'         => in_array(($d['gender'] ?? ''), ['hombre', 'mujer'], true) ? $d['gender'] : null,
                'nickname'       => trim($d['nickname'] ?? '') ?: null,
                'size'           => trim($d['size'] ?? '') ?: null,
                'chest'          => $num($d['chest'] ?? null),
                'waist'          => $num($d['waist'] ?? null),
                'hip'            => $num($d['hip'] ?? null),
                'sleeve_length'  => $num($d['sleeve_length'] ?? null),
                'talle_length'   => $num($d['talle_length'] ?? null),
                'payment_status' => $paid ? 'pagado' : 'pendiente',
                'paid_at'        => $paid ? ($s->paid_at ?? now()) : null,
            ]);
            $count++;
        }

        // Filas nuevas agregadas en la hoja (se crean como alumnos)
        $created = 0;
        foreach ((array) $request->input('new', []) as $d) {
            $name = trim($d['name'] ?? '');
            $doc  = trim($d['document'] ?? '');
            if ($name === '' && $doc === '') {
                continue; // fila vacía, se ignora
            }
            if ($doc === '') {
                continue; // sin documento no se crea
            }
            if ($contract->students()->where('document', $doc)->exists()) {
                continue; // ya existe ese documento en el contrato
            }
            $paid = ! empty($d['paid']);
            $contract->students()->create([
                'document'       => $doc,
                'name'           => $name ?: 'Sin nombre',
                'gender'         => in_array(($d['gender'] ?? ''), ['hombre', 'mujer'], true) ? $d['gender'] : null,
                'nickname'       => trim($d['nickname'] ?? '') ?: null,
                'size'           => trim($d['size'] ?? '') ?: null,
                'chest'          => $num($d['chest'] ?? null),
                'waist'          => $num($d['waist'] ?? null),
                'hip'            => $num($d['hip'] ?? null),
                'sleeve_length'  => $num($d['sleeve_length'] ?? null),
                'talle_length'   => $num($d['talle_length'] ?? null),
                'payment_status' => $paid ? 'pagado' : 'pendiente',
                'paid_at'        => $paid ? now() : null,
            ]);
            $created++;
        }

        $msg = "Medidas guardadas ($count alumno(s))";
        $msg .= $created ? ". $created alumno(s) nuevo(s) agregado(s)." : '.';

        return back()->with('ok', $msg);
    }

    /** Alterna el estado de pago (para cobros manuales: efectivo/transferencia). */
    public function togglePay(Contract $contract, Student $student)
    {
        abort_unless($student->contract_id === $contract->id, 404);
        $paid = $student->isPaid();
        $student->update([
            'payment_status' => $paid ? 'pendiente' : 'pagado',
            'paid_at'        => $paid ? null : now(),
        ]);

        return back()->with('ok', $paid ? 'Pago revertido a pendiente.' : 'Alumno marcado como pagado.');
    }

    /** Activa/desactiva los avisos por WhatsApp para este alumno. */
    public function toggleNotify(Contract $contract, Student $student)
    {
        abort_unless($student->contract_id === $contract->id, 404);
        $student->update(['notify_optin' => ! $student->notify_optin]);

        return back()->with('ok', $student->notify_optin ? 'Este alumno SÍ recibirá avisos por WhatsApp.' : 'Avisos desactivados para este alumno.');
    }

    private function validated(Request $request, Contract $contract, ?int $id): array
    {
        $unique = 'unique:students,document,'.($id ?? 'NULL').',id,contract_id,'.$contract->id;

        return $request->validate([
            'document'      => 'required|string|max:40|'.$unique,
            'name'          => 'required|string|max:160',
            'gender'        => 'nullable|in:hombre,mujer',
            'nickname'      => 'nullable|string|max:60',
            'size'          => 'nullable|string|max:20',
            'talle_length'  => 'nullable|numeric',
            'sleeve_length' => 'nullable|numeric',
            'chest'         => 'nullable|numeric',
            'waist'         => 'nullable|numeric',
            'hip'           => 'nullable|numeric',
            'payment_status' => 'nullable|in:pendiente,pagado',
            'amount'        => 'nullable|numeric|min:0',
        ]);
    }
}
