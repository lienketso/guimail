@extends('layouts.app')
@section('title', 'Danh sách danh mục')
@section('content')
<div class="container">
    <h3 class="title-main">Danh sách danh mục</h3>
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
   
    <form class="row g-2 mb-3 mt-3" method="GET" action="{{ route('categories.index') }}">
        <div class="col-md-5">
            <input type="text" class="form-control" name="name" value="{{ request('name') }}" placeholder="Tìm theo tên danh mục...">
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search"></i> Tìm kiếm</button>
        </div>
        @if(Auth::user()->role === 'admin')
        <div class="col-md-2">
            <a href="{{ route('categories.create') }}" class="btn btn-primary w-100"><i class="bi bi-plus-circle"></i> Thêm danh mục</a>
        </div>
        @endif
    </form>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Tên danh mục</th>
                <th>Thứ tự</th>
                <th>Trạng thái</th>
                @if(Auth::user()->role === 'admin')
                <th>Hành động</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach($categories as $category)
            <tr>
                <td>{{ $category->id }}</td>
                <td>{{ $category->name }}</td>
                <td>{{ $category->sort_order }}</td>
                <td>{{ $category->status }}</td>
                @if(Auth::user()->role === 'admin')
                <td>
                    <a href="{{ route('categories.edit', $category->id) }}" class="btn btn-warning btn-sm"><i class="bi bi-pencil-square"></i> Sửa</a>
                    <form action="{{ route('categories.destroy', $category->id) }}" method="POST" style="display:inline-block" onsubmit="return confirm('Bạn có chắc muốn xóa?')">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger btn-sm"><i class="bi bi-trash"></i> Xóa</button>
                    </form>
                </td>
                @endif
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="d-flex justify-content-center">
        {{ $categories->links() }}
    </div>
</div>
@endsection 
