<?php

namespace App\Modules\Chaquetas\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Chaquetas\Models\RankingEntry;
use Illuminate\Http\Request;

class RankingController extends Controller
{
    public function index()
    {
        $entries = RankingEntry::orderByDesc('votes')->orderBy('name')->get();

        return view('admin.cms.ranking', compact('entries'));
    }

    public function update(Request $request, RankingEntry $entry)
    {
        $data = $request->validate([
            'name'  => 'required|string|max:120',
            'city'  => 'nullable|string|max:80',
            'votes' => 'required|integer|min:0',
        ]);
        $entry->update($data);

        return back()->with('ok', 'Colegio actualizado.');
    }

    public function toggle(RankingEntry $entry)
    {
        $entry->update(['active' => ! $entry->active]);

        return back()->with('ok', $entry->active ? 'Colegio visible.' : 'Colegio oculto.');
    }

    public function destroy(RankingEntry $entry)
    {
        $entry->delete();

        return back()->with('ok', 'Colegio eliminado del ranking.');
    }
}
