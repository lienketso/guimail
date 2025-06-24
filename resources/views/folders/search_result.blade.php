@extends('layouts.app')
@section('title', 'Kết quả tìm kiếm file')
@section('content')
    <h3 class="title-main">Kết quả tìm kiếm file: "{{ $keyword }}"</h3>
    <a href="{{ url()->previous() }}" class="btn btn-secondary mb-3"><i class="bi bi-arrow-left"></i> Quay lại</a>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Tên file</th>
                <th>Thư mục</th>
                <th>Ngày upload</th>
                <th>Tải về</th>
            </tr>
        </thead>
        <tbody>
            @forelse($files as $file)
            <tr>
                <td>{{ $file->name }}</td>
                <td>{{ $file->folder->name ?? '-' }}</td>
                <td>{{ $file->created_at->format('d-m-Y H:i') }}</td>
                <td><a href="{{ route('folders.download', $file->id) }}" class="btn btn-sm btn-success"><i class="bi bi-download"></i> Tải về</a></td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="text-center">Không tìm thấy file nào phù hợp.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
@endsection 