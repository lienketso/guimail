@extends('layouts.app')
@section('title', 'Lịch sử đăng nhập')
@section('content')
<div class="container">
    <h3 class="title-main">Lịch sử đăng nhập</h3>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Tên User</th>
                <th>Email</th>
                <th>Địa chỉ IP</th>
                <th>Thiết bị</th>
                <th>Thời gian</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $log)
            <tr>
                <td>{{ $log->id }}</td>
                <td>{{ $log->user->name ?? 'N/A' }}</td>
                <td>{{ $log->user->email ?? 'N/A' }}</td>
                <td>{{ $log->ip_address }}</td>
                <td>{{ Str::limit($log->user_agent, 50) }}</td>
                <td>{{ $log->created_at->format('H:i:s d-m-Y') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center">Không có lịch sử đăng nhập nào.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    <div class="d-flex justify-content-center">
        {{ $logs->links() }}
    </div>
</div>
@endsection 