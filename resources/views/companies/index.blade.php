@extends('layouts.app')
@section('title', 'Danh sách công ty')
@section('content')
    <h3 class="title-main">Danh sách công ty</h3>
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(Auth::user()->role === 'admin')
        <a href="{{ route('companies.create') }}" class="btn btn-primary mb-3"><i class="bi bi-plus-circle me-1"></i> Thêm công ty mới</a>
        <form action="{{ route('companies.import') }}" method="POST" enctype="multipart/form-data" class="mb-3 frm-import-company">
            @csrf
            <input type="file" name="file" accept=".xlsx,.xls" required>
            <button type="submit" class="btn btn-success"><i class="bi bi-upload me-1"></i> Import công ty (xlsx, xls)</button>
            <a class="download-template-file" href="{{ asset('images/import-comany.xlsx') }}" target="_blank"><i class="bi bi-file-earmark-arrow-down me-1"></i> File import mẫu</a>
        </form>
    @endif
    <form class="row g-2 mb-3 mt-3" method="GET" action="{{ route('companies.index') }}">
        <div class="col-md-5">
            <input type="text" class="form-control" name="name" value="{{ request('name') }}" placeholder="Tìm theo tên công ty...">
        </div>
        <div class="col-md-5">
            <input type="text" class="form-control" name="tax_code" value="{{ request('tax_code') }}" placeholder="Tìm theo mã số thuế...">
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search"></i> Tìm kiếm</button>
        </div>
    </form>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Tên công ty</th>
                <th>Mã số thuế</th>
                <th>Năm thành lập</th>
                @if(Auth::user()->role === 'admin')
                <th>Hành động</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach($companies as $company)
            <tr>
                <td>{{ $company->id }}</td>
                <td>{{ $company->name }}</td>
                <td>{{ $company->tax_code }}</td>
                <td>{{ $company->founded_year }}</td>
                @if(Auth::user()->role === 'admin')
                <td>
                    <a href="{{ route('companies.edit', $company->id) }}" class="btn btn-warning btn-sm"><i class="bi bi-pencil-square"></i> Sửa</a>
                    <form action="{{ route('companies.destroy', $company->id) }}" method="POST" style="display:inline-block" onsubmit="return confirm('Bạn có chắc muốn xóa?')">
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
        {{ $companies->links() }}
    </div>
@endsection 