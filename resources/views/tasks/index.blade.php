@extends('layouts.app')
@section('title', 'Danh sách công việc')
@section('css')
<style>
.btn-view {
    background-color: #17a2b8 !important;
    color: white !important;
}
.btn-view:hover {
    background-color: #138496 !important;
}
#task-assignee-select {
    background-color: white;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 8px;
    font-size: 14px;
    transition: border-color 0.2s;
}
#task-assignee-select:focus {
    outline: none;
    border-color: #007bff;
    box-shadow: 0 0 0 2px rgba(0,123,255,0.25);
}
#task-detail-popup .priority-option, #task-detail-popup .status-option {
    padding: 8px 12px;
    margin: 2px 0;
    border-radius: 4px;
    cursor: pointer;
    transition: background-color 0.2s;
}
#task-detail-popup .priority-option:hover, #task-detail-popup .status-option:hover {
    opacity: 0.8;
}
</style>
@endsection
@section('js-init')
<script>
$(function() {
    // Hiển thị popup khi click vào nút priority
    $('.priority').on('click', function(e) {
        e.preventDefault();
        var $btn = $(this);
        var offset = $btn.offset();
        $('#priority-popup').css({
            top: offset.top + $btn.outerHeight(),
            left: offset.left,
            display: 'block'
        }).data('task-id', $btn.data('id'))
          .data('btn', $btn);
    });

    // Ẩn popup khi click ra ngoài
    $(document).on('mousedown', function(e) {
        if (!$(e.target).closest('#priority-popup, .priority').length) {
            $('#priority-popup').hide();
        }
    });

    // Xử lý chọn độ ưu tiên
    $('.priority-option').on('click', function() {
        var value = $(this).data('value');
        var text = $(this).text();
        var $popup = $('#priority-popup');
        var taskId = $popup.data('task-id');
        var $btn = $popup.data('btn');
        // Gửi AJAX cập nhật
        $.ajax({
            url: '{{ route('admin.task.update-priority') }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                id: taskId,
                priority: value
            },
            success: function(res) {
                if(res.success) {
                    $btn.text(text)
                        .attr('data-priority', value)
                        .removeClass('option-low option-medium option-high option-important')
                        .addClass('option-' + (value == 1 ? 'low' : value == 2 ? 'medium' : value == 3 ? 'high' : 'important'));
                } else {
                    alert('Cập nhật thất bại!');
                }
                $popup.hide();
            },
            error: function() {
                alert('Có lỗi xảy ra!');
                $popup.hide();
            }
        });
    });

    // Đặt màu cho các nút priority ban đầu
    $('.priority').each(function() {
        var $btn = $(this);
        var value = $btn.data('value');
        $btn.attr('data-priority', value)
            .addClass('option-' + (value == 1 ? 'low' : value == 2 ? 'medium' : value == 3 ? 'high' : 'important'));
    });

    // Hiển thị popup khi click vào badge trạng thái
    $('.status-badge').on('click', function(e) {
        e.preventDefault();
        var $badge = $(this);
        var offset = $badge.offset();
        $('#status-popup').css({
            top: offset.top + $badge.outerHeight(),
            left: offset.left,
            display: 'block'
        }).data('task-id', $badge.data('id'))
          .data('badge', $badge)
          .data('row', $badge.closest('tr'));
    });

    // Ẩn popup khi click ra ngoài
    $(document).on('mousedown', function(e) {
        if (!$(e.target).closest('#status-popup, .status-badge').length) {
            $('#status-popup').hide();
        }
    });

    // Xử lý chọn trạng thái
    $('.status-option').on('click', function() {
        var value = $(this).data('value');
        var text = $(this).text();
        var $popup = $('#status-popup');
        var taskId = $popup.data('task-id');
        var $badge = $popup.data('badge');
        var $row = $popup.data('row');
        // Gửi AJAX cập nhật
        $.ajax({
            url: '{{ route('admin.task.update-status') }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                id: taskId,
                status: value
            },
            success: function(res) {
                if(res.success) {
                    // Đổi badge
                    $badge.text(text)
                        .removeClass('bg-danger bg-warning bg-success text-white text-dark')
                        .addClass(value == 'pending' ? 'bg-danger text-white' : value == 'processing' ? 'bg-warning text-dark' : 'bg-success text-white')
                        .attr('data-status', value);
                    // Nếu completed thì gạch giữa và xám dòng
                    if(value == 'completed') {
                        $row.addClass('row-completed');
                    } else {
                        $row.removeClass('row-completed');
                    }
                } else {
                    alert('Cập nhật trạng thái thất bại!');
                }
                $popup.hide();
            },
            error: function() {
                alert('Có lỗi xảy ra!');
                $popup.hide();
            }
        });
    });

    // Đặt class cho các dòng đã hoàn thành khi load trang
    $('tr').each(function() {
        var $row = $(this);
        var $badge = $row.find('.status-badge');
        if($badge.data('status') === 'completed') {
            $row.addClass('row-completed');
        }
    });

    // Xử lý xem chi tiết task
    $('.view-detail').on('click', function(e) {
        e.preventDefault();
        var taskId = $(this).data('id');
        
        // Gửi AJAX lấy thông tin chi tiết task
        $.ajax({
            url: "{{ route('admin.task.detail') }}",
            method: 'GET',
            data: { id: taskId },
            success: function(res) {
                if(res.success) {
                    var task = res.task;
                    
                    // Cập nhật nội dung popup
                    $('#task-title').text(task.title);
                    $('#task-content').html(task.content || 'Không có mô tả');
                    $('#task-priority').text(getPriorityText(task.priority));
                    
                    // Xử lý hiển thị người thực hiện
                    if('{{ Auth::user()->role }}' === 'admin') {
                        $('#task-assignee-select').val(task.user_id || '');
                    } else {
                        $('#task-assignee').text(task.user ? task.user.name : 'Chưa phân công');
                    }
                    
                    $('#task-type').text(task.task_type);
                    $('#task-status').text(getStatusText(task.status));
                    $('#task-created').text(formatDate(task.created_at));
                    $('#task-end-date').text(task.end_date ? formatDate(task.end_date) : 'Không có hạn');
                    
                    // Lưu task ID để sử dụng khi cập nhật
                    $('#task-detail-popup').data('task-id', taskId);
                    
                    // Hiển thị popup
                    $('#task-detail-popup').show();
                } else {
                    alert('Không thể tải thông tin task!');
                }
            },
            error: function() {
                alert('Có lỗi xảy ra khi tải thông tin task!');
            }
        });
    });

    // Đóng popup chi tiết
    $('.close-popup').on('click', function() {
        $('#task-detail-popup').hide();
    });

    // Đóng popup khi click ra ngoài
    $(document).on('mousedown', function(e) {
        if (!$(e.target).closest('#task-detail-popup, .view-detail').length) {
            $('#task-detail-popup').hide();
        }
    });

    // Xử lý thay đổi người thực hiện (chỉ admin)
    $('#task-assignee-select').on('change', function() {
        var userId = $(this).val();
        var taskId = $('#task-detail-popup').data('task-id');
        
        $.ajax({
            url: '{{ route('admin.task.update-assignee') }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                id: taskId,
                user_id: userId
            },
            success: function(res) {
                if(res.success) {
                    // Cập nhật hiển thị trong bảng
                    $('.assignee-cell[data-task-id="' + taskId + '"]').text(res.assignee_name || '---');
                    
                    // Hiển thị thông báo thành công
                    alert('Đã cập nhật người thực hiện thành công!');
                } else {
                    alert('Cập nhật thất bại: ' + (res.message || 'Có lỗi xảy ra'));
                }
            },
            error: function() {
                alert('Có lỗi xảy ra khi cập nhật người thực hiện!');
            }
        });
    });

    // Hàm helper để lấy text priority
    function getPriorityText(priority) {
        switch(parseInt(priority)) {
            case 1: return 'Thấp';
            case 2: return 'Trung bình';
            case 3: return 'Cao';
            case 4: return 'Quan trọng';
            default: return 'Không xác định';
        }
    }

    // Hàm helper để lấy text status
    function getStatusText(status) {
        switch(status) {
            case 'pending': return 'Mới';
            case 'processing': return 'Đang xử lý';
            case 'completed': return 'Đã hoàn thành';
            default: return 'Không xác định';
        }
    }

    // Hàm helper để format date
    function formatDate(dateString) {
        if (!dateString) return '';
        var date = new Date(dateString);
        return date.toLocaleDateString('vi-VN');
    }
});
</script>
@endsection
@section('content')
    <div class="container">
        <h3 class="title-main">Danh sách công việc</h3>
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        <form class="row g-2 mb-3 mt-3" method="GET" action="{{ route('admin.task.index.get') }}">
            <div class="col-md-5">
                <input type="text" class="form-control" name="title" value="{{ request('title') }}" placeholder="Tìm theo tiêu đề...">
            </div>
            @if(Auth::user()->role === 'admin')
            <div class="col-md-3">
                <select name="user_id" class="form-control">
                    <option value="">Tất cả người thực hiện</option>
                    @foreach(\App\Models\User::all() as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search"></i> Tìm kiếm</button>
            </div>
            @if(Auth::user()->role === 'admin')
                <div class="col-md-2">
                    <a href="{{ route('admin.task.create.get') }}" class="btn btn-primary w-100"><i class="bi bi-plus-circle"></i> Thêm công việc</a>
                </div>
            @endif
        </form>
        <table class="table table-bordered">
            <thead>
            <tr>
                <th>ID</th>
                <th>Tiêu đề</th>
                <th>Ưu tiên</th>
                <th>Người thực hiện</th>
                <th>Loại công việc</th>
                <th>Trạng thái</th>
                <th>Ngày nhận</th>
                <th>Hành động</th>
            </tr>
            </thead>
            <tbody>
            @foreach($tasks as $task)
                <tr>
                    <td>{{ $task->id }}</td>
                    <td class="title-completed">{{ $task->title }}</td>
                    <td>
                        @if(\Illuminate\Support\Facades\Auth::user()->role=='admin')
                            <button title="Chọn độ ưu tiên" id="priority-{{ $task->id }}" class="priority" data-id="{{ $task->id }}" data-value="{{ $task->priority }}">
                                @php
                                    $priorityText = match((int)$task->priority) {
                                        1 => 'Thấp',
                                        2 => 'Trung bình',
                                        3 => 'Cao',
                                        4 => 'Quan trọng',
                                        default => 'Không xác định'
                                    };
                                @endphp
                                {{ $priorityText }}
                            </button>
                        @else
                            @php
                                $priorityText = match((int)$task->priority) {
                                    1 => 'Thấp',
                                    2 => 'Trung bình',
                                    3 => 'Cao',
                                    4 => 'Quan trọng',
                                    default => 'Không xác định'
                                };
                            @endphp
                            {{ $priorityText }}
                        @endif

                    </td>
                    <td class="assignee-cell" data-task-id="{{ $task->id }}">{{ ($task->user()->exists()) ? $task->user->name : '---' }}</td>
                    <td>{{ $task->task_type }}</td>
                    <td>
                        @if($task->status == 'pending')
                            <span class="badge bg-danger status-badge" data-id="{{ $task->id }}" data-status="pending" style="cursor:pointer">Mới</span>
                        @endif
                        @if($task->status == 'processing')
                            <span class="badge bg-warning status-badge" data-id="{{ $task->id }}" data-status="processing" style="cursor:pointer">Đang xử lý</span>
                        @endif
                        @if($task->status == 'completed')
                            <span class="badge bg-success status-badge" data-id="{{ $task->id }}" data-status="completed" style="cursor:pointer">Đã hoàn thành</span>
                        @endif
                    </td>
                    <td>{{ $task->created_at->format('d/m/Y') }}</td>
                    <td>
                        <a href="#" class="btn-module btn-view view-detail" data-id="{{ $task->id }}" title="Xem chi tiết"><i class="fa fa-eye"></i></a>
                        <a href="{{ route('admin.task.edit.get', $task->id) }}" class="btn-module btn-edit"><i class="fa fa-edit"></i></a>
                        <a href="#" class="confirm-class btn-module btn-delete" data-url="{{ route('admin.task.destroy', $task->id) }}"><i class="fa fa-remove"></i></a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        {{ $tasks->links() }}
    </div>


    <!-- Popup chọn độ ưu tiên -->
<div id="priority-popup" style="display:none; position:absolute; z-index:9999;">
    <div class="priority-option option-low" data-value="1">Thấp</div>
    <div class="priority-option option-medium" data-value="2">Trung bình</div>
    <div class="priority-option option-high" data-value="3">Cao</div>
    <div class="priority-option option-important" data-value="4">Quan trọng</div>
</div>

<!-- Popup chọn trạng thái -->
<div id="status-popup" style="display:none; position:absolute; z-index:9999;">
    <div class="status-option bg-danger text-white" data-value="pending">Mới</div>
    <div class="status-option bg-warning text-dark" data-value="processing">Đang xử lý</div>
    <div class="status-option bg-success text-white" data-value="completed">Đã hoàn thành</div>
</div>

<!-- Popup chi tiết task -->
<div id="task-detail-popup" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:10000;">
    <div style="position:absolute; top:50%; left:50%; transform:translate(-50%, -50%); background:white; padding:30px; border-radius:10px; max-width:600px; width:90%; max-height:80vh; overflow-y:auto;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; border-bottom:1px solid #eee; padding-bottom:10px;">
            <h4 style="margin:0; color:#333;">Chi tiết công việc</h4>
            <button class="close-popup" style="background:none; border:none; font-size:24px; cursor:pointer; color:#666;">&times;</button>
        </div>
        
        <div style="margin-bottom:15px;">
            <strong style="color:#555;">Tiêu đề:</strong>
            <div id="task-title" style="margin-top:5px; font-size:16px; color:#333;"></div>
        </div>
        
        <div style="margin-bottom:15px;">
            <strong style="color:#555;">Mô tả:</strong>
            <div id="task-content" style="margin-top:5px; padding:10px; background:#f9f9f9; border-radius:5px; min-height:60px;"></div>
        </div>
        
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px; margin-bottom:15px;">
            <div>
                <strong style="color:#555;">Độ ưu tiên:</strong>
                <div id="task-priority" style="margin-top:5px;"></div>
            </div>
            <div>
                <strong style="color:#555;">Người thực hiện:</strong>
                @if(Auth::user()->role === 'admin')
                    <select id="task-assignee-select" style="margin-top:5px; width:100%; padding:5px; border:1px solid #ddd; border-radius:4px;">
                        <option value="">Chưa phân công</option>
                        @foreach(\App\Models\User::all() as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                @else
                    <div id="task-assignee" style="margin-top:5px;"></div>
                @endif
            </div>
        </div>
        
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px; margin-bottom:15px;">
            <div>
                <strong style="color:#555;">Loại công việc:</strong>
                <div id="task-type" style="margin-top:5px;"></div>
            </div>
            <div>
                <strong style="color:#555;">Trạng thái:</strong>
                <div id="task-status" style="margin-top:5px;"></div>
            </div>
        </div>
        
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px; margin-bottom:15px;">
            <div>
                <strong style="color:#555;">Ngày tạo:</strong>
                <div id="task-created" style="margin-top:5px;"></div>
            </div>
            <div>
                <strong style="color:#555;">Hạn hoàn thành:</strong>
                <div id="task-end-date" style="margin-top:5px;"></div>
            </div>
        </div>
        
        <div style="text-align:center; margin-top:25px;">
            <button class="close-popup" style="background:#007bff; color:white; border:none; padding:10px 25px; border-radius:5px; cursor:pointer;">Đóng</button>
        </div>
    </div>
</div>

@endsection


