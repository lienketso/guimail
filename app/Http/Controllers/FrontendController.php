<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Categories;
use App\Models\Post;
use App\Models\Task;
use Illuminate\Http\Request;

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
    public function supportRequest(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'task_type' => 'required'
        ]);
        $task = new Task();
        $task->title = $request->title;
        $task->content = $request->content;
        $task->task_type = $request->task_type;
        $task->status = 'pending';
        $task->save();
        return response()->json(['success' => true, 'message' => 'Yêu cầu hỗ trợ đã được gửi thành công']);
    }
} 