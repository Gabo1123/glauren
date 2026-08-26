<?php

namespace App\Modules\Chaquetas\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Chaquetas\Models\Collection;

class CollectionController extends Controller
{
    public function show(string $slug)
    {
        $collection = Collection::where('slug', $slug)->where('active', true)->firstOrFail();
        $collection->load('images');

        $others = Collection::where('active', true)
            ->where('id', '!=', $collection->id)
            ->orderBy('sort')
            ->limit(4)
            ->get();

        return view('chaquetas.collection', compact('collection', 'others'));
    }
}
