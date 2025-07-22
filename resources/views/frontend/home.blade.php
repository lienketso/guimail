@extends('frontend.master')
@section('js-init')
    
@endsection
@section('content')
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-3">
                <div class="category-sidebar">
                    <h3><i class="fa fa-list"></i> Tài liệu & văn bản</h3>
                    <ul class="nav flex-column nav-pills" id="categoryTab" role="tablist" aria-orientation="vertical">
                        @foreach ($categories as $key => $category)
                            <li class="nav-item" role="presentation">
                                <a class="nav-link {{ $key == 0 ? 'active' : '' }}" id="cat{{ $category->id }}-tab"
                                    data-bs-toggle="pill" href="#cat{{ $category->id }}" role="tab"
                                    aria-controls="cat{{ $category->id }}" aria-selected="false">{{ $category->name }}</a>
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
                <div class="tab-content" id="categoryTabContent">
                    @foreach ($categories as $key => $category)
                        <div class="tab-pane fade {{ $key == 0 ? 'show active' : '' }}" id="cat{{ $category->id }}"
                            role="tabpanel" aria-labelledby="cat{{ $category->id }}-tab">
                            <h4 class="mb-4" style="color:#604188;"><i class="fa fa-star"></i> {{ $category->name }}</h4>
                            @foreach ($category->posts as $post)
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <h5 class="card-title">
                                            <a
                                                href="{{ route('frontend.posts.detail', $post->slug) }}">{{ $post->title }}</a>

                                        </h5>
                                        <p class="card-text">{{ $post->description }}</p>
                                        <small class="text-muted">{{ $post->created_at->format('d/m/Y') }}</small>
                                        @if ($post->file_attach)
                                            <a href="{{ asset('uploads/posts/' . $post->file_attach) }}" target="_blank"
                                                class="download-link" title="Tải tài liệu"><i
                                                    class="fa fa-download"></i></a>
                                        @endif
                                        <div class="btn-detail">
                                            <a href="{{ route('frontend.posts.detail', $post->slug) }}"
                                                class="btn-detail-link">Xem chi tiết <i class="fa fa-arrow-right"></i></a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endforeach
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
