<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Task;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{

   public function index(){
    $q = Task::query();
    $tasks = $q->orderBy('created_at','desc')->paginate(20);
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
       $task = Task::create($request->all());
       return redirect()->route('admin.task.index.get')->with('success', 'Đã thêm công việc!');
   }

   public function edit($id){
       $task = Task::find($id);
       $users = User::all();
       return view('tasks.edit',compact('task','users'));
   }

   public function update(Request $request,$id){
       $task = Task::find($id);
       $task->update($request->all());
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

    public function destroy($id)
    {
        $task = Task::find($id);
        $task->delete();
        return redirect()->back()->with('success', 'Đã xóa công việc!');
    }

}
