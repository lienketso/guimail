@extends('layouts.app')
@section('title', 'Sửa cây thư mục và file')
@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header text-center">Nhập mã số thuế</div>
                <div class="card-body">
                    @if($errors->has('tax_code'))
                        <div class="alert alert-danger">{{ $errors->first('tax_code') }}</div>
                    @endif
                    <form method="GET" action="{{ route('folders.tree') }}"> 
                        <div class="mb-3">
                            <label for="tax_code" class="form-label">Mã số thuế</label>
                            <input type="text" class="form-control" id="tax_code" name="tax_code" value="{{ old('tax_code') }}" required autofocus>
                        </div>
                        <button type="submit" class="btn btn-warning w-100">Sửa cây thư mục và file</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection