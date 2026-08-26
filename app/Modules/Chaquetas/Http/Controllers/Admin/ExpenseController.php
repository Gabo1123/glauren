<?php

namespace App\Modules\Chaquetas\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Chaquetas\Models\Contract;
use App\Modules\Chaquetas\Models\Expense;
use App\Modules\Chaquetas\Models\JacketType;
use App\Modules\Chaquetas\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $q = Expense::with(['school', 'jacketType'])->orderByDesc('date')->orderByDesc('id');

        if ($request->filled('category')) {
            $q->where('category', $request->input('category'));
        }
        if ($request->filled('school')) {
            $q->where('school_id', $request->input('school'));
        }
        if ($request->filled('month')) {
            [$y, $m] = array_pad(explode('-', $request->input('month')), 2, null);
            if ($y && $m) {
                $q->whereYear('date', $y)->whereMonth('date', $m);
            }
        }

        $expenses = $q->get();
        $total = $expenses->sum('amount');
        $byCat = $expenses->groupBy('category')->map->sum('amount');

        return view('admin.expenses.index', [
            'expenses'   => $expenses,
            'total'      => $total,
            'byCat'      => $byCat,
            'categories' => Expense::CATEGORIES,
            'schools'    => School::orderBy('name')->get(),
            'filters'    => $request->only(['category', 'school', 'month']),
        ]);
    }

    /** Exporta los gastos (con los filtros aplicados) a CSV. */
    public function export(Request $request)
    {
        $q = Expense::with(['school', 'jacketType', 'contract'])->orderByDesc('date');
        if ($request->filled('category')) {
            $q->where('category', $request->input('category'));
        }
        if ($request->filled('school')) {
            $q->where('school_id', $request->input('school'));
        }
        if ($request->filled('month')) {
            [$y, $m] = array_pad(explode('-', $request->input('month')), 2, null);
            if ($y && $m) {
                $q->whereYear('date', $y)->whereMonth('date', $m);
            }
        }

        $rows = [['Fecha', 'Categoria', 'Concepto', 'Proveedor', 'Valor', 'Colegio', 'Contrato', 'Tipo', 'Material']];
        foreach ($q->get() as $e) {
            $rows[] = [
                $e->date->format('Y-m-d'), $e->categoryLabel(), $e->concept, $e->supplier,
                (int) $e->amount, $e->school?->name, $e->contract?->code, $e->jacketType?->name, $e->material,
            ];
        }

        $csv = collect($rows)->map(fn ($r) => implode(',', array_map(fn ($v) => '"'.str_replace('"', '""', (string) $v).'"', $r)))->implode("\n");

        return response("\xEF\xBB\xBF".$csv, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="gastos.csv"',
        ]);
    }

    public function create()
    {
        return view('admin.expenses.form', $this->formData(new Expense(['date' => now()->toDateString(), 'category' => 'tela'])));
    }

    public function store(Request $request)
    {
        $expense = new Expense();
        $this->save($request, $expense);

        return redirect()->route('admin.expenses.index')->with('ok', 'Gasto registrado.');
    }

    public function edit(Expense $expense)
    {
        return view('admin.expenses.form', $this->formData($expense));
    }

    public function update(Request $request, Expense $expense)
    {
        $this->save($request, $expense);

        return redirect()->route('admin.expenses.index')->with('ok', 'Gasto actualizado.');
    }

    public function destroy(Expense $expense)
    {
        if ($expense->invoice_path) {
            Storage::disk('public')->delete($expense->invoice_path);
        }
        $expense->delete();

        return back()->with('ok', 'Gasto eliminado.');
    }

    private function formData(Expense $expense): array
    {
        return [
            'expense'    => $expense,
            'categories' => Expense::CATEGORIES,
            'schools'    => School::orderBy('name')->get(),
            'contracts'  => Contract::with('school')->orderByDesc('year')->get(),
            'types'      => JacketType::orderBy('name')->get(),
        ];
    }

    private function save(Request $request, Expense $expense): void
    {
        $data = $request->validate([
            'date'           => 'required|date',
            'category'       => 'required|in:'.implode(',', array_keys(Expense::CATEGORIES)),
            'concept'        => 'required|string|max:190',
            'supplier'       => 'nullable|string|max:160',
            'amount'         => 'required|numeric|min:0',
            'school_id'      => 'nullable|exists:schools,id',
            'contract_id'    => 'nullable|exists:contracts,id',
            'jacket_type_id' => 'nullable|exists:jacket_types,id',
            'material'       => 'nullable|string|max:120',
            'notes'          => 'nullable|string',
            'invoice'        => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:8192',
        ]);

        $expense->fill([
            'date'           => $data['date'],
            'category'       => $data['category'],
            'concept'        => $data['concept'],
            'supplier'       => $data['supplier'] ?? null,
            'amount'         => $data['amount'],
            'school_id'      => $data['school_id'] ?? null,
            'contract_id'    => $data['contract_id'] ?? null,
            'jacket_type_id' => $data['jacket_type_id'] ?? null,
            'material'       => $data['material'] ?? null,
            'notes'          => $data['notes'] ?? null,
        ]);

        if ($request->hasFile('invoice')) {
            if ($expense->invoice_path) {
                Storage::disk('public')->delete($expense->invoice_path);
            }
            $expense->invoice_path = $request->file('invoice')->store('facturas', 'public');
        }

        $expense->save();
    }
}
