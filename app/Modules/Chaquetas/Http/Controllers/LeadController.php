<?php

namespace App\Modules\Chaquetas\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Chaquetas\Models\Lead;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'           => 'required|string|max:120',
            'email'          => 'required|email|max:160',
            'phone'          => 'nullable|string|max:40',
            'jacket_type'    => 'nullable|string|max:60',
            'accepted_terms' => 'accepted',
        ]);

        $lead = Lead::create([
            'name'           => $data['name'],
            'email'          => $data['email'],
            'phone'          => $data['phone'] ?? null,
            'jacket_type'    => $data['jacket_type'] ?? null,
            'accepted_terms' => true,
        ]);

        return response()->json(['ok' => true, 'lead_id' => $lead->id]);
    }
}
