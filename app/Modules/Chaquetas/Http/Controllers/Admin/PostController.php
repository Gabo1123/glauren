<?php

namespace App\Modules\Chaquetas\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Chaquetas\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::orderByDesc('created_at')->get();

        return view('admin.cms.posts.index', compact('posts'));
    }

    public function create()
    {
        $post = new Post(['active' => true, 'published_at' => now()]);

        return view('admin.cms.posts.form', compact('post'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request, null);
        $data['cover_path'] = $this->cover($request);
        Post::create($data);

        return redirect()->route('admin.posts.index')->with('ok', 'Noticia publicada.');
    }

    public function edit(Post $post)
    {
        return view('admin.cms.posts.form', compact('post'));
    }

    public function update(Request $request, Post $post)
    {
        $data = $this->validated($request, $post->id);
        if ($cover = $this->cover($request)) {
            $data['cover_path'] = $cover;
        }
        $post->update($data);

        return redirect()->route('admin.posts.index')->with('ok', 'Noticia actualizada.');
    }

    public function destroy(Post $post)
    {
        $post->delete();

        return back()->with('ok', 'Noticia eliminada.');
    }

    private function validated(Request $request, ?int $id): array
    {
        $data = $request->validate([
            'title'   => 'required|string|max:180',
            'slug'    => 'nullable|alpha_dash|max:180|unique:posts,slug'.($id ? ",$id" : ''),
            'excerpt' => 'nullable|string|max:400',
            'author'      => 'nullable|string|max:120',
            'author_role' => 'nullable|string|max:160',
            'body'    => 'nullable|string',
            'published_at' => 'nullable|date',
            'active'  => 'nullable|boolean',
            'cover'   => 'nullable|image|max:5120',
            'meta_title'       => 'nullable|string|max:180',
            'meta_description' => 'nullable|string|max:320',
            'focus_keyword'    => 'nullable|string|max:120',
            'og_image'         => 'nullable|url|max:500',
            'noindex'          => 'nullable|boolean',
        ]);
        $data['slug'] = $data['slug'] ?: Str::slug($data['title']);
        $data['active'] = $request->boolean('active');
        $data['noindex'] = $request->boolean('noindex');
        $data['published_at'] = $data['published_at'] ?: now();
        unset($data['cover']);

        return $data;
    }

    private function cover(Request $request): ?string
    {
        if ($request->hasFile('cover')) {
            return $request->file('cover')->store('noticias', 'public');
        }

        return null;
    }
}
