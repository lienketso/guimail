@extends('layouts.app')
@section('title', 'Sửa công ty')
@section('content')
    <h3 class="title-main">Sửa công ty</h3>
    <form action="{{ route('companies.update', $company->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="name" class="form-label">Tên công ty(*)</label>
            <input type="text" class="form-control" id="name" name="name" value="{{ $company->name }}" required>
        </div>
        <div class="mb-3">
            <label for="tax_code" class="form-label">Mã số thuế(*)</label>
            <input type="text" class="form-control" id="tax_code" name="tax_code" value="{{ $company->tax_code }}" required>
        </div>
        <div class="mb-3">
            <label for="founded_year" class="form-label">Năm thành lập(*)</label>
            <input type="number" class="form-control" id="founded_year" name="founded_year" min="1800" max="2100" value="{{ $company->founded_year }}">
        </div>
        <div class="mb-3">
            <label for="phone" class="form-label">Số điện thoại</label>
            <input type="text" class="form-control" id="phone" name="phone" value="{{ $company->phone }}" required>
        </div>
        <div class="mb-3">
            <label for="address" class="form-label">Địa chỉ</label>
            <input type="text" class="form-control" id="address" name="address" value="{{ $company->address }}" required>   
        </div>
        <div class="mb-3">
            <label for="ceo_name" class="form-label">Tên giám đốc</label>
            <input type="text" class="form-control" id="ceo_name" name="ceo_name" value="{{ $company->ceo_name }}" required>
        </div>
        <div class="mb-3">
            <label for="logo" class="form-label">Logo</label>
            <input type="file" class="form-control" id="logo" name="logo" value="{{ $company->logo }}" >
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Mô tả</label>
            <textarea class="form-control" id="description" name="description" rows="3">{{ $company->description }}</textarea>
        </div>
        <button type="submit" class="btn btn-success">Cập nhật</button>
        <a href="{{ route('companies.index') }}" class="btn btn-secondary">Quay lại</a>
    </form>
@endsection 