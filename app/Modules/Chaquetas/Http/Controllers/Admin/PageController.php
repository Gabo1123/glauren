<?php

namespace App\Modules\Chaquetas\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Chaquetas\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PageController extends Controller
{
    public function index()
    {
        $pages = Page::orderBy('sort')->get();

        return view('admin.cms.pages.index', compact('pages'));
    }

    public function create()
    {
        $page = new Page(['published' => true]);

        return view('admin.cms.pages.form', compact('page'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request, null);
        Page::create($data);

        return redirect()->route('admin.pages.index')->with('ok', 'Página creada.');
    }

    public function edit(Page $page)
    {
        return view('admin.cms.pages.form', compact('page'));
    }

    public function update(Request $request, Page $page)
    {
        $page->update($this->validated($request, $page->id));

        return redirect()->route('admin.pages.index')->with('ok', 'Página actualizada.');
    }

    public function destroy(Page $page)
    {
        $page->delete();

        return back()->with('ok', 'Página eliminada.');
    }

    private function validated(Request $request, ?int $id): array
    {
        $data = $request->validate([
            'title' => 'required|string|max:160',
            'slug'  => 'nullable|alpha_dash|max:160|unique:pages,slug'.($id ? ",$id" : ''),
            'body'  => 'nullable|string',
            'sort'  => 'nullable|integer',
            'published' => 'nullable|boolean',
            'meta_title'       => 'nullable|string|max:180',
            'meta_description' => 'nullable|string|max:320',
            'focus_keyword'    => 'nullable|string|max:120',
            'og_image'         => 'nullable|url|max:500',
            'noindex'          => 'nullable|boolean',
            'schema_type'      => 'nullable|string|max:40',
        ]);
        $data['slug'] = $data['slug'] ?: Str::slug($data['title']);
        $data['published'] = $request->boolean('published');
        $data['noindex'] = $request->boolean('noindex');
        $data['sort'] = $data['sort'] ?? 0;

        return $data;
    }
}
