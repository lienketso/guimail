@extends('frontend.master')
@section('title', $post->title)
@section('js-init')
<script>
    function showCareForm(type) {
      document.getElementById('careForm-support').style.display = 'none';
      document.getElementById('careForm-feedback').style.display = 'none';
      document.querySelector('.btn-support').classList.remove('active');
      document.querySelector('.btn-feedback').classList.remove('active');
      if(type === 'support') {
        document.getElementById('careForm-support').style.display = 'block';
        document.querySelector('.btn-support').classList.add('active');
      } else {
        document.getElementById('careForm-feedback').style.display = 'block';
        document.querySelector('.btn-feedback').classList.add('active');
      }
    }
  </script>
@endsection
@section('content')

<div class="container mt-4">
  <div class="row">
    <div class="col-md-3">
      <div class="category-sidebar">
        <h3><i class="fa fa-list"></i> Tài liệu & văn bản</h3>
        <ul class="nav flex-column nav-pills" >
          @foreach($categories as $key => $cat)
            <li class="nav-item" role="presentation">
              <a class="nav-link {{ $cat->id == $post->category->id ? 'active' : '' }}" 
                 href="{{ route('frontend.posts.list', $cat->slug) }}">{{ $cat->name }}</a>
            </li>
          @endforeach
        </ul>
        <div class="link-admin">
          @if(Auth::check())
            <a href="{{ route('admin.dashboard') }}" class="btn btn-primary"><i class="fa fa-list"></i> Vào trang admin</a>
          @else
            <a href="{{ route('login') }}" class="btn btn-primary"><i class="fa fa-user"></i> Đăng nhập quản lý</a>
          @endif
        </div>
      </div>
    </div>
    <div class="col-md-9">
      <div class="tab-content-detail" id="detail-content">
        <div class="post-header">
            <a href="{{ route('frontend.home') }}" class="btn btn-primary"><i class="fa fa-home"></i> Quay lại</a>
        </div>

        <h1>{{ $post->title }}</h1>
        <div class="content-post">{!! $post->content !!}</div>
        @if($post->file_attach)
            <a href="{{ asset('uploads/posts/'.$post->file_attach) }}" target="_blank" class="download-link" 
                title="Tải tài liệu">
                <i class="fa fa-download"></i> Tải xuống</a>
        @endif
      </div>
 


        <!-- Box chăm sóc khách hàng -->
  <div class="row justify-content-center mt-5">
    <div class="col-md-12">
      <div class="customer-care-box p-4">
        <h4 class="mb-4" style="color:#604188;"><i class="fa fa-headset me-2"></i>Hỗ trợ khách hàng</h4>
        <div class="d-flex gap-3 mb-4">
          <button class="btn-care btn-support" onclick="showCareForm('support')"><i class="fa fa-comment"></i> Hỗ trợ công việc</button>
          <button class="btn-care btn-feedback" onclick="showCareForm('feedback')"><i class="fa fa-smile"></i> Phản ánh dịch vụ</button>
        </div>
        <div id="careForm-support" class="care-form" style="display:none;">
          <form>
            <div class="mb-3">
              <label class="form-label">Tiêu đề</label>
              <input type="text" class="form-control" placeholder="Nhập tiêu đề hỗ trợ">
            </div>
            <div class="mb-3">
              <label class="form-label">Nội dung</label>
              <textarea class="form-control" rows="4" placeholder="Nhập nội dung cần hỗ trợ"></textarea>
            </div>
            <button type="submit" class="btn btn-submit">Gửi hỗ trợ</button>
          </form>
        </div>
        <div id="careForm-feedback" class="care-form" style="display:none;">
          <form>
            <div class="mb-3">
              <label class="form-label">Tiêu đề</label>
              <input type="text" class="form-control" placeholder="Nhập tiêu đề phản ánh">
            </div>
            <div class="mb-3">
              <label class="form-label">Nội dung</label>
              <textarea class="form-control" rows="4" placeholder="Nhập nội dung phản ánh dịch vụ"></textarea>
            </div>
            <button type="submit" class="btn btn-submit">Gửi phản ánh</button>
          </form>
        </div>
      </div>
    </div>
  </div>

    </div>
  </div>



</div>
<!-- Nhớ include Bootstrap JS nếu chưa có -->
@endsection