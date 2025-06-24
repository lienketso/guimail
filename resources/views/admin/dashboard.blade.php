@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')
<div class="container mt-4">
    <h3 class="mb-4">Chào mừng {{ Auth::user()->name }} đến với trang quản trị !</h3>
    <div class="row g-4">
        <div class="col-md-4">
            <div class="card text-center shadow-sm">
                <div class="card-body">
                    <div class="mb-2" style="font-size:2.5rem;color:#6366f1;"><i class="bi bi-building"></i></div>
                    <h5 class="card-title">Công ty</h5>
                    <p class="display-6 fw-bold mb-0">{{ $companyCount }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center shadow-sm">
                <div class="card-body">
                    <div class="mb-2" style="font-size:2.5rem;color:#22c55e;"><i class="bi bi-people"></i></div>
                    <h5 class="card-title">Người dùng</h5>
                    <p class="display-6 fw-bold mb-0">{{ $userCount }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center shadow-sm">
                <div class="card-body">
                    <div class="mb-2" style="font-size:2.5rem;color:#f59e42;"><i class="bi bi-file-earmark-text"></i></div>
                    <h5 class="card-title">Tài liệu</h5>
                    <p class="display-6 fw-bold mb-0">{{ $fileCount }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 