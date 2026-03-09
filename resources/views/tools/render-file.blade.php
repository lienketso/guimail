@extends('layouts.app')
@section('js')
    <!-- jQuery UI -->
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
@endsection

@section('js-init')
    <script>
        $(document).ready(function(){

            // Click chọn item
            $('#availableFields').on('click', 'li', function(){
                $(this).toggleClass('active');
            });

            $('#selectedFields').on('click', 'li', function(){
                $(this).toggleClass('active');
            });

            // Thêm field
            $('#btnAddField').click(function(){
                $('#availableFields li.active').appendTo('#selectedFields').removeClass('active');
            });

            // Xoá field
            $('#btnRemoveField').click(function(){
                $('#selectedFields li.active').appendTo('#availableFields').removeClass('active');
            });

            // Sortable
            $("#selectedFields").sortable({
                placeholder: "sortable-placeholder",
                forcePlaceholderSize: true,
                cursor: "move",
                opacity: 0.8,
                tolerance: "pointer"
            });

            $("#selectedFields").disableSelection();

            //import

            $('#formImport').submit(function(e){

                e.preventDefault();

                let formData = new FormData(this);
                
                // Bật loading
                $('#loadingOverlay').removeClass('d-none');
                $('#formImport button[type=submit]')
                    .prop('disabled', true)
                    .html('<span class="spinner-border spinner-border-sm"></span> Đang import...');
                let uploadedFilePath = '';
                $.ajax({
                    url: "{{ route('product.import') }}",
                    type: "POST",
                    data: formData,
                    contentType: false,
                    processData: false,

                    success: function(res){

                        if(res.status){

                            uploadedFilePath = res.file_path;
                            $('#hiddenFilePath').val(res.file_path);

                            $('#exportSection').fadeIn();
                            $('#availableFields').html('');

                            res.headers.forEach(function(item){
                                if(item && item.trim() !== ''){
                                    $('#availableFields').append(`
                            <li class="list-group-item d-flex justify-content-between align-items-center"
                                data-field="${item}">
                                <span><i class="bi bi-list"></i> ${item}</span>
                            </li>
                        `);
                                }
                            });
                        }
                    },

                    error: function(xhr){
                        alert("Có lỗi xảy ra khi đọc file Excel");
                    },

                    complete: function(){

                        // Tắt loading
                        $('#loadingOverlay').addClass('d-none');
                        $('#formImport button[type=submit]')
                            .prop('disabled', false)
                            .html('Import');
                    }
                });

            });

            // Export
            $('#btnExport').click(function(){

                let fields = [];

                $('#selectedFields li').each(function(){
                    fields.push($(this).data('field'));
                });

                if(fields.length === 0){
                    alert("Chọn ít nhất 1 cột");
                    return;
                }

                let filePath = $('#hiddenFilePath').val();

                if(!filePath){
                    alert("File không tồn tại");
                    return;
                }

                // ===== BẬT LOADING =====
                $('#exportLoadingOverlay').removeClass('d-none');

                let btn = $('#btnExport');
                btn.prop('disabled', true)
                    .html('<span class="spinner-border spinner-border-sm"></span> Đang export...');

                $.ajax({
                    url: "{{ route('product.export') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        fields: fields,
                        file_path: filePath
                    },
                    xhrFields: {
                        responseType: 'blob'
                    },

                    success: function(blob, status, xhr){

                        // Tạo link tải file
                        let disposition = xhr.getResponseHeader('Content-Disposition');
                        let fileName = "export.xlsx";

                        if(disposition && disposition.indexOf('filename=') !== -1){
                            fileName = disposition
                                .split('filename=')[1]
                                .replace(/"/g, '');
                        }

                        let link = document.createElement('a');
                        link.href = window.URL.createObjectURL(blob);
                        link.download = fileName;
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);
                    },

                    error: function(){
                        alert("Có lỗi khi export file");
                    },

                    complete: function(){

                        // ===== TẮT LOADING =====
                        $('#exportLoadingOverlay').addClass('d-none');

                        btn.prop('disabled', false)
                            .html('<i class="bi bi-file-earmark-excel"></i> Export Excel');
                    }
                });

            });

        });
    </script>
@endsection
@section('content')

    <div class="container mt-4">

        <!-- PAGE TITLE -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">Công cụ Import & Export Excel</h4>
        </div>

        <!-- IMPORT CARD -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                Import File Excel
            </div>
            <div class="card-body">
                <div id="loadingOverlay" class="loading-overlay d-none">
                    <div class="spinner-border text-primary" role="status"></div>
                    <div class="mt-2">Đang xử lý file Excel...</div>
                </div>
                <form id="formImport" enctype="multipart/form-data">
                    @csrf
                    <div class="row align-items-end">
                        <div class="col-md-6">
                            <label class="form-label">Chọn file Excel</label>
                            <input type="file" name="excel_file" class="form-control" accept=".xlsx,.xls">
                            <input type="hidden" id="hiddenFilePath">
                        </div>

                        <div class="col-md-3">
                            <button type="submit" class="btn btn-success w-100">
                                <i class="bi bi-upload"></i> Import
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- EXPORT SECTION -->
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white">
                Tùy chọn Export
            </div>
            <div class="card-body">

                <div class="row">

                    <!-- AVAILABLE FIELDS -->
                    <div class="col-md-5">
                        <h6>Danh sách cột dữ liệu ( Chọn tối thiểu 1 cột để export)</h6>
                        <div id="exportSection" style="display:none;">
                            <ul id="availableFields" class="list-group min-height">
                                <li class="list-group-item d-flex justify-content-between align-items-center"
                                    data-field="material_code">
                                    <span><i class="bi bi-list"></i> Mã vật tư</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center"
                                    data-field="product_name">
                                    <span><i class="bi bi-list"></i> Tên sản phẩm</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center"
                                    data-field="unit"><span><i class="bi bi-list"></i> Đơn vị</span></li>
                                <li class="list-group-item d-flex justify-content-between align-items-center"
                                    data-field="price"> <span><i class="bi bi-list"></i> Giá</span></li>
                                <li class="list-group-item d-flex justify-content-between align-items-center"
                                    data-field="category"> <span><i class="bi bi-list"></i> Danh mục</span></li>
                                <li class="list-group-item d-flex justify-content-between align-items-center"
                                    data-field="origin"> <span><i class="bi bi-list"></i> Xuất xứ</span></li>
                                <li class="list-group-item d-flex justify-content-between align-items-center"
                                    data-field="created_at"> <span><i class="bi bi-list"></i> Ngày tạo</span></li>
                            </ul>
                        </div>
                    </div>

                    <!-- BUTTONS -->
                    <div class="col-md-2 d-flex flex-column justify-content-start ">
                        <button id="btnAddField" class="btn btn-outline-primary mb-2">Thêm cột</button>
                        <button id="btnRemoveField" class="btn btn-outline-danger">Bỏ cột</button>
                    </div>

                    <!-- SELECTED FIELDS -->
                    <div class="col-md-5">
                        <h6>Cột sẽ export (kéo thả để sắp xếp)</h6>
                        <ul id="selectedFields" class="list-group min-height border-primary">
                        </ul>
                    </div>

                </div>

                <hr>

                <div class="text-end">
                    <button id="btnExport" class="btn btn-success">
                        <i class="bi bi-file-earmark-excel"></i> Export Excel
                    </button>
                </div>

            </div>
        </div>

    </div>

@endsection
