<?php

namespace App\Modules\Chaquetas\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Chaquetas\Models\PhotoCategory;
use App\Modules\Chaquetas\Models\PhotoPackage;

class PhotographyController extends Controller
{
    public function index()
    {
        $categories = PhotoCategory::where('active', true)
            ->with(['services' => fn ($q) => $q->where('active', true)])
            ->orderBy('sort')->orderBy('id')->get();

        $packages = PhotoPackage::where('active', true)->orderBy('sort')->orderBy('id')->get();

        return view('chaquetas.fotografia', compact('categories', 'packages'));
    }
}
