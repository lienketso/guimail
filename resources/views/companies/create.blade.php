@extends('layouts.app')
@section('title', 'Thêm công ty')
@section('content')
    <h3 class="title-main">Thêm công ty mới</h3>
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    <form action="{{ route('companies.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="mb-3">
            <label for="name" class="form-label">Tên công ty(*)</label>
            <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required>
        </div>
        <div class="mb-3">
            <label for="tax_code" class="form-label">Mã số thuế(*)</label>
            <input type="text" class="form-control" id="tax_code" name="tax_code" value="{{ old('tax_code') }}" required>
        </div>
        <div class="mb-3">
            <label for="founded_year" class="form-label">Năm thành lập(*)</label>
            <input type="number" class="form-control" id="founded_year" name="founded_year" value="{{ old('founded_year') }}" min="1800" max="2100">
        </div>
        <div class="mb-3">
            <label for="phone" class="form-label">Số điện thoại</label>
            <input type="text" class="form-control" id="phone" name="phone" value="{{ old('phone') }}" required>
        </div>
        <div class="mb-3">
            <label for="address" class="form-label">Địa chỉ</label>
            <input type="text" class="form-control" id="address" name="address" value="{{ old('address') }}" required>
        </div>
        <div class="mb-3">
            <label for="ceo_name" class="form-label">Người đại diện </label>
            <input type="text" class="form-control" id="ceo_name" name="ceo_name" value="{{ old('ceo_name') }}" required>
        </div>
        <div class="mb-3">
            <label for="logo" class="form-label">Logo</label>
            <input type="file" class="form-control" id="logo" name="logo" >
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Mô tả</label>
            <textarea class="form-control" id="description" name="description" rows="3">{{ old('description') }}</textarea>
        </div>
        <button type="submit" class="btn btn-success">Lưu</button>
        <a href="{{ route('companies.index') }}" class="btn btn-secondary">Quay lại</a>
    </form>
@endsection
