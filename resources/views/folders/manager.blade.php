@extends('layouts.app')
@section('js-init')
<script>
    document.addEventListener('DOMContentLoaded', function() {
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
        document.getElementById('btnSaveNgayNop').onclick = function() {
            var folderId = document.getElementById('inputFolderId').value;
            var ngayNop = document.getElementById('inputNgayNop').value;
            if (!ngayNop) { alert('Vui lòng chọn ngày nộp!'); return; }
            fetch('/folders/' + folderId + '/ngay-nop', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('input[name=_token]').value
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
    });
</script>
@endsection
@section('content')
<div class="content-page">
    <h4 class="title-main">Dữ liệu công ty</h4>
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
            <span class="company-info-value"><strong>{{ $company->phone }}</strong></span>
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
    </div>

    @if(count($folderTree))
        <ul class="folder-list">
            @foreach($folderTree as $year)
                <li>
                    <div class="folder-title" style="display: flex; align-items: center; justify-content: space-between;">
                        <span><i class="fa fa-folder folder-icon"></i> {{ $year->name }}</span>
                        <button type="button" class="btn btn-sm toggle-folder-row" data-year="{{ $year->name }}"><i class="fa fa-chevron-down"></i></button>
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
                                <div class="folder-col">
                                    <div class="folder-title">
                                        <i class="fa fa-folder folder-icon"></i> {{ $group->name }}
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
                                                @include('folders._folder_recursive', ['folder' => $sub])
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
@endsection

