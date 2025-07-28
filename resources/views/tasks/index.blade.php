@extends('layouts.app')
@section('title', 'Danh sách công việc')
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
                    <td>{{ ($task->user()->exists()) ? $task->user->name : '---' }}</td>
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

@endsection


