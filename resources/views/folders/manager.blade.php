@extends('layouts.app')
@section('title', 'Quản lý dữ liệu công ty')
@section('js-init')
<meta name="csrf-token" content="{{ csrf_token() }}">
<script>
    document.addEventListener('DOMContentLoaded', function() {
        $('.folder-row').hide();
        document.querySelectorAll('.toggle-folder-row').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var year = btn.getAttribute('data-year');
                var row = document.querySelector('.folder-row[data-year="' + year + '"]');  
                if (row) {
                    row.style.display = (row.style.display === 'none') ? '' : 'none';
                }
            });
        });
        document.querySelectorAll('.add-ngay-nop').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var folderId = btn.getAttribute('data-folder-id');
                document.getElementById('inputFolderId').value = folderId;
                document.getElementById('inputNgayNop').value = '';
                var modal = new bootstrap.Modal(document.getElementById('modalNgayNop'));
                modal.show();
            });
        });
        document.querySelectorAll('.add-subfolder').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var parentFolderId = btn.getAttribute('data-parent-folder-id');
                document.getElementById('inputParentFolderId').value = parentFolderId;
                document.getElementById('inputSubfolderName').value = '';
                var modal = new bootstrap.Modal(document.getElementById('modalAddSubfolder'));
                modal.show();
            });
        });
        document.querySelectorAll('.upload-file-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var folderId = btn.getAttribute('data-folder-id');
                document.getElementById('uploadFolderId').value = folderId;
                document.getElementById('file-upload').value = '';
                var modal = new bootstrap.Modal(document.getElementById('modalUploadFile'));
                modal.show();
            });
        });
        document.getElementById('btnSaveNgayNop').onclick = function() {
            var folderId = document.getElementById('inputFolderId').value;
            var ngayNop = document.getElementById('inputNgayNop').value;
            if (!ngayNop) { alert('Vui lòng chọn ngày nộp!'); return; }
            var url = '{{ route('folders.setNgayNop', ['folder' => 'FOLDER_ID']) }}'.replace('FOLDER_ID', folderId);
            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ ngay_nop: ngayNop })
            }).then(res => res.json()).then(data => {
                if (data.success) {
                    alert('Đã lưu ngày nộp!');
                    var modal = bootstrap.Modal.getInstance(document.getElementById('modalNgayNop'));
                    modal.hide();
                } else {
                    alert('Lỗi: ' + (data.message || 'Không thể lưu ngày nộp'));
                }
            });
        };
        document.getElementById('btnSaveSubfolder').onclick = function() {
            var parentFolderId = document.getElementById('inputParentFolderId').value;
            var subfolderName = document.getElementById('inputSubfolderName').value;
            if (!subfolderName) { alert('Vui lòng nhập tên thư mục con!'); return; }
            fetch('{{ route("folders.store") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ 
                    name: subfolderName,
                    parent_id: parentFolderId,
                    company_id: '{{ $company->id }}'
                })
            }).then(res => {
                console.log('Response status:', res.status);
                return res.json();
            }).then(data => {
                console.log('Response data:', data);
                if (data.success) {
                    alert('Đã tạo thư mục con thành công!');
                    var modal = bootstrap.Modal.getInstance(document.getElementById('modalAddSubfolder'));
                    modal.hide();
                    // Reload trang để hiển thị thư mục mới
                    location.reload();
                } else {
                    alert('Lỗi: ' + (data.message || 'Không thể tạo thư mục con'));
                }
            }).catch(error => {
                console.error('Error:', error);
                alert('Lỗi kết nối: ' + error.message);
            });
        };
        document.getElementById('form-upload-file').onsubmit = function(e) {
            e.preventDefault();
            var formData = new FormData(this);
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
            fetch("{{ route('folders.upload') }}", {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.id) {
                    alert('Upload file thành công!');
                    var modal = bootstrap.Modal.getInstance(document.getElementById('modalUploadFile'));
                    modal.hide();
                    location.reload();
                } else {
                    alert('Lỗi: ' + (data.message || 'Không thể upload file'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Lỗi kết nối: ' + error.message);
            });
        };
    });
