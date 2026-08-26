<?php

namespace App\Modules\Chaquetas\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Chaquetas\Models\RankingEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RankingController extends Controller
{
    private const KEY = 'ranking_voted';

    public function index()
    {
        $entries = RankingEntry::active()->orderByDesc('votes')->orderBy('name')->get();
        $voted   = session(self::KEY, []);

        return view('chaquetas.ranking', compact('entries', 'voted'));
    }

    public function vote(RankingEntry $entry)
    {
        if (! $entry->active) {
            return back();
        }
        $voted = session(self::KEY, []);
        $today = now()->toDateString();

        if (($voted[$entry->id] ?? null) === $today) {
            return back()->with('rank_msg', 'Ya votaste hoy por '.$entry->name.'. Comparte el link para sumar más votos 💪');
        }

        $entry->increment('votes');
        $voted[$entry->id] = $today;
        session([self::KEY => $voted]);

        return back()->with('rank_msg', '¡Voto registrado por '.$entry->name.'! 🎉');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:120',
            'city' => 'nullable|string|max:80',
        ]);
        $slug = Str::slug($data['name']);

        $entry = RankingEntry::where('slug', $slug)->first();
        if ($entry) {
            return redirect()->route('ranking')->with('rank_msg', $entry->name.' ya está en el ranking. ¡Vota por él! 👇');
        }

        $entry = RankingEntry::create([
            'name'  => $data['name'],
            'city'  => $data['city'] ?? null,
            'slug'  => $slug,
            'votes' => 1,
            'active' => true,
        ]);
        $voted = session(self::KEY, []);
        $voted[$entry->id] = now()->toDateString();
        session([self::KEY => $voted]);

        return redirect()->route('ranking')->with('rank_msg', '¡'.$entry->name.' agregado con tu primer voto! Comparte para que suba 🚀');
    }
}
