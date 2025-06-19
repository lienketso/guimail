@extends('layouts.app')
@section('title', 'Cây thư mục')

@section('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.3.12/jstree.min.js"></script>
@endsection
@section('js-init')

<script>
    const company_id = "{{ $company_id }}";
    let selectedNode = null;
    
    $('#jstree').jstree({
        'core' : {
            'data' : {
                "url" : "{{ route('folders.data') }}",
                "data" : function (node) {
                    return { "tax_code" : "{{ $tax_code }}" };
                }
            },
            "check_callback" : true
        },
        "plugins" : [ "dnd", "contextmenu" ],
        "contextmenu": {
            "items": function(node) {
                var items = $.jstree.defaults.contextmenu.items();
                items.create.label = "Tạo thư mục con";
                items.create.action = function () {
                    selectedNode = node;
                    $('#modalCreateFolder').modal('show');
                };
                items.rename.label = "Đổi tên";
                items.rename.action = function () {
                    $('#jstree').jstree(true).edit(node);
                };
                items.remove.label = "Xóa";
                items.remove.action = function () {
                    if (confirm('Bạn có chắc muốn xóa thư mục này?')) {
                        $.ajax({
                            url: '/folders/' + node.id,
                            method: 'DELETE',
                            data: {
                                _token: "{{ csrf_token() }}"
                            },
                            success: function() {
                                $('#jstree').jstree(true).refresh();
                            },
                            error: function(xhr) {
                                alert('Lỗi: ' + (xhr.responseJSON?.message || 'Không thể xóa'));
                            }
                        });
                    }
                };
                return items;
            }
        }
    });
    
    $('#jstree').on('select_node.jstree', function (e, data) {
        selectedNode = data.node;
    });
    
    $('#jstree').on('rename_node.jstree', function (e, data) {
        $.ajax({
            url: '/folders/' + data.node.id,
            method: 'PATCH',
            data: {
                text: data.text,
                _token: "{{ csrf_token() }}"
            },
            success: function() {
                $('#jstree').jstree(true).refresh();
            },
            error: function(xhr) {
                alert('Lỗi: ' + (xhr.responseJSON?.message || 'Không thể đổi tên'));
                $('#jstree').jstree(true).refresh();
            }
        });
    });
    
    $('#jstree').on('move_node.jstree', function (e, data) {
        // Lấy danh sách id các node cùng cấp sau khi di chuyển
        var parent = data.parent;
        var children = $('#jstree').jstree(true).get_node(parent).children;
        $.ajax({
            url: "{{ route('folders.move') }}",
            method: "POST",
            data: {
                id: data.node.id,
                parent_id: parent === "#" ? null : parent,
                order: children, // mảng id theo thứ tự mới
                _token: "{{ csrf_token() }}"
            },
            success: function(res) {
                // Có thể thông báo thành công nếu muốn
            },
            error: function(xhr) {
                alert('Lỗi: ' + (xhr.responseJSON?.message || 'Không thể di chuyển thư mục'));
                $('#jstree').jstree(true).refresh();
            }
        });
    });
    //tạo thư mục gốc
    $('#btn-create-root-folder').click(function() {
        selectedNode = null;
        $('#modalCreateFolder').modal('show');
    });
    //tạo thư mục con
    $('#btn-create-folder').click(function() {
        if (!selectedNode) {
            alert('Hãy chọn thư mục cha trên cây!');
            return;
        }
        $('#modalCreateFolder').modal('show');
    });
    
    $('#form-create-folder').submit(function(e) {
        e.preventDefault();
        // Chỉ kiểm tra nếu KHÔNG phải tạo root
        // Nếu đang tạo root (selectedNode == null), cho phép luôn
        // Nếu đang tạo con (selectedNode != null), vẫn cho phép
        $.ajax({
            url: "{{ route('folders.store') }}",
            method: "POST",
            data: {
                name: $('#folder-name').val(),
                parent_id: selectedNode ? selectedNode.id : null,
                company_id: company_id,
                _token: "{{ csrf_token() }}"
            },
            success: function(res) {
                $('#jstree').jstree(true).refresh();
                $('#modalCreateFolder').modal('hide');
                $('#folder-name').val('');
            },
            error: function(xhr) {
                alert('Lỗi: ' + (xhr.responseJSON?.message || 'Không thể tạo thư mục mới'));
            }
        });
    });
    
    $('#btn-upload-file').click(function() {
        if (!selectedNode) {
            alert('Hãy chọn thư mục để upload file!');
            return;
        }
        $('#modalUploadFile').modal('show');
    });
    
    $('#form-upload-file').submit(function(e) {
        e.preventDefault();
        if (!selectedNode) {
            alert('Hãy chọn thư mục để upload file!');
            return;
        }
        let formData = new FormData(this);
        formData.append('folder_id', selectedNode.id);
        formData.append('_token', "{{ csrf_token() }}");
        $.ajax({
            url: "{{ route('folders.upload') }}",
            method: "POST",
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                alert('Upload thành công!');
                $('#modalUploadFile').modal('hide');
                $('#file-upload').val('');
            },
            error: function(xhr) {
                alert('Lỗi: ' + (xhr.responseJSON?.message || 'Không thể upload file'));
            }
        });
    });
    </script>
@endsection
@section('content')
<div class="container mt-4">
    <h4 class="title-main">Dữ liệu công ty</h4>
    <div class="company-info">
        <div class="company-info-item">
            <span class="company-info-label">Tên công ty:</span>
            <span class="company-info-value"><strong>{{ $company->name }}</strong></span>
        </div>
        <div class="company-info-item">
            <span class="company-info-label">Mã số thuế:</span>
            <span class="company-info-value"><strong>{{ $tax_code }}</strong></span>
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
    <div class="mt-3 mb-3 button-folder">
        <button class="btn btn-primary" id="btn-create-root-folder">Tạo thư mục gốc</button>
        <button class="btn btn-primary" id="btn-create-folder">Tạo thư mục</button>
        <button class="btn btn-success" id="btn-upload-file">Upload file</button>
    </div>
    <div id="jstree"></div>
    
    <button class="btn btn-secondary mt-3" onclick="location.href='{{ route('taxcode.form') }}'">Đổi mã số thuế</button>
</div>

<!-- Modal tạo thư mục -->
<div class="modal fade" id="modalCreateFolder" tabindex="-1">
  <div class="modal-dialog">
    <form id="form-create-folder" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Tạo thư mục mới</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label for="folder-name" class="form-label">Tên thư mục</label>
          <input type="text" class="form-control" id="folder-name" name="name" required>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Tạo</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
      </div>
    </form>
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
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-success">Upload</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
      </div>
    </form>
  </div>
</div>


@endsection
</body>
</html> 