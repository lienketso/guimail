@extends('layouts.app')
@section('title', 'Mã số thuế')
@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header text-center">Nhập mã số thuế công ty</div>
                <div class="card-body">
                    @if($errors->has('tax_code'))
                        <div class="alert alert-danger">{{ $errors->first('tax_code') }}</div>
                    @endif
                    <form method="GET" action="{{ url('/folders') }}">
                        <div class="mb-3">
                            <label for="tax_code" class="form-label">Mã số thuế</label>
                            <input type="text" class="form-control" id="tax_code" name="tax_code" value="{{ old('tax_code') }}" required autofocus>
                        </div>
                        <button type="submit" class="btn btn-success w-100">Xem cây thư mục</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection