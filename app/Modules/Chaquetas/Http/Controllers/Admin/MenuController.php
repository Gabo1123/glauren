<?php

namespace App\Modules\Chaquetas\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Chaquetas\Models\MenuItem;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function index()
    {
        $items = MenuItem::orderBy('sort')->get();

        return view('admin.cms.menu', compact('items'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        MenuItem::create($data);

        return back()->with('ok', 'Ítem agregado al menú.');
    }

    public function update(Request $request, MenuItem $menu)
    {
        $menu->update($this->validated($request));

        return redirect()->route('admin.menu.index')->with('ok', 'Ítem actualizado.');
    }

    public function edit(MenuItem $menu)
    {
        return view('admin.cms.menu-edit', compact('menu'));
    }

    public function destroy(MenuItem $menu)
    {
        $menu->delete();

        return back()->with('ok', 'Ítem eliminado.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'label' => 'required|string|max:60',
            'url'   => 'required|string|max:190',
            'sort'  => 'nullable|integer',
            'active' => 'nullable|boolean',
        ]);
        $data['active'] = $request->boolean('active');
        $data['sort'] = $data['sort'] ?? 0;

        return $data;
    }
}
