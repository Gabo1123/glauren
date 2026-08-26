<?php

namespace App\Modules\Chaquetas\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Chaquetas\Models\Student;

class PaymentApprovalController extends Controller
{
    public function index()
    {
        $students = Student::with('contract.school')
            ->where('payment_status', 'en_revision')
            ->orderBy('payment_declared_at')
            ->get();

        return view('admin.finance.approvals', compact('students'));
    }

    public function approve(Student $student)
    {
        $student->update([
            'payment_status' => 'pagado',
            'paid_at'        => now(),
        ]);

        return back()->with('ok', 'Pago de '.$student->name.' aprobado.');
    }

    public function reject(Student $student)
    {
        $student->update([
            'payment_status'      => 'pendiente',
            'payment_method'      => null,
            'payment_proof'       => null,
            'payment_declared_at' => null,
        ]);

        return back()->with('ok', 'Pago de '.$student->name.' rechazado. El estudiante puede volver a registrarlo.');
    }
}
