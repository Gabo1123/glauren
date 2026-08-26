<?php

namespace App\Modules\Chaquetas\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Chaquetas\Models\Post;

class NewsController extends Controller
{
    public function index()
    {
        $posts = Post::published()->paginate(9);

        return view('chaquetas.news.index', compact('posts'));
    }

    public function show(string $slug)
    {
        $post = Post::where('slug', $slug)->published()->firstOrFail();

        return view('chaquetas.news.show', compact('post'));
    }
}
