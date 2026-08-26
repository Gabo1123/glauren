<?php

namespace App\Modules\Chaquetas\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Chaquetas\Models\ShowcasePhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ShowcaseController extends Controller
{
    public function index()
    {
        $photos = ShowcasePhoto::orderBy('sort')->get();

        return view('admin.cms.showcase.index', compact('photos'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'label'     => 'nullable|string|max:120',
            'sort'      => 'nullable|integer',
            'image'     => 'nullable|image|max:8192',
            'image_url' => 'nullable|url|max:300',
        ]);

        // Subida múltiple de archivos
        $sort = (int) ShowcasePhoto::max('sort');
        $created = 0;
        foreach ($request->file('images', []) as $file) {
            if ($file) {
                ShowcasePhoto::create([
                    'image_path' => $file->store('promociones', 'public'),
                    'label'      => $data['label'] ?? null,
                    'sort'       => ++$sort,
                    'active'     => true,
                ]);
                $created++;
            }
        }

        // URL pegada
        if (! empty($data['image_url'])) {
            ShowcasePhoto::create([
                'image_path' => $data['image_url'],
                'label'      => $data['label'] ?? null,
                'sort'       => ++$sort,
                'active'     => true,
            ]);
            $created++;
        }

        if (! $created) {
            return back()->withErrors(['image' => 'Sube al menos una foto o pega una URL.']);
        }

        return back()->with('ok', "Se agregaron $created foto(s).");
    }

    public function update(Request $request, ShowcasePhoto $photo)
    {
        $data = $request->validate([
            'label'  => 'nullable|string|max:120',
            'sort'   => 'nullable|integer',
            'active' => 'nullable|boolean',
        ]);
        $photo->update([
            'label'  => $data['label'] ?? null,
            'sort'   => $data['sort'] ?? $photo->sort,
            'active' => $request->boolean('active'),
        ]);

        return back()->with('ok', 'Foto actualizada.');
    }

    public function destroy(ShowcasePhoto $photo)
    {
        if ($photo->image_path && ! str_starts_with($photo->image_path, 'http')) {
            Storage::disk('public')->delete($photo->image_path);
        }
        $photo->delete();

        return back()->with('ok', 'Foto eliminada.');
    }
}
