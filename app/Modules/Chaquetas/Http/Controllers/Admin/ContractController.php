<?php

namespace App\Modules\Chaquetas\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Chaquetas\Models\Contract;
use App\Modules\Chaquetas\Models\JacketType;
use App\Modules\Chaquetas\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ContractController extends Controller
{
    public function index()
    {
        $contracts = Contract::with('school', 'jacketType')->withCount('students')->latest()->get();

        return view('admin.contracts.index', compact('contracts'));
    }

    public function create()
    {
        $contract = new Contract(['year' => (int) date('Y'), 'production_status' => 'cotizado']);

        return view('admin.contracts.form', $this->formData($contract));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request, null);
        $data['code'] = $data['code'] ?: $this->nextCode();
        $contract = Contract::create($data);

        return redirect()->route('admin.contracts.show', $contract)->with('ok', 'Contrato creado. Ahora agrega los alumnos.');
    }

    public function show(Contract $contract)
    {
        $contract->load('school', 'jacketType', 'students', 'photos', 'signers');

        // Resumen financiero del contrato
        $unit = (float) ($contract->price_per_unit ?? 0);
        $fin = [
            'expected' => $contract->students->sum(fn ($s) => (float) ($s->amount ?? $unit)),
            'received' => $contract->students->where('payment_status', 'pagado')->sum(fn ($s) => (float) ($s->amount ?? $unit)),
            'expenses' => (float) \App\Modules\Chaquetas\Models\Expense::where('contract_id', $contract->id)->sum('amount'),
        ];
        $fin['profit'] = $fin['expected'] - $fin['expenses'];
        $fin['margin'] = $fin['expected'] > 0 ? round($fin['profit'] / $fin['expected'] * 100) : 0;
        $fin['pending'] = $fin['expected'] - $fin['received'];

        // Mensajes con el colegio
        $messages = \App\Modules\Chaquetas\Models\ContractMessage::where('contract_id', $contract->id)->orderBy('id')->get();
        \App\Modules\Chaquetas\Models\ContractMessage::where('contract_id', $contract->id)->where('sender', 'colegio')->where('read', false)->update(['read' => true]);

        // Votación de diseño (opciones + conteo)
        $designOptions = \App\Modules\Chaquetas\Models\DesignOption::where('contract_id', $contract->id)
            ->withCount('votes')->orderBy('sort')->orderBy('id')->get();
        $totalVotes = $designOptions->sum('votes_count');

        return view('admin.contracts.show', ['contract' => $contract, 'stages' => Contract::STAGES, 'fin' => $fin, 'messages' => $messages, 'designOptions' => $designOptions, 'totalVotes' => $totalVotes]);
    }

    /** Agrega un firmante (acudiente) al contrato. */
    public function addSigner(Request $request, Contract $contract)
    {
        $data = $request->validate([
            'name'  => 'required|string|max:160',
            'role'  => 'nullable|string|max:40',
            'phone' => 'nullable|string|max:40',
            'email' => 'nullable|email|max:160',
        ]);
        $contract->signers()->create($data);

        return back()->with('ok', 'Firmante agregado. Envíale su enlace para firmar.');
    }

    public function deleteSigner(Contract $contract, \App\Modules\Chaquetas\Models\ContractSigner $signer)
    {
        abort_unless($signer->contract_id === $contract->id, 404);
        if ($signer->signature_path) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($signer->signature_path);
        }
        $signer->delete();

        return back()->with('ok', 'Firmante eliminado.');
    }

    /** Agrega una opción de diseño para que el curso vote. */
    public function addDesignOption(Request $request, Contract $contract)
    {
        $request->validate([
            'label' => 'nullable|string|max:80',
            'image' => 'required|image|mimes:jpg,jpeg,png,webp|max:6144',
        ]);
        $path = $request->file('image')->store('diseno-opciones', 'public');
        \App\Modules\Chaquetas\Models\DesignOption::create([
            'contract_id' => $contract->id,
            'label'       => $request->input('label') ?: 'Opción',
            'image_path'  => $path,
        ]);

        return back()->with('ok', 'Opción de diseño agregada para votación.');
    }

    /** Activa/desactiva la votación y si se muestra la ganadora. */
    public function saveVotingSettings(Request $request, Contract $contract)
    {
        $contract->update([
            'voting_enabled'      => $request->boolean('voting_enabled'),
            'voting_show_results' => $request->boolean('voting_show_results'),
        ]);

        return back()->with('ok', 'Ajustes de votación guardados.');
    }

    public function deleteDesignOption(Contract $contract, \App\Modules\Chaquetas\Models\DesignOption $option)
    {
        abort_unless($option->contract_id === $contract->id, 404);
        if ($option->image_path) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($option->image_path);
        }
        \App\Modules\Chaquetas\Models\DesignVote::where('design_option_id', $option->id)->delete();
        $option->delete();

        return back()->with('ok', 'Opción eliminada.');
    }

    /** El equipo Glauren responde al colegio. */
    public function reply(Request $request, Contract $contract)
    {
        $data = $request->validate(['body' => 'required|string|max:1000']);
        \App\Modules\Chaquetas\Models\ContractMessage::create([
            'contract_id' => $contract->id, 'sender' => 'glauren', 'body' => $data['body'],
        ]);

        return back()->with('ok', 'Respuesta enviada al colegio.');
    }

    public function edit(Contract $contract)
    {
        return view('admin.contracts.form', $this->formData($contract));
    }

    public function update(Request $request, Contract $contract)
    {
        $contract->update($this->validated($request, $contract->id));

        return redirect()->route('admin.contracts.show', $contract)->with('ok', 'Contrato actualizado.');
    }

    public function updateStatus(Request $request, Contract $contract)
    {
        $data = $request->validate(['production_status' => 'required|in:'.implode(',', array_keys(Contract::STAGES))]);
        $contract->update($data);

        return back()->with('ok', 'Estado de fabricación actualizado.');
    }

    public function destroy(Contract $contract)
    {
        $contract->delete();

        return redirect()->route('admin.contracts.index')->with('ok', 'Contrato eliminado.');
    }

    /** Editor del texto del contrato (genera un borrador si está vacío) */
    public function contract(Contract $contract)
    {
        $contract->load('school');
        if (! $contract->terms) {
            $contract->terms = $this->defaultTerms($contract);
        }

        return view('admin.contracts.contrato', compact('contract'));
    }

    public function saveTerms(Request $request, Contract $contract)
    {
        $contract->update(['terms' => $request->input('terms')]);

        return back()->with('ok', 'Contrato guardado.');
    }

    /** Deja el contrato listo para firmar y genera el enlace único */
    public function send(Contract $contract)
    {
        if (! $contract->sign_token) {
            $contract->sign_token = Str::random(48);
        }
        if (! $contract->terms) {
            $contract->terms = $this->defaultTerms($contract->loadMissing('school'));
        }
        $contract->contract_status = 'enviado';
        $contract->sent_at = now();
        $contract->save();

        return back()->with('ok', 'Contrato listo para firmar. Comparte el enlace con el colegio.');
    }

    /** Borrador del contrato con la plantilla de Glauren + datos del contrato */
    private function defaultTerms(Contract $c): string
    {
        $colegio = e($c->school?->name ?? '________');
        $anio = $c->year ?: date('Y');
        $numero = e($c->code);
        $valor = $c->price_per_unit ? '$'.number_format((float) $c->price_per_unit, 0, ',', '.') : '$________';

        return <<<HTML
<h3>CONTRATO DE PRESTACIÓN DE SERVICIOS N.º {$numero}</h3>
<p>Entre los suscritos a saber: por una parte, <b>Nancy Lorena Gómez Muñoz</b>, mayor de edad, vecina y residente en Bogotá, identificada con cédula de ciudadanía N.º <b>1.033.779.133-6</b>, quien actúa a nombre propio como comerciante (G'Lauren) y para todos los efectos se llamará <b>EL CONTRATISTA</b>; y por la otra, el/los representante(s) del <b>Grado 11 – Promoción {$anio}</b> del <b>Colegio {$colegio}</b>, quien(es) para todos los efectos se llamará(n) <b>EL CONTRATANTE</b>. Acuerdan suscribir el presente contrato:</p>

<h4>CLÁUSULA PRIMERA — OBJETO</h4>
<p>Diseño y confección de [EDITA: cantidad] chaquetas y [EDITA: cantidad] busos para el grado once del Colegio {$colegio}.</p>

<h4>CLÁUSULA SEGUNDA — VALOR</h4>
<p>Valor por unidad (combo): <b>{$valor}</b>. Valor total del contrato: [EDITA: valor total en números y letras].</p>

<h4>CLÁUSULA TERCERA — FORMA DE PAGO</h4>
<p>50% al inicio y firma del contrato, y el 50% restante un (1) día antes de la entrega formal. Pago a cuenta de ahorros Bancolombia N.º 91201212184 a nombre de Lorena Gómez Muñoz, CC 1.033.779.133.</p>

<h4>CLÁUSULA CUARTA — DURACIÓN</h4>
<p>Treinta y cinco (35) días hábiles a partir de la firma, la consignación del primer 50% y la entrega de diseños y nombres de los bordados.</p>

<h4>CLÁUSULA QUINTA — ESPECIFICACIONES DE LAS PRENDAS</h4>
<p><b>Chaqueta:</b> tela [EDITA], contorno [EDITA color], mangas [EDITA color]; bordados: frente, manga derecha (logo), espalda (número y nombre); bolsillos; <b>pretina y puños</b> [EDITA: material y líneas]; cremallera [EDITA marca].<br>
<b>Buso:</b> tela [EDITA], contorno [EDITA], mangas [EDITA]; bordados frente y espalda; pretina y puños del mismo material del contorno.</p>
<p><b>Garantía:</b> cubre exclusivamente defectos de fábrica en broches, cremalleras y costuras durante el año escolar. No cubre el material (adquirido de terceros), mal uso, lavado inadecuado, desgaste o motas.</p>

<h4>CLÁUSULA SEXTA — OBLIGACIONES DEL CONTRATANTE</h4>
<p>Realizar los pagos pactados y enviar oportunamente la información requerida (imágenes de bordados, fuentes, tallas, etc.).</p>

<h4>CLÁUSULA SÉPTIMA — AUTORIZACIÓN DE USO DE IMAGEN</h4>
<p>EL CONTRATANTE autoriza a G'Lauren para captar, reproducir y utilizar imágenes, fotografías y videos relacionados con la entrega de las prendas, con fines publicitarios en redes y página web, sin compensación económica y con vigencia de quince (15) años a partir de la firma.</p>

<h4>CLÁUSULA OCTAVA — CONFIDENCIALIDAD</h4>
<p>EL CONTRATISTA guardará la confidencialidad de los datos e información registrada y no los divulgará ni distribuirá a terceros.</p>

<h4>CLÁUSULA NOVENA — SOLUCIÓN DE CONFLICTOS</h4>
<p>Las diferencias se resolverán mediante mecanismos alternativos (conciliación) y, en su defecto, por tribunal de arbitramento de la Cámara de Comercio de Bogotá, conforme a la Ley 1563 de 2012, en español, en Bogotá D.C.</p>

<h4>CLÁUSULA DÉCIMA — CLÁUSULA PENAL</h4>
<p>En caso de incumplimiento, la parte incumplida pagará a la cumplida una suma equivalente al 5% del valor total del contrato.</p>

<h4>CLÁUSULA UNDÉCIMA — PERFECCIONAMIENTO</h4>
<p>El presente contrato se entiende perfeccionado con las firmas de las partes y podrá firmarse de manera digitalizada. Notificaciones: Cl 48k 5a-27 / Calle 29 sur #12H-92, glaurenz@chaquetasdeprom.com.</p>
HTML;
    }

    private function formData(Contract $contract): array
    {
        return [
            'contract' => $contract,
            'schools'  => School::orderBy('name')->get(),
            'types'    => JacketType::orderBy('sort')->get(),
            'stages'   => Contract::STAGES,
        ];
    }

    private function validated(Request $request, ?int $id): array
    {
        return $request->validate([
            'school_id'         => 'required|exists:schools,id',
            'jacket_type_id'    => 'nullable|exists:jacket_types,id',
            'code'              => 'nullable|string|max:60|unique:contracts,code'.($id ? ",$id" : ''),
            'year'             => 'nullable|integer|min:2020|max:2100',
            'price_per_unit'    => 'nullable|numeric|min:0',
            'production_status' => 'required|in:'.implode(',', array_keys(Contract::STAGES)),
            'notes'             => 'nullable|string',
            'objeto'            => 'nullable|string',
            'product_details'   => 'nullable|string',
            'combo_value'       => 'nullable|numeric|min:0',
            'contract_total'    => 'nullable|numeric|min:0',
            'total_words'       => 'nullable|string|max:255',
            'delivery_date'     => 'nullable|date',
            'delivery_business_days' => 'nullable|integer|min:1|max:200',
        ]);
    }

    private function nextCode(): string
    {
        $n = Contract::whereYear('created_at', date('Y'))->count() + 1;

        return 'CTR-'.date('Y').'-'.str_pad((string) $n, 3, '0', STR_PAD_LEFT);
    }
}
