<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Quản trị')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.3.12/themes/default/style.min.css" />
    <link rel="stylesheet" href="{{ asset('css/style.css') }}" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="{{asset('libs/confirm/jquery-confirm.css')}}">
{{--    <link rel="stylesheet" href="{{ asset('css/chatbot.css') }}">--}}
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
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('taxcode.tree.form') ? 'active' : '' }}" href="{{ route('taxcode.tree.form') }}">Sửa dữ liệu</a>
                </li>
                @endif
                <li class="nav-item">
                    <a style="color: yellow !important;" class="nav-link {{ request()->routeIs('taxcode.form') ? 'active' : '' }}" href="{{ route('taxcode.form') }}">Tra cứu mã số thuế</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle {{ request()->routeIs('categories.*') ? 'active' : '' }}" href="{{ route('categories.index') }}" id="categoriesDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Quản lý bài viết
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="categoriesDropdown">
                        <li><a class="dropdown-item" href="{{ route('categories.index') }}">Danh mục</a></li>
                        <li><a class="dropdown-item" href="{{ route('posts.index') }}">Bài viết</a></li>
                        <li><a class="dropdown-item" href="{{ route('posts.create') }}">Thêm bài viết</a></li>
                    </ul>
                </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.task.index.get') ? 'active' : '' }}"
                           href="{{ route('admin.task.index.get') }}">Quản lý công việc 
                           @php
                               $pendingCount = \App\Http\Controllers\TaskController::getPendingTaskCount();
                           @endphp
                           @if($pendingCount > 0)
                               <span class="count-task">{{ $pendingCount }}</span>
                           @endif
                        </a>
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
<div class="content-main mt-4">
    @yield('content')
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{asset('libs/confirm/jquery-confirm.js')}}"></script>
{{--<script src="{{ asset('js/chatbot.js') }}"></script>--}}
@yield('js')
@yield('js-init')
@stack('js')
@stack('js-init')
<!-- Chatbot Widget -->
<!-- <div id="chatbot-widget">
    <div id="chatbot-header">
        <span>Hỗ trợ AI</span>
        <button id="chatbot-toggle">−</button>
    </div>
    <div id="chatbot-body">
        <div id="chatbot-messages"></div>
        <div id="chatbot-input-area">
            <input type="text" id="chatbot-input" placeholder="Nhập câu hỏi...">
            <button id="chatbot-send">Gửi</button>
        </div>
    </div>
</div>
<button id="chatbot-show-btn" style="display:none;">Chat</button> -->
<script type="text/javascript">
    // auto close
    $('.confirm-class').on('click', function(e){
        e.preventDefault();
        let _this = $(e.currentTarget);
        let url = _this.attr('data-url');
        $.confirm({
            title: 'Xác nhận xóa',
            content: 'Bạn có chắc chắn muốn xóa dữ liệu không',
            autoClose: 'cancelAction|10000',
            escapeKey: 'cancelAction',
            buttons: {
                confirm: {
                    btnClass: 'btn-red',
                    text: 'Xóa dữ liệu',
                    action: function(){
                        location.href = url;
                    }
                },
                cancelAction: {
                    text: 'Hủy',
                    action: function(){
                        $.alert('Đã hủy xóa dữ liệu !');
                    }
                }
            }
        });
    });

</script>
</body>
</html>
