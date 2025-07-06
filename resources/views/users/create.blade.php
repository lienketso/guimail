@extends('layouts.app')
@section('title', 'Thêm user')
@section('content')
<div class="container">
    <h3 class="title-main">Thêm user mới</h3>
    <form action="{{ route('users.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="name" class="form-label">Tên *</label>
            <input type="text" class="form-control" id="name" name="name" required>
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">Email *</label>
            <input type="email" class="form-control" id="email" name="email" required>
        </div>
        <div class="mb-3">
            <label for="phone" class="form-label">Số điện thoại * <span class="text-muted">(có thể sử dụng để đăng nhập)</span></label>
            <input type="text" class="form-control" id="phone" name="phone">
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Mật khẩu *</label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>
        <div class="mb-3">
            <label for="password_confirmation" class="form-label">Nhập lại mật khẩu</label>
            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
        </div>
        <div class="mb-3">
            <label for="role" class="form-label">Quyền</label>
            <select class="form-control" id="role" name="role" required>
                <option value="user">User</option>
                <option value="admin">Admin</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="status" class="form-label">Trạng thái</label>
            <select class="form-control" id="status" name="status" required>
                <option value="active">Hoạt động</option>
                <option value="inactive">Khóa tài khoản</option>
            </select>
        </div>
        <button type="submit" class="btn btn-success">Lưu</button>
        <a href="{{ route('users.index') }}" class="btn btn-secondary">Quay lại</a>
    </form>
</div>
@endsection 