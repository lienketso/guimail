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
    
    {{-- Giữ nguyên cây thư mục --}}
    @if($folderTree)
        <ul class="folder-parent-list">
            @foreach($folderTree as $folder)
                @include('folders._folder_recursive_year', ['folder' => $folder, 'class' => 'tree-branch'])
            @endforeach
        </ul>
    @else
        <p>Không có thư mục cho năm này.</p>
    @endif

    <hr>
    
    {{-- Thay thế phần thống kê cũ bằng 4 bảng thống kê riêng biệt --}}
    <div class="detailed-stats-section">
        <h5>Thống kê chi tiết theo năm {{ $selectedYear }}</h5>
        
        @if($folderTree)
            {{-- Bảng 1: VAT --}}
            @php $vatFolders = collect($folderTree)->where('name', 'VAT')->first(); @endphp
            @if($vatFolders && !empty($vatFolders->children))
                <div class="stats-table-container mb-4">
                    <h6 class="table-title bg-primary text-white p-2 rounded">
                        <i class="fa fa-file-text"></i> Báo cáo VAT
                    </h6>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover detailed-stats-table">
                            <thead class="table-primary">
                                <tr>
                                    <th class="text-center align-middle">Kỳ báo cáo</th>
                                    <th class="text-center align-middle">Số lần nộp</th>
                                    <th class="text-center align-middle">Chi tiết lần nộp</th>
                                    <th class="text-center align-middle">Ngày nộp</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($vatFolders->children as $quarter)
                                    @if(in_array($quarter->name, ['Quý 1', 'Quý 2', 'Quý 3', 'Quý 4']))
                                        @if(!empty($quarter->children))
                                            @foreach($quarter->children as $lanIndex => $lanFolder)
                                                <tr class="{{ $lanIndex === 0 ? 'border-top' : '' }}">
                                                    @if($lanIndex === 0)
                                                        <td rowspan="{{ count($quarter->children) }}" class="text-center align-middle fw-bold bg-light">
                                                            {{ $quarter->name }}
                                                        </td>
                                                        <td rowspan="{{ count($quarter->children) }}" class="text-center align-middle">
                                                            <span class="badge bg-primary">{{ count($quarter->children) }}</span>
                                                        </td>
                                                    @endif
                                                    <td class="text-center">
                                                        <span class="badge bg-info">{{ $lanFolder->name }}</span>
                                                    </td>
                                                    <td class="text-center">
                                                        @if($lanFolder->ngay_nop)
                                                            <span class="text-success">{{ \Carbon\Carbon::parse($lanFolder->ngay_nop)->format('d/m/Y') }}</span>
                                                        @else
                                                            <span class="text-muted">Chưa có</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr>
                                                <td class="text-center align-middle fw-bold bg-light">{{ $quarter->name }}</td>
                                                <td class="text-center align-middle">
                                                    <span class="badge bg-secondary">0</span>
                                                </td>
                                                <td class="text-center">-</td>
                                                <td class="text-center">-</td>
                                            </tr>
                                        @endif
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            {{-- Bảng 2: TNCN --}}
            @php $tncnFolders = collect($folderTree)->where('name', 'TNCN')->first(); @endphp
            @if($tncnFolders && !empty($tncnFolders->children))
                <div class="stats-table-container mb-4">
                    <h6 class="table-title bg-success text-white p-2 rounded">
                        <i class="fa fa-user"></i> Báo cáo TNCN
                    </h6>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover detailed-stats-table">
                            <thead class="table-success">
                                <tr>
                                    <th class="text-center align-middle">Kỳ báo cáo</th>
                                    <th class="text-center align-middle">Số lần nộp</th>
                                    <th class="text-center align-middle">Chi tiết lần nộp</th>
                                    <th class="text-center align-middle">Ngày nộp</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($tncnFolders->children as $childIndex => $child)
                                    @php
                                        $isQuarterly = in_array($child->name, ['Quý 1', 'Quý 2', 'Quý 3', 'Quý 4']);
                                        $isLan = Str::startsWith(Str::lower($child->name), 'lần');
                                    @endphp
                                    
                                    @if($isQuarterly)
                                        @if(!empty($child->children))
                                            @foreach($child->children as $lanIndex => $lanFolder)
                                                <tr class="{{ $lanIndex === 0 ? 'border-top' : '' }}">
                                                    @if($lanIndex === 0)
                                                        <td rowspan="{{ count($child->children) }}" class="text-center align-middle fw-bold bg-light">
                                                            {{ $child->name }}
                                                        </td>
                                                        <td rowspan="{{ count($child->children) }}" class="text-center align-middle">
                                                            <span class="badge bg-primary">{{ count($child->children) }}</span>
                                                        </td>
                                                    @endif
                                                    <td class="text-center">
                                                        <span class="badge bg-info">{{ $lanFolder->name }}</span>
                                                    </td>
                                                    <td class="text-center">
                                                        @if($lanFolder->ngay_nop)
                                                            <span class="text-success">{{ \Carbon\Carbon::parse($lanFolder->ngay_nop)->format('d/m/Y') }}</span>
                                                        @else
                                                            <span class="text-muted">Chưa có</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr>
                                                <td class="text-center align-middle fw-bold bg-light">{{ $child->name }}</td>
                                                <td class="text-center align-middle">
                                                    <span class="badge bg-secondary">0</span>
                                                </td>
                                                <td class="text-center">-</td>
                                                <td class="text-center">-</td>
                                            </tr>
                                        @endif
                                    @elseif($isLan)
                                        <tr class="{{ $childIndex === 0 ? 'border-top' : '' }}">
                                            <td class="text-center align-middle">{{ $child->name }}</td>
                                            <td class="text-center align-middle">-</td>
                                            <td class="text-center">-</td>
                                            <td class="text-center">
                                                @if($child->ngay_nop)
                                                    <span class="text-success">{{ \Carbon\Carbon::parse($child->ngay_nop)->format('d/m/Y') }}</span>
                                                @else
                                                    <span class="text-muted">Chưa có</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @else
                                        <tr class="{{ $childIndex === 0 ? 'border-top' : '' }}">
                                            <td class="text-center align-middle">{{ $child->name }}</td>
                                            <td class="text-center align-middle">
                                                @if(!empty($child->children))
                                                    <span class="badge bg-primary">{{ count($child->children) }}</span>
                                                @else
                                                    <span class="badge bg-secondary">0</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if(!empty($child->children))
                                                    @foreach($child->children as $lanFolder)
                                                        <div class="mb-1">
                                                            <span class="badge bg-info">{{ $lanFolder->name }}</span>
                                                        </div>
                                                    @endforeach
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if(!empty($child->children))
                                                    @foreach($child->children as $lanFolder)
                                                        <div class="mb-1">
                                                            @if($lanFolder->ngay_nop)
                                                                <span class="text-success">{{ \Carbon\Carbon::parse($lanFolder->ngay_nop)->format('d/m/Y') }}</span>
                                                            @else
                                                                <span class="text-muted">Chưa có</span>
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                @else
                                                    -
                                                @endif
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            {{-- Bảng 3: TNDN --}}
            @php $tndnFolders = collect($folderTree)->where('name', 'TNDN')->first(); @endphp
            @if($tndnFolders && !empty($tndnFolders->children))
                <div class="stats-table-container mb-4">
                    <h6 class="table-title bg-warning text-dark p-2 rounded">
                        <i class="fa fa-building"></i> Báo cáo TNDN
                    </h6>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover detailed-stats-table">
                            <thead class="table-warning">
                                <tr>
                                    <th class="text-center align-middle">Kỳ báo cáo</th>
                                    <th class="text-center align-middle">Số lần nộp</th>
                                    <th class="text-center align-middle">Chi tiết lần nộp</th>
                                    <th class="text-center align-middle">Ngày nộp</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($tndnFolders->children as $childIndex => $child)
                                    @php
                                        $isQuarterly = in_array($child->name, ['Quý 1', 'Quý 2', 'Quý 3', 'Quý 4']);
                                        $isLan = Str::startsWith(Str::lower($child->name), 'lần');
                                    @endphp
                                    
                                    @if($isQuarterly)
                                        @if(!empty($child->children))
                                            @foreach($child->children as $lanIndex => $lanFolder)
                                                <tr class="{{ $lanIndex === 0 ? 'border-top' : '' }}">
                                                    @if($lanIndex === 0)
                                                        <td rowspan="{{ count($child->children) }}" class="text-center align-middle fw-bold bg-light">
                                                            {{ $child->name }}
                                                        </td>
                                                        <td rowspan="{{ count($child->children) }}" class="text-center align-middle">
                                                            <span class="badge bg-primary">{{ count($child->children) }}</span>
                                                        </td>
                                                    @endif
                                                    <td class="text-center">
                                                        <span class="badge bg-info">{{ $lanFolder->name }}</span>
                                                    </td>
                                                    <td class="text-center">
                                                        @if($lanFolder->ngay_nop)
                                                            <span class="text-success">{{ \Carbon\Carbon::parse($lanFolder->ngay_nop)->format('d/m/Y') }}</span>
                                                        @else
                                                            <span class="text-muted">Chưa có</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr>
                                                <td class="text-center align-middle fw-bold bg-light">{{ $child->name }}</td>
                                                <td class="text-center align-middle">
                                                    <span class="badge bg-secondary">0</span>
                                                </td>
                                                <td class="text-center">-</td>
                                                <td class="text-center">-</td>
                                            </tr>
                                        @endif
                                    @elseif($isLan)
                                        <tr class="{{ $childIndex === 0 ? 'border-top' : '' }}">
                                            <td class="text-center align-middle">{{ $child->name }}</td>
                                            <td class="text-center align-middle">-</td>
                                            <td class="text-center">-</td>
                                            <td class="text-center">
                                                @if($child->ngay_nop)
                                                    <span class="text-success">{{ \Carbon\Carbon::parse($child->ngay_nop)->format('d/m/Y') }}</span>
                                                @else
                                                    <span class="text-muted">Chưa có</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @else
                                        <tr class="{{ $childIndex === 0 ? 'border-top' : '' }}">
                                            <td class="text-center align-middle">{{ $child->name }}</td>
                                            <td class="text-center align-middle">
                                                @if(!empty($child->children))
                                                    <span class="badge bg-primary">{{ count($child->children) }}</span>
                                                @else
                                                    <span class="badge bg-secondary">0</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if(!empty($child->children))
                                                    @foreach($child->children as $lanFolder)
                                                        <div class="mb-1">
                                                            <span class="badge bg-info">{{ $lanFolder->name }}</span>
                                                        </div>
                                                    @endforeach
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if(!empty($child->children))
                                                    @foreach($child->children as $lanFolder)
                                                        <div class="mb-1">
                                                            @if($lanFolder->ngay_nop)
                                                                <span class="text-success">{{ \Carbon\Carbon::parse($lanFolder->ngay_nop)->format('d/m/Y') }}</span>
                                                            @else
                                                                <span class="text-muted">Chưa có</span>
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                @else
                                                    -
                                                @endif
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            {{-- Bảng 4: Báo cáo khác --}}
            @php $otherFolders = collect($folderTree)->where('name', 'Báo cáo khác')->first(); @endphp
            @if($otherFolders && !empty($otherFolders->children))
                <div class="stats-table-container mb-4">
                    <h6 class="table-title bg-info text-white p-2 rounded">
                        <i class="fa fa-files-o"></i> Báo cáo khác
                    </h6>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover detailed-stats-table">
                            <thead class="table-info">
                                <tr>
                                    <th class="text-center align-middle">Kỳ báo cáo</th>
                                    <th class="text-center align-middle">Số lần nộp</th>
                                    <th class="text-center align-middle">Chi tiết lần nộp</th>
                                    <th class="text-center align-middle">Ngày nộp</th>
                                    <th class="text-center align-middle">Mô tả</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($otherFolders->children as $childIndex => $child)
                                    @php
                                        $isQuarterly = in_array($child->name, ['Quý 1', 'Quý 2', 'Quý 3', 'Quý 4']);
                                        $isLan = Str::startsWith(Str::lower($child->name), 'lần');
                                    @endphp
                                    
                                    @if($isQuarterly)
                                        @if(!empty($child->children))
                                            @foreach($child->children as $lanIndex => $lanFolder)
                                                <tr class="{{ $lanIndex === 0 ? 'border-top' : '' }}">
                                                    @if($lanIndex === 0)
                                                        <td rowspan="{{ count($child->children) }}" class="text-center align-middle fw-bold bg-light">
                                                            {{ $child->name }}
                                                        </td>
                                                        <td rowspan="{{ count($child->children) }}" class="text-center align-middle">
                                                            <span class="badge bg-primary">{{ count($child->children) }}</span>
                                                        </td>
                                                    @endif
                                                    <td class="text-center">
                                                        <span class="badge bg-info">{{ $lanFolder->name }}</span>
                                                    </td>
                                                    <td class="text-center">
                                                        @if($lanFolder->ngay_nop)
                                                            <span class="text-success">{{ \Carbon\Carbon::parse($lanFolder->ngay_nop)->format('d/m/Y') }}</span>
                                                        @else
                                                            <span class="text-muted">Chưa có</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-center">
                                                        @if($lanFolder->description)
                                                            <span class="text-info">{{ $lanFolder->description }}</span>
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr>
                                                <td class="text-center align-middle fw-bold bg-light">{{ $child->name }}</td>
                                                <td class="text-center align-middle">
                                                    <span class="badge bg-secondary">0</span>
                                                </td>
                                                <td class="text-center">-</td>
                                                <td class="text-center">-</td>
                                                <td class="text-center">-</td>
                                            </tr>
                                        @endif
                                    @elseif($isLan)
                                        <tr class="{{ $childIndex === 0 ? 'border-top' : '' }}">
                                            <td class="text-center align-middle">{{ $child->name }}</td>
                                            <td class="text-center align-middle">-</td>
                                            <td class="text-center">-</td>
                                            <td class="text-center">
                                                @if($child->ngay_nop)
                                                    <span class="text-success">{{ \Carbon\Carbon::parse($child->ngay_nop)->format('d/m/Y') }}</span>
                                                @else
                                                    <span class="text-muted">Chưa có</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if($child->description)
                                                    <span class="text-info">{{ $child->description }}</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @else
                                        <tr class="{{ $childIndex === 0 ? 'border-top' : '' }}">
                                            <td class="text-center align-middle">{{ $child->name }}</td>
                                            <td class="text-center align-middle">
                                                @if(!empty($child->children))
                                                    <span class="badge bg-primary">{{ count($child->children) }}</span>
                                                @else
                                                    <span class="badge bg-secondary">0</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if(!empty($child->children))
                                                    @foreach($child->children as $lanFolder)
                                                        <div class="mb-1">
                                                            <span class="badge bg-info">{{ $lanFolder->name }}</span>
                                                        </div>
                                                    @endforeach
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if(!empty($child->children))
                                                    @foreach($child->children as $lanFolder)
                                                        <div class="mb-1">
                                                            @if($lanFolder->ngay_nop)
                                                                <span class="text-success">{{ \Carbon\Carbon::parse($lanFolder->ngay_nop)->format('d/m/Y') }}</span>
                                                            @else
                                                                <span class="text-muted">Chưa có</span>
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if(!empty($child->children))
                                                    @foreach($child->children as $lanFolder)
                                                        <div class="mb-1">
                                                            @if($lanFolder->description)
                                                                <span class="text-info">{{ $lanFolder->description }}</span>
                                                            @else
                                                                <span class="text-muted">-</span>
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                @else
                                                    -
                                                @endif
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            {{-- Thông báo nếu không có dữ liệu nào --}}
            @if(empty(collect($folderTree)->whereIn('name', ['VAT', 'TNCN', 'TNDN', 'Báo cáo khác'])->filter->children->count()))
                <div class="alert alert-info">
                    <i class="fa fa-info-circle"></i> Không có dữ liệu báo cáo nào cho năm này.
                </div>
            @endif

        @else
            <div class="alert alert-info">
                <i class="fa fa-info-circle"></i> Không có dữ liệu thống kê cho năm này.
            </div>
        @endif
    </div>
    </div>
</div>
@endsection
