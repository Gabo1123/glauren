<?php

namespace App\Modules\Chaquetas\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Chaquetas\Models\Faq;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    public function store(Request $request)
    {
        Faq::create($this->validated($request));

        return back()->with('ok', 'Pregunta agregada.');
    }

    public function update(Request $request, Faq $faq)
    {
        $faq->update($this->validated($request));

        return back()->with('ok', 'Pregunta actualizada.');
    }

    public function destroy(Faq $faq)
    {
        $faq->delete();

        return back()->with('ok', 'Pregunta eliminada.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'question' => 'required|string|max:190',
            'answer'   => 'required|string',
            'page_key' => 'nullable|string|max:60',
            'sort'     => 'nullable|integer',
            'active'   => 'nullable|boolean',
        ]);
        $data['page_key'] = $data['page_key'] ?: 'home';
        $data['active'] = $request->boolean('active');
        $data['sort'] = $data['sort'] ?? 0;

        return $data;
    }
}
