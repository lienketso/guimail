<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Quản trị')</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.3.12/themes/default/style.min.css" />
    <link rel="stylesheet" href="{{ asset('css/style.css') }}" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark fixed-top">
    <div class="container-fluid">
        <a class="navbar-brand" href="{{ route('admin.dashboard') }}">Quản trị</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                @if(Auth::user()?->role === 'admin')
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('companies.*') ? 'active' : '' }}" href="{{ route('companies.index') }}">Quản lý công ty</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}">Quản lý user</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('logs.login') ? 'active' : '' }}" href="{{ route('logs.login') }}">Lịch sử đăng nhập</a>
                </li>
                @endif
                <li class="nav-item">
                    <a style="color: yellow !important;" class="nav-link {{ request()->routeIs('taxcode.form') ? 'active' : '' }}" href="{{ route('taxcode.form') }}">Tra cứu mã số thuế</a>
                </li>
            </ul>
            <span class="navbar-text me-3">
                {{ Auth::user()->name ?? '' }}
                @if(Auth::user()?->role === 'admin')
                    <span class="badge badge-admin ms-1">Admin</span>
                @elseif(Auth::user()?->role === 'user')
                    <span class="badge badge-user ms-1">User</span>
                @endif
            </span>
            <form action="{{ route('logout') }}" method="POST" class="d-inline">
                @csrf
                <button class="btn btn-outline-light btn-sm">Đăng xuất</button>
            </form>
        </div>
    </div>
</nav>
<div class="container mt-4">
    @yield('content')
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
@yield('js')
@yield('js-init')
@stack('js')
@stack('js-init')
</body>
</html> 