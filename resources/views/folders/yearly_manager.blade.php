@extends('layouts.app')
@section('title', 'Quản lý thư mục theo năm')
@section('content')
<div class="container-fluid">
    <div class="content-page">
    <h4 class="title-main">Dữ liệu công ty theo năm: {{ $selectedYear }}</h4>
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
    <form method="GET" action="{{ route('folders.yearly-manager') }}" class="year-select-form">
        <input type="hidden" name="tax_code" value="{{ $tax_code }}">
        <label for="year">Chọn năm:</label>
        <select name="year" id="year" onchange="this.form.submit()">
            @foreach($years as $year)
                <option value="{{ $year }}" {{ $year == $selectedYear ? 'selected' : '' }}>{{ $year }}</option>
            @endforeach
        </select>
        <div class="back-to-manager">
            <a href="{{ route('folders.manager', ['tax_code' => $tax_code]) }}" 
                class="back-to-manager-link"><i class="fa fa-arrow-left"></i> Quay lại kỳ kê khai</a>
        </div>
    </form>
    <hr>
    @if($folderTree)
        <ul class="folder-parent-list">
            @foreach($folderTree as $folder)
                @include('folders._folder_recursive_year', ['folder' => $folder, 'class' => 'tree-branch'])
            @endforeach
        </ul>
    @else
                <p>Không có thư mục cho năm này.</p>
        @endif
    </div>
</div>
@endsection