@extends('layouts.app')
@section('title', 'Danh sách bài viết')
@section('content')
<div class="container">
    <h3 class="title-main">Danh sách bài viết</h3>
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    <form class="row g-2 mb-3 mt-3" method="GET" action="{{ route('posts.index') }}">
        <div class="col-md-5">
            <input type="text" class="form-control" name="title" value="{{ request('title') }}" placeholder="Tìm theo tên bài viết...">
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search"></i> Tìm kiếm</button>
        </div>
        @if(Auth::user()->role === 'admin')
        <div class="col-md-2">
            <a href="{{ route('posts.create') }}" class="btn btn-primary w-100"><i class="bi bi-plus-circle"></i> Thêm bài viết</a>
        </div>
        @endif
    </form>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Tiêu đề</th>
                <th>Danh mục</th>
                <th>Trạng thái</th>
                <th>Tác giả</th>
                <th>Ngày tạo</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
            @foreach($posts as $post)
                <tr>
                    <td>{{ $post->id }}</td>
                    <td>{{ $post->title }}</td> 
                    <td>{{ $post->category->name }}</td>
                    <td>{{ $post->status }}</td>
                    <td>{{ $post->user->name }}</td>
                    <td>{{ $post->created_at->format('d/m/Y') }}</td>
                    <td>
                        <a href="{{ route('posts.edit', $post->id) }}" class="btn btn-primary">Sửa</a>
                        <form action="{{ route('posts.destroy', $post->id) }}" method="POST" style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">Xóa</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    {{ $posts->links() }}
</div>

@endsection
