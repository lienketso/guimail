@extends('layouts.app')
@section('title', 'Danh sách công ty')
@section('content')
    <h3 class="title-main">Danh sách công ty</h3>
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(Auth::user()->role === 'admin')
        <a href="{{ route('companies.create') }}" class="btn btn-primary mb-3">Thêm công ty</a>
    @endif
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
                    <a href="{{ route('companies.edit', $company->id) }}" class="btn btn-warning btn-sm">Sửa</a>
                    <form action="{{ route('companies.destroy', $company->id) }}" method="POST" style="display:inline-block" onsubmit="return confirm('Bạn có chắc muốn xóa?')">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger btn-sm">Xóa</button>
                    </form>
                </td>
                @endif
            </tr>
            @endforeach
        </tbody>
    </table>
@endsection 