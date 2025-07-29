<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\Categories;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{
    public function index()
    {
        $q = Post::query();
        if(request('title')){
            $q->where('title', 'like', '%'.request('title').'%');
        }

        $posts = $q->orderBy('id', 'desc')->paginate(10);
        return view('posts.index', compact('posts'));
    }
    public function create()
    {
        $categories = Categories::all();
        return view('posts.create', compact('categories'));
    }
    public function store(Request $request)
    {
        $request->merge(['user_id' => Auth::user()->id]);
        $request->merge(['slug' => str_slug($request->title,'-','')]);
        $post = Post::create($request->all());
        if($request->hasFile('file_attach')){
            $file = $request->file('file_attach');
            $file_name = time().'_'.$file->getClientOriginalName();
            $file->move(public_path('uploads/posts'), $file_name);
            $post->file_attach = $file_name;
            $post->save();
        }
        return redirect()->route('posts.index')->with('success', 'Bài viết đã được tạo thành công');
    }
    public function edit($id)
    {
        $post = Post::find($id);
        $categories = Categories::all();
        return view('posts.edit', compact('post', 'categories'));
    }
    public function update(Request $request, $id)
    {
        $post = Post::find($id);    
        $request->merge(['slug' => str_slug($request->title,'-','')]);
        $post->update($request->all());
        if($request->hasFile('file_attach')){
            $file = $request->file('file_attach');
            $file_name = time().'_'.$file->getClientOriginalName();
            $file->move(public_path('uploads/posts'), $file_name);
            $post->file_attach = $file_name;
            $post->save();
        }
        return redirect()->route('posts.index')->with('success', 'Bài viết đã được cập nhật thành công');
    }
    public function destroy($id)
    {
        $post = Post::find($id);
        $post->delete();
        return redirect()->route('posts.index')->with('success', 'Bài viết đã được xóa thành công');
    }
}
