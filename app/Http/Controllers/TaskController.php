<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Task;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{

   public function index(Request $request){
    $q = Task::query();
    if($request->has('title')){
        $q->where('title','like','%'.$request->title.'%');
    }
    if($request->has('user_id')){
        $q->where('user_id',$request->user_id);
    }
    $user = Auth::user();
    if($user->role === 'admin'){
        $tasks = $q->orderBy('created_at','desc')->paginate(20);
    }else{
        $tasks = $q->where('user_id',$user->id)->orderBy('created_at','desc')->paginate(20);
    }
    return view('tasks.index',compact('tasks'));
   }

   public function create(){
       $users = User::all();
       return view('tasks.create',compact('users'));
   }

   public function store(Request $request){
       $request->validate([
           'title' => 'required',
       ]);
       
       $data = $request->all();
       if (empty($data['user_id'])) {
           $data['user_id'] = Auth::id();
       }
       $task = Task::create($data);
       return redirect()->route('admin.task.index.get')->with('success', 'Đã thêm công việc!');
   }

   public function edit($id){
       $task = Task::find($id);
       $users = User::all();
       return view('tasks.edit',compact('task','users'));
   }

   public function update(Request $request,$id){
       $task = Task::find($id);
       $data = $request->all();
       if (empty($data['user_id'])) {
           $data['user_id'] = Auth::id();
       }
       $task->update($data);
       return redirect()->route('admin.task.index.get')->with('success', 'Đã sửa công việc!');
   }

   public function updatePriority(Request $request)
{
    $request->validate([
        'id' => 'required|exists:task,id',
        'priority' => 'required|in:1,2,3,4'
    ]);

    $task = Task::find($request->id);
    $task->priority = $request->priority;
    $task->save();

    return response()->json(['success' => true]);
}

public function updateStatus(Request $request)
{
    $request->validate([
        'id' => 'required|exists:task,id',
        'status' => 'required|in:pending,processing,completed'
    ]);

    $task = Task::find($request->id);
    $task->status = $request->status;
    $task->save();

    return response()->json(['success' => true]);
}

public function detail(Request $request)
{
    $request->validate([
        'id' => 'required|exists:task,id'
    ]);

    $task = Task::with('user')->find($request->id);
    
    if (!$task) {
        return response()->json(['success' => false, 'message' => 'Task không tồn tại']);
    }

    return response()->json([
        'success' => true,
        'task' => $task
    ]);
}

public function updateAssignee(Request $request)
{
    // Kiểm tra quyền admin
    if (Auth::user()->role !== 'admin') {
        return response()->json(['success' => false, 'message' => 'Không có quyền thực hiện']);
    }

    $request->validate([
        'id' => 'required|exists:task,id',
        'user_id' => 'nullable|exists:users,id'
    ]);

    $task = Task::find($request->id);
    $task->user_id = $request->user_id ?: null;
    $task->save();

    // Lấy tên user mới để trả về
    $assigneeName = null;
    if ($request->user_id) {
        $user = User::find($request->user_id);
        $assigneeName = $user ? $user->name : null;
    }

    return response()->json([
        'success' => true,
        'assignee_name' => $assigneeName
    ]);
}

    public function destroy($id)
    {
        $task = Task::find($id);
        $task->delete();
        return redirect()->back()->with('success', 'Đã xóa công việc!');
    }

    /**
     * Lấy số lượng task pending dựa trên role của user
     */
    public static function getPendingTaskCount()
    {
        $user = Auth::user();
        
        if ($user->role === 'admin') {
            // Admin: lấy tất cả task có status pending
            return Task::where('status', 'pending')->count();
        } else {
            // User: chỉ lấy task pending của user đó
            return Task::where('status', 'pending')
                      ->where('user_id', $user->id)
                      ->count();
        }
    }

}
