<?php

namespace App\Modules\Chaquetas\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Chaquetas\Models\Testimonial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TestimonialController extends Controller
{
    public function index()
    {
        $testimonials = Testimonial::orderBy('sort')->get();

        return view('admin.cms.testimonials.index', compact('testimonials'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $t = new Testimonial();
        $this->fill($t, $request, $data);

        return back()->with('ok', 'Testimonio agregado.');
    }

    public function update(Request $request, Testimonial $testimonial)
    {
        $data = $this->validated($request);
        $this->fill($testimonial, $request, $data);

        return back()->with('ok', 'Testimonio actualizado.');
    }

    public function destroy(Testimonial $testimonial)
    {
        if ($testimonial->photo_path && ! str_starts_with($testimonial->photo_path, 'http')) {
            Storage::disk('public')->delete($testimonial->photo_path);
        }
        $testimonial->delete();

        return back()->with('ok', 'Testimonio eliminado.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'text'   => 'required|string|max:400',
            'name'   => 'required|string|max:120',
            'role'   => 'nullable|string|max:120',
            'rating' => 'nullable|integer|min:1|max:5',
            'sort'   => 'nullable|integer',
            'active' => 'nullable|boolean',
            'photo'  => 'nullable|image|max:4096',
        ]);
    }

    private function fill(Testimonial $t, Request $request, array $data): void
    {
        $t->fill([
            'text'   => $data['text'],
            'name'   => $data['name'],
            'role'   => $data['role'] ?? null,
            'rating' => $data['rating'] ?? 5,
            'sort'   => $data['sort'] ?? $t->sort ?? 0,
            'active' => $request->boolean('active'),
        ]);
        if ($request->hasFile('photo')) {
            $t->photo_path = $request->file('photo')->store('testimonios', 'public');
        }
        $t->save();
    }
}
