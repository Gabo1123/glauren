<?php

namespace App\Modules\Chaquetas\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Chaquetas\Models\Collection;
use App\Modules\Chaquetas\Models\CollectionImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CollectionController extends Controller
{
    public function index()
    {
        $collections = Collection::orderBy('sort')->get();

        return view('admin.cms.collections.index', compact('collections'));
    }

    public function create()
    {
        $collection = new Collection(['active' => true]);

        return view('admin.cms.collections.form', compact('collection'));
    }

    public function store(Request $request)
    {
        $collection = new Collection();
        $this->fillAndSave($request, $collection, true);

        return redirect()->route('admin.collections.index')->with('ok', 'Colección creada.');
    }

    public function edit(Collection $collection)
    {
        $collection->load('images');

        return view('admin.cms.collections.form', compact('collection'));
    }

    public function update(Request $request, Collection $collection)
    {
        $this->fillAndSave($request, $collection, false);

        return redirect()->route('admin.collections.index')->with('ok', 'Colección actualizada.');
    }

    public function destroy(Collection $collection)
    {
        $collection->delete();

        return back()->with('ok', 'Colección eliminada.');
    }

    public function destroyImage(Collection $collection, CollectionImage $image)
    {
        abort_unless($image->collection_id === $collection->id, 404);

        // borra el archivo si estaba en storage (no si es URL externa)
        if ($image->path && ! str_starts_with($image->path, 'http')) {
            Storage::disk('public')->delete($image->path);
        }
        $image->delete();

        return back()->with('ok', 'Imagen eliminada de la galería.');
    }

    private function fillAndSave(Request $request, Collection $collection, bool $creating): void
    {
        $data = $request->validate([
            'title'       => 'required|string|max:120',
            'description' => 'nullable|string',
            'link'        => 'nullable|string|max:190',
            'sort'        => 'nullable|integer',
            'active'      => 'nullable|boolean',
            'image'       => 'nullable|image|max:8192',
            'image_url'   => 'nullable|url|max:300',
            'images.*'    => 'nullable|image|max:8192',
            'image_urls'  => 'nullable|string',
        ]);

        $collection->fill([
            'title'       => $data['title'],
            'description' => $data['description'] ?? null,
            'link'        => $data['link'] ?? null,
            'sort'        => $data['sort'] ?? 0,
            'active'      => $request->boolean('active'),
        ]);

        // Portada
        if ($request->hasFile('image')) {
            $collection->image_path = $request->file('image')->store('colecciones', 'public');
        } elseif (! empty($data['image_url'])) {
            $collection->image_path = $data['image_url'];
        }

        $collection->save();

        // Galería: archivos subidos
        $sort = (int) $collection->images()->max('sort');
        foreach ($request->file('images', []) as $file) {
            if ($file) {
                $collection->images()->create([
                    'path' => $file->store('colecciones', 'public'),
                    'sort' => ++$sort,
                ]);
            }
        }

        // Galería: URLs pegadas (una por línea)
        if (! empty($data['image_urls'])) {
            foreach (preg_split('/\r\n|\r|\n/', $data['image_urls']) as $url) {
                $url = trim($url);
                if ($url !== '' && filter_var($url, FILTER_VALIDATE_URL)) {
                    $collection->images()->create(['path' => $url, 'sort' => ++$sort]);
                }
            }
        }
    }
}
