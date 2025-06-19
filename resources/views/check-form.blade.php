<!DOCTYPE html>
<html>
<head>
    <title>Kiểm tra đạo văn</title>
</head>
<body>
<h2>Nhập nội dung cần kiểm tra</h2>

@if ($errors->any())
    <div style="color:red;">
        @foreach ($errors->all() as $err)
            <p>{{ $err }}</p>
        @endforeach
    </div>
@endif

<form method="POST" action="/plagiarism">
    @csrf
    <textarea name="text" rows="10" cols="80">{{ old('text', $original ?? '') }}</textarea><br><br>
    <button type="submit">Kiểm tra đạo văn</button>
</form>

@isset($results)
    <h3>Kết quả phân tích:</h3>
    @foreach ($results as $item)
        <div style="margin-bottom: 10px;">
            <strong>Câu:</strong> {{ $item['sentence'] }}<br>
            <strong>Đạo văn:</strong> {{ $item['plagiarism'] }}
        </div>
    @endforeach
@endisset
</body>
</html>
