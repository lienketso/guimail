<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác Nhận</title>
</head>
<body>
<h2>Nhập mật khẩu để tiếp tục</h2>
@if(session('error'))
    <p style="color: red;">{{ session('error') }}</p>
@endif
<form action="{{ route('verify.post') }}" method="POST">
    @csrf
    <input type="password" name="number" required>
    <button type="submit">Xác nhận</button>
</form>
</body>
</html>
