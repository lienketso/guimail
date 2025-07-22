@extends('frontend.master')
@section('title', $category->name)
@section('js-init')

@endsection
@section('content')

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-3">
                <div class="category-sidebar">
                    <h3><i class="fa fa-list"></i> Tài liệu & văn bản</h3>
                    <ul class="nav flex-column nav-pills">
                        @foreach ($categories as $key => $cat)
                            <li class="nav-item" role="presentation">
                                <a class="nav-link {{ $cat->id == $category->id ? 'active' : '' }}"
                                    href="{{ route('frontend.posts.list', $cat->slug) }}">{{ $cat->name }}</a>
                            </li>
                        @endforeach
                    </ul>
                    <div class="link-admin">
                        @if (Auth::check())
                            <a href="{{ route('admin.dashboard') }}" class="btn btn-primary"><i class="fa fa-list"></i> Vào
                                trang admin</a>
                        @else
                            <a href="{{ route('login') }}" class="btn btn-primary"><i class="fa fa-user"></i> Đăng nhập quản
                                lý</a>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-9">
                <div class="tab-content-detail" id="detail-content">
                    <div class="post-header">
                        <a href="{{ route('frontend.home') }}" class="btn btn-primary"><i class="fa fa-home"></i> Quay
                            lại</a>
                    </div>

                    <h1 style="color:#604188;"><i class="fa fa-star"></i> {{ $category->name }}</h1>
                    @foreach ($posts as $post)
                        <div class="card mb-3">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <a href="{{ route('frontend.posts.detail', $post->slug) }}">{{ $post->title }}</a>
                                </h5>
                                <p class="card-text">{{ $post->description }}</p>
                                <small class="text-muted">{{ $post->created_at->format('d/m/Y') }}</small>
                                @if ($post->file_attach)
                                    <a href="{{ asset('uploads/posts/' . $post->file_attach) }}" target="_blank"
                                        class="download-link" title="Tải tài liệu"><i class="fa fa-download"></i></a>
                                @endif
                                <div class="btn-detail">
                                    <a href="{{ route('frontend.posts.detail', $post->slug) }}" class="btn-detail-link">Xem
                                        chi tiết <i class="fa fa-arrow-right"></i></a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                    <div class="pagination-container">
                        {{ $posts->links('pagination::bootstrap-4') }}
                    </div>
                </div>



                <!-- Box chăm sóc khách hàng -->
                <div class="row justify-content-center mt-5">
                    <div class="col-md-12">
                        @include('frontend.partials.support')
                    </div>
                </div>

            </div>
        </div>



    </div>
    <!-- Nhớ include Bootstrap JS nếu chưa có -->
@endsection
