@extends('layouts.app')
@section('title', 'Sửa công việc')
@section('js')
    <script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
@endsection
@section('js-init')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            ClassicEditor.create(document.querySelector('#content'))
                .then(editor => {
                    editor.ui.view.editable.element.style.minHeight = '350px';
                    editor.ui.view.editable.element.style.height = '350px';
                });
        });
    </script>
@endsection
@section('content')
    <div class="container">
        <h3 class="title-main">Sửa công việc</h3>
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        <form action="{{ route('admin.task.edit.post',$task->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label for="status" class="form-label">Loại công việc</label>
                <select class="form-control" id="task_type" name="task_type" >
                    <option value="support" {{($task->task_type=='support') ? 'selected' : ''}}>Hỗ trợ khách hàng (support)</option>
                    <option value="feedback" {{($task->task_type=='feedback') ? 'selected' : ''}}>Xử lý phản ánh (feedback)</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="title" class="form-label">Tiêu đề công việc*</label>
                <input type="text" class="form-control" id="title" name="title" value="{{ $task->title }}" required>
            </div>
            <div class="mb-3">
                <label for="category_id" class="form-label">Giao việc cho ?</label>
                <select class="form-control" id="user_id" name="user_id">
                    <option value="">---Chưa giao---</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{($task->user_id==$user->id) ? 'selected' : ''}}>{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label for="content" class="form-label">Nội dung công việc</label>
                <textarea class="form-control" id="content" name="content" rows="10">{!! $task->content !!}</textarea>
            </div>
            <div class="mb-3">
                <label for="title" class="form-label">Ngày hoàn thành dự kiến</label>
                <input type="datetime-local" class="form-control" id="end_date" name="end_date" value="{{ $task->end_date }}" >
            </div>
            <div class="mb-3">
                <label for="status" class="form-label">Mức độ ưu tiên</label>
                <select class="form-control" id="priority" name="priority" >
                    <option value="1" {{($task->priority==1) ? 'selected' : ''}}>Thấp</option>
                    <option value="2" {{($task->priority==2) ? 'selected' : ''}}>Trung bình</option>
                    <option value="3" {{($task->priority==3) ? 'selected' : ''}}>Cao</option>
                    <option value="4" {{($task->priority==4) ? 'selected' : ''}}>Quan trọng </option>
                </select>
            </div>
            <div class="mb-3">
                <label for="status" class="form-label">Trạng thái</label>
                <select class="form-control" id="status" name="status" required>
                    <option value="pending" {{($task->status=='pending') ? 'selected' : ''}}>Mới</option>
                    <option value="processing" {{($task->status=='processing') ? 'selected' : ''}}>Đang xử lý</option>
                    <option value="completed" {{($task->status=='completed') ? 'selected' : ''}}>Đã hoàn thành</option>
                </select>
            </div>
            <a href="{{ route('admin.task.index.get') }}" class="btn btn-secondary">Quay lại</a>
            <button type="submit" class="btn btn-success"><i class="fa fa-save"></i>  Lưu lại</button>
        </form>
    </div>
@endsection
