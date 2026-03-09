@extends('layouts.app')
@section('title', 'Danh sách sản phẩm')
@section('content')
<div class="container-fluid">
    <h3 class="title-main">Danh sách sản phẩm</h3>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="row g-2 mb-3 mt-3">
        <div class="col-md-6">
            <form class="row g-2" method="GET" action="{{ route('products.index') }}">
                <div class="col-8">
                    <input type="text" class="form-control" name="q" value="{{ request('q') }}" placeholder="Tìm theo tên, mã vật tư, MST...">
                </div>
                <div class="col-4">
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search"></i> Tìm kiếm</button>
                </div>
            </form>
        </div>
        <div class="col-md-6 text-end">
            <form action="{{ route('products.import') }}" method="POST" enctype="multipart/form-data" class="d-inline-block me-2">
                @csrf
                <input type="file" name="file" accept=".xlsx,.xls" required class="form-control d-inline-block" style="width:220px; display:inline-block;">
                <button type="submit" class="btn btn-success ms-1"><i class="bi bi-upload me-1"></i> Import sản phẩm</button>
                <a class="download-template-file ms-2" href="{{ asset('images/import-products.xlsx') }}" target="_blank">
                    <i class="bi bi-file-earmark-arrow-down me-1"></i> File import mẫu
                </a>
            </form>
            <a href="{{ route('products.create') }}" class="btn btn-outline-primary"><i class="bi bi-plus-circle me-1"></i> Thêm sản phẩm</a>
        </div>
    </div>

    <table class="table table-bordered table-striped">
        <thead>
        <tr>
            <th>ID</th>
            <th>Mã vật tư</th>
            <th>Tên sản phẩm</th>
            <th>MST</th>
            <th>Đơn vị</th>
            <th>Đơn giá</th>
            <th>Thao tác</th>
        </tr>
        </thead>
        <tbody>
        @forelse($products as $product)
            <tr>
                <td>{{ $product->id }}</td>
                <td>{{ $product->material_code }}</td>
                <td>{{ $product->product_name }}</td>
                <td>{{ $product->tax_code }}</td>
                <td>{{ $product->unit }}</td>
                <td>{{ $product->price ? number_format($product->price, 0, ',', '.') : '' }}</td>
                <td>
                    <a href="{{ route('products.edit', $product->id) }}" class="btn btn-warning btn-sm">
                        <i class="bi bi-pencil-square"></i> Sửa
                    </a>
                    <form action="{{ route('products.destroy', $product->id) }}" method="POST" style="display:inline-block" onsubmit="return confirm('Bạn có chắc muốn xóa sản phẩm này?')">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger btn-sm">
                            <i class="bi bi-trash"></i> Xóa
                        </button>
                    </form>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="text-center">Chưa có sản phẩm nào.</td>
            </tr>
        @endforelse
        </tbody>
    </table>

    <div class="d-flex justify-content-center">
        {{ $products->links() }}
    </div>
</div>
@endsection

