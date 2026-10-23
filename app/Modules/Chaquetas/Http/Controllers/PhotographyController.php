<?php

namespace App\Modules\Chaquetas\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Chaquetas\Models\Lead;
use App\Modules\Chaquetas\Models\PhotoCategory;
use App\Modules\Chaquetas\Models\PhotoGalleryImage;
use App\Modules\Chaquetas\Models\PhotoPackage;
use Illuminate\Http\Request;

class PhotographyController extends Controller
{
    public function index()
    {
        $categories = PhotoCategory::where('active', true)
            ->with(['services' => fn ($q) => $q->where('active', true)])
            ->orderBy('sort')->orderBy('id')->get();

        $packages = PhotoPackage::where('active', true)->orderBy('sort')->orderBy('id')->get();

        $gallery = PhotoGalleryImage::where('active', true)
            ->with('category')->orderBy('sort')->latest()->get();

        // Categorías que tienen al menos una foto (para los filtros del portafolio)
        $galleryCats = $gallery->pluck('category')->filter()->unique('id')->values();

        return view('chaquetas.fotografia', compact('categories', 'packages', 'gallery', 'galleryCats'));
    }

    /** Guarda un contacto (lead) del formulario de la landing de fotografía. */
    public function lead(Request $request)
    {
        $data = $request->validate([
            'name'    => 'required|string|max:120',
            'phone'   => 'required|string|max:40',
            'city'    => 'nullable|string|max:80',
            'school'  => 'nullable|string|max:160',
            'message' => 'nullable|string|max:1000',
        ], [
            'name.required'  => 'Escribe tu nombre.',
            'phone.required' => 'Escribe tu teléfono o WhatsApp.',
        ]);

        Lead::create([
            'name'           => $data['name'],
            'phone'          => preg_replace('/\s+/', '', $data['phone']),
            'school'         => $data['school'] ?? null,
            'message'        => trim(($data['city'] ? 'Ciudad: '.$data['city'].'. ' : '').($data['message'] ?? '')) ?: null,
            'source'         => 'fotografia-grados',
            'ip'             => $request->ip(),
            'accepted_terms' => true,
        ]);

        return back()->with('foto_ok', '¡Gracias! Recibimos tus datos. Te contactaremos muy pronto. 📸')
            ->withFragment('contacto');
    }
}
