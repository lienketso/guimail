@extends('layouts.app')
@section('title', 'Thêm sản phẩm')
@section('content')
<div class="container">
    <h3 class="title-main">Thêm sản phẩm mới</h3>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('products.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="tax_code" class="form-label">Mã số thuế (MST người bán)*</label>
            <input type="text" class="form-control" id="tax_code" name="tax_code" value="{{ old('tax_code') }}" required>
        </div>
        <div class="mb-3">
            <label for="material_code" class="form-label">Mã vật tư*</label>
            <input type="text" class="form-control" id="material_code" name="material_code" value="{{ old('material_code') }}" required>
        </div>
        <div class="mb-3">
            <label for="product_name" class="form-label">Tên sản phẩm*</label>
            <input type="text" class="form-control" id="product_name" name="product_name" value="{{ old('product_name') }}" required>
        </div>
        <div class="mb-3">
            <label for="unit" class="form-label">Đơn vị</label>
            <input type="text" class="form-control" id="unit" name="unit" value="{{ old('unit') }}">
        </div>
        <div class="mb-3">
            <label for="price" class="form-label">Đơn giá</label>
            <input type="number" step="0.01" class="form-control" id="price" name="price" value="{{ old('price') }}">
        </div>
        <button type="submit" class="btn btn-success">Lưu</button>
        <a href="{{ route('products.index') }}" class="btn btn-secondary">Quay lại</a>
    </form>
</div>
@endsection

