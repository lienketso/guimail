<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Categories;
use App\Models\Post;

class FrontendController extends Controller
{
    public function home()
    {
        $categories = Categories::where('status', 'active')->orderBy('sort_order', 'asc')->get();
        return view('frontend.home', compact('categories'));
    }
    public function postDetail($slug)
    {
        $post = Post::where('slug', $slug)->first();
        $categories = Categories::where('status', 'active')->orderBy('sort_order', 'asc')->get();
        return view('frontend.posts.single', compact('post', 'categories'));
    }
    public function postList($slug)
    {
        $category = Categories::where('slug', $slug)->first();
        $posts = Post::where('category_id', $category->id)->where('status', 'active')->orderBy('created_at', 'desc')->paginate(10);
        $categories = Categories::where('status', 'active')->orderBy('sort_order', 'asc')->get();
        return view('frontend.posts.list', compact('category', 'posts', 'categories'));
    }
} 