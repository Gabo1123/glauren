<?php

namespace App\Modules\Chaquetas\Http\Controllers\Foto;

use App\Http\Controllers\Controller;
use App\Modules\Chaquetas\Models\PhotoCategory;
use App\Modules\Chaquetas\Models\PhotoPackage;
use App\Modules\Chaquetas\Models\PhotoService;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PanelController extends Controller
{
    public function dashboard()
    {
        $categories = PhotoCategory::with('services')->orderBy('sort')->orderBy('id')->get();
        $packages = PhotoPackage::orderBy('sort')->orderBy('id')->get();

        return view('foto.dashboard', compact('categories', 'packages'));
    }

    /* ---------------- Categorías ---------------- */

    public function createCategory()
    {
        return view('foto.category-form', ['category' => new PhotoCategory(['active' => true])]);
    }

    public function editCategory(PhotoCategory $category)
    {
        return view('foto.category-form', compact('category'));
    }

    public function storeCategory(Request $request)
    {
        $data = $this->validateCategory($request);
        $data['slug'] = $this->uniqueSlug(PhotoCategory::class, $data['name']);
        if ($img = $this->storeImage($request, 'image', 'fotografia')) {
            $data['image_path'] = $img;
        }
        PhotoCategory::create($data);

        return redirect()->route('foto.dashboard')->with('ok', 'Categoría creada.');
    }

    public function updateCategory(Request $request, PhotoCategory $category)
    {
        $data = $this->validateCategory($request);
        if ($img = $this->storeImage($request, 'image', 'fotografia')) {
            $data['image_path'] = $img;
        }
        $category->update($data);

        return redirect()->route('foto.dashboard')->with('ok', 'Categoría actualizada.');
    }

    public function deleteCategory(PhotoCategory $category)
    {
        $category->delete();

        return back()->with('ok', 'Categoría eliminada.');
    }

    /* ---------------- Servicios ---------------- */

    public function createService(PhotoCategory $category)
    {
        return view('foto.service-form', ['category' => $category, 'service' => new PhotoService(['active' => true])]);
    }

    public function editService(PhotoService $service)
    {
        return view('foto.service-form', ['category' => $service->category, 'service' => $service]);
    }

    public function storeService(Request $request, PhotoCategory $category)
    {
        $data = $this->validateService($request);
        if ($img = $this->storeImage($request, 'image', 'fotografia')) {
            $data['image_path'] = $img;
        }
        $category->services()->create($data);

        return redirect()->route('foto.dashboard')->with('ok', 'Servicio agregado.');
    }

    public function updateService(Request $request, PhotoService $service)
    {
        $data = $this->validateService($request);
        if ($img = $this->storeImage($request, 'image', 'fotografia')) {
            $data['image_path'] = $img;
        }
        $service->update($data);

        return redirect()->route('foto.dashboard')->with('ok', 'Servicio actualizado.');
    }

    public function deleteService(PhotoService $service)
    {
        $service->delete();

        return back()->with('ok', 'Servicio eliminado.');
    }

    /* ---------------- Paquetes ---------------- */

    public function createPackage()
    {
        return view('foto.package-form', ['package' => new PhotoPackage(['active' => true])]);
    }

    public function editPackage(PhotoPackage $package)
    {
        return view('foto.package-form', compact('package'));
    }

    public function storePackage(Request $request)
    {
        PhotoPackage::create($this->validatePackage($request));

        return redirect()->route('foto.dashboard')->with('ok', 'Paquete creado.');
    }

    public function updatePackage(Request $request, PhotoPackage $package)
    {
        $package->update($this->validatePackage($request));

        return redirect()->route('foto.dashboard')->with('ok', 'Paquete actualizado.');
    }

    public function deletePackage(PhotoPackage $package)
    {
        $package->delete();

        return back()->with('ok', 'Paquete eliminado.');
    }

    /* ---------------- Helpers ---------------- */

    private function validateCategory(Request $request): array
    {
        return $request->validate([
            'name'        => 'required|string|max:120',
            'subtitle'    => 'nullable|string|max:180',
            'description' => 'nullable|string',
            'keywords'    => 'nullable|string',
            'icon'        => 'nullable|string|max:16',
            'sort'        => 'nullable|integer',
            'active'      => 'nullable|boolean',
        ]) + ['active' => $request->boolean('active')];
    }

    private function validateService(Request $request): array
    {
        return $request->validate([
            'title'       => 'required|string|max:160',
            'subtitle'    => 'nullable|string|max:180',
            'description' => 'nullable|string',
            'keywords'    => 'nullable|string',
            'sort'        => 'nullable|integer',
            'active'      => 'nullable|boolean',
        ]) + ['active' => $request->boolean('active')];
    }

    private function validatePackage(Request $request): array
    {
        $data = $request->validate([
            'name'     => 'required|string|max:120',
            'tagline'  => 'nullable|string|max:180',
            'features' => 'nullable|string',
            'featured' => 'nullable|boolean',
            'sort'     => 'nullable|integer',
            'active'   => 'nullable|boolean',
        ]);
        // Una característica por línea → array
        $data['features'] = collect(preg_split('/\r?\n/', (string) $request->input('features')))
            ->map(fn ($l) => trim($l))->filter()->values()->all();
        $data['featured'] = $request->boolean('featured');
        $data['active'] = $request->boolean('active');

        return $data;
    }

    private function uniqueSlug(string $model, string $name): string
    {
        $base = Str::slug($name) ?: 'cat';
        $slug = $base;
        $i = 2;
        while ($model::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }

    /** Guarda una imagen de forma segura (imagen real, sin SVG). */
    private function storeImage(Request $request, string $field, string $dir): ?string
    {
        if (! $request->hasFile($field)) {
            return null;
        }
        $request->validate([
            $field => 'image|mimes:jpg,jpeg,png,webp|mimetypes:image/jpeg,image/png,image/webp|max:8192',
        ]);
        /** @var UploadedFile $file */
        $file = $request->file($field);
        if (! @getimagesize($file->getRealPath())) {
            return null;
        }

        return $file->store($dir, 'public');
    }
}