</script>
@endsection
@section('content')
<div class="container-fluid">
<div class="content-page">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="title-main">Dữ liệu công ty</h4>
    </div>
    <div class="company-info">
        <div class="company-info-item">
            <span class="company-info-label">Tên công ty:</span>
            <span class="company-info-value"><strong>{{ $company->name }}</strong></span>
        </div>
        <div class="company-info-item">
            <span class="company-info-label">Mã số thuế:</span>
            <span class="company-info-value"><strong>{{ $company->tax_code }}</strong></span>
        </div>
        <div class="company-info-item">
            <span class="company-info-label">Địa chỉ:</span>
            <span class="company-info-value"><strong>{{ $company->address }}</strong></span>
        </div>
        <div class="company-info-item">
            <span class="company-info-label">Điện thoại:</span>
            <span class="company-info-value"><strong><a href="tel:{{ $company->phone }}">{{ $company->phone }}</a></strong> <i class="fa fa-phone"></i></span>
        </div>
        <div class="company-info-item">
            <span class="company-info-label">Email:</span>
            <span class="company-info-value"><strong><a href="mailto:{{ $company->email }}">{{ $company->email }}</a></strong> <i class="fa fa-envelope"></i></span>
        </div>
        <div class="company-info-item">
            <span class="company-info-label">Người đại diện:</span>
            <span class="company-info-value"><strong>{{ $company->ceo_name }}</strong></span>
        </div>
        <div class="company-info-item">
            <span class="company-info-label">Năm thành lập:</span>
            <span class="company-info-value"><strong>{{ $company->founded_year }}</strong></span>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="form-upload-xml">
        <form action="{{ route('folders.uploadXml', $company->id) }}" method="POST" enctype="multipart/form-data" style="margin-bottom: 20px;">
            @csrf
            <label>Chọn file XML (có thể chọn nhiều):</label>
            <input type="file" name="xml_files[]" accept=".xml" multiple required>
            <button type="submit" class="btn btn-primary">Upload XML</button>
        </form>
        <a href="{{ route('folders.yearly-manager', ['tax_code' => $company->tax_code]) }}" class="btn btn-outline-primary mb-3">
            <i class="fa fa-clock"></i> Xem theo dòng thời gian
        </a>
    </div>

    @if(count($folderTree))
        <ul class="folder-list">
            @foreach($folderTree as $year)
                <li>
                    <div class="folder-title" style="display: flex; align-items: center; justify-content: space-between;">
                        <span><i class="fa fa-folder folder-icon"></i> {{ $year->name }}</span>
                        <div>
                            <button type="button" class="btn btn-sm add-subfolder" data-parent-folder-id="{{ $year->id }}" title="Thêm thư mục con">
                                <i class="fa fa-plus"></i>
                            </button>
                            <button type="button" class="btn btn-sm upload-file-btn" data-folder-id="{{ $year->id }}" title="Upload file">
                                <i class="fa fa-cloud-upload"></i>
                            </button>
                            <button type="button" class="btn btn-sm toggle-folder-row" data-year="{{ $year->name }}"><i class="fa fa-chevron-down"></i></button>
                        </div>
                    </div>
                    @if(count($year->files))
                        <ul class="file-list">
                            @foreach($year->files as $file)
                                <li>
                                    <i class="fa fa-file file-icon"></i>
                                    <a class="file-link" href="{{ route('folders.download', $file->id) }}" target="_blank">{{ $file->name }}</a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                    @if(count($year->children_tree))
                        <div class="folder-row" data-year="{{ $year->name }}">
                            @foreach($year->children_tree as $group)
                                <div class="folder-col tree-branch">
                                    <div class="folder-title" style="display: flex; align-items: center; justify-content: space-between;">
                                        <span><i class="fa fa-folder folder-icon"></i> {{ $group->name }}</span>
                                        <div>
                                        <button type="button" class="btn btn-sm  add-subfolder" data-parent-folder-id="{{ $group->id }}" title="Thêm thư mục con">
                                            <i class="fa fa-plus"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm upload-file-btn" data-folder-id="{{ $group->id }}" title="Upload file">
                                            <i class="fa fa-cloud-upload"></i>
                                        </button>
                                        </div>
                                    </div>
                                    @if(count($group->files))
                                        <ul class="file-list">
                                            @foreach($group->files as $file)
                                                <li>
                                                    <i class="fa fa-file file-icon"></i>
                                                    <a class="file-link" href="{{ route('folders.download', $file->id) }}" target="_blank">{{ $file->name }}</a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                    @if(count($group->children_tree))
                                        <ul class="folder-list">
                                            @foreach($group->children_tree as $sub)
                                                @include('folders._folder_recursive', ['folder' => $sub, 'class' => 'tree-branch'])
                                            @endforeach
                                        </ul>
                                    @endif
                                    @if($group->name && Str::contains(Str::lower($group->name), 'lần'))
                                        <button type="button" class="btn btn-sm btn-link add-ngay-nop" 
                                        data-folder-id="{{ $group->id }}"><i class="fa fa-calendar-plus"></i></button>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </li>
            @endforeach
        </ul>
    @else
        <p>Chưa có dữ liệu thư mục.</p>
    @endif
</div>
<!-- Modal nhập ngày nộp -->
<div class="modal fade" id="modalNgayNop" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Nhập ngày nộp</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="date" class="form-control" id="inputNgayNop">
        <input type="hidden" id="inputFolderId">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" id="btnSaveNgayNop">Lưu</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
      </div>
    </div>
  </div>
</div>
</div>

<!-- Modal thêm thư mục con -->
<div class="modal fade" id="modalAddSubfolder" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Thêm thư mục con</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label for="inputSubfolderName" class="form-label">Tên thư mục con</label>
          <input type="text" class="form-control" id="inputSubfolderName" placeholder="Nhập tên thư mục con">
        </div>
        <input type="hidden" id="inputParentFolderId">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-success" id="btnSaveSubfolder">Tạo</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal upload file -->
<div class="modal fade" id="modalUploadFile" tabindex="-1">
  <div class="modal-dialog">
    <form id="form-upload-file" class="modal-content" enctype="multipart/form-data">
      <div class="modal-header">
        <h5 class="modal-title">Upload file</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label for="file-upload" class="form-label">Chọn file</label>
          <input type="file" class="form-control" id="file-upload" name="file" required>
        </div>
        <input type="hidden" id="uploadFolderId" name="folder_id">
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-success">Upload</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
      </div>
    </form>
  </div>
</div>
@endsection

