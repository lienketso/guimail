@extends('layouts.app')
@section('title', 'Sửa user')
@section('content')
<div class="container">
    <h3 class="title-main">Sửa user</h3>
    <form action="{{ route('users.update', $user->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="name" class="form-label">Tên *</label>
            <input type="text" class="form-control" id="name" name="name" value="{{ $user->name }}" required>
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">Email*</label>
            <input type="email" class="form-control" id="email" name="email" value="{{ $user->email }}" required>
        </div>
        <div class="mb-3"> 
            <label for="phone" class="form-label">Số điện thoại * <span class="text-muted">(có thể sử dụng để đăng nhập)</span></label>
            <input type="text" class="form-control" id="phone" name="phone" value="{{ $user->phone }}">
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Mật khẩu mới (bỏ qua nếu không đổi)</label>
            <input type="password" class="form-control" id="password" name="password">
        </div>
        <div class="mb-3">
            <label for="password_confirmation" class="form-label">Nhập lại mật khẩu mới</label>
            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation">
        </div>
        <div class="mb-3">
            <label for="role" class="form-label">Quyền</label>
            <select class="form-control" id="role" name="role" required>
                <option value="user" @if($user->role=='user') selected @endif>User</option>
                <option value="admin" @if($user->role=='admin') selected @endif>Admin</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="status" class="form-label">Trạng thái</label>
            <select class="form-control" id="status" name="status" required>
                <option value="active" @if($user->status=='active') selected @endif>Hoạt động</option>
                <option value="inactive" @if($user->status=='inactive') selected @endif>Khóa tài khoản</option>
            </select>
        </div>
        <button type="submit" class="btn btn-success">Cập nhật</button>
        <a href="{{ route('users.index') }}" class="btn btn-secondary">Quay lại</a>
    </form>
</div>
@endsection 