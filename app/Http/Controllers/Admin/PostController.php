<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\PostRequest;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\Category;
use App\Models\Tag;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

class PostController extends Controller
{

    public function __construct()
    {
        $this->middleware('can:admin.posts.index')->only('index');
        $this->middleware('can:admin.posts.create')->only('create', 'store');
        $this->middleware('can:admin.posts.edit')->only('edit', 'update');
        $this->middleware('can:admin.posts.delete')->only('destroy');
    }

    public function index()
    {
        
        return view('admin.posts.index');
    }

    public function create()
    {
        $categories = Category::pluck('name', 'id');
        $tags = Tag::all();

        return view('admin.posts.create', compact('categories', 'tags'));
    }

    public function store(PostRequest $request)
    {
        
        $post = Post::create($request->all());

        if($request->file('file')) {
            $url = Storage::put('public/posts', $request->file('file'));
            $url = str_replace("public/" , "", $url);

            $uploadedFileUrl = Cloudinary::upload($request->file('file')->getRealPath())->getSecurePath();

            $post->image()->create([
                'url' => $url
            ]);
        }

        if($request->tags){
            $post->tags()->attach($request->tags);
        }

        Cache::flush();

        return redirect()->route('admin.posts.edit', $post)->with('info', 'El post se creó con exitó');
        
    }

    public function edit(Post $post)
    {

        $this->authorize('author', $post);

        $categories = Category::pluck('name', 'id');
        $tags = Tag::all();

        return view('admin.posts.edit', compact('post', 'categories', 'tags'));
    }

    public function update(PostRequest $request, Post $post)
    {

        $this->authorize('author', $post);
        
        $post->update($request->all());

        if($request->file('file')) {
            $url = Storage::put('public/posts', $request->file('file'));
            $url = str_replace("public/" , "", $url);

            $uploadedFileUrl = Cloudinary::upload($request->file('file')->getRealPath())->getSecurePath();

            if($post->image) {
                Storage::delete('public/'.$post->image->url);

                $post->image->update([
                    'url' => $url
                ]);
            }else{
                $post->image()->create([
                    'url' => $url
                ]);
            }

        }

        if($request->tags){
            $post->tags()->sync($request->tags);
        }

        Cache::flush();

        return redirect()->route('admin.posts.edit', $post)->with('info', 'El post se actulizó con exitó');
    
    }

    public function destroy(Post $post)
    {
        
        $this->authorize('author', $post);

        $post->delete();

        Cache::flush();

        return redirect()->route('admin.posts.index')->with('info', 'El post se eliminó con exitó');
    }
}
