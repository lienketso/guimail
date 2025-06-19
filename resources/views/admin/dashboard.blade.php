<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-4">
    <h3>Chào mừng {{ Auth::user()->name }} đến với trang quản trị!</h3>
    <ul class="nav nav-pills flex-column flex-sm-row mt-4">
        <li class="nav-item">
            <a class="nav-link" href="{{ route('companies.index') }}">Quản lý công ty</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#">Quản lý user</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ route('taxcode.form') }}">Tra cứu mã số thuế</a>
        </li>
    </ul>
</div>
</body>
</html> 