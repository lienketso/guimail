<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
    <title>Gửi Email</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="{{asset('public/js/tinymce/tinymce.min.js')}}"></script>

    <script>
        tinymce.init({
            selector: '#myTextarea'
        });
    </script>
    <script>
        $(document).ready(function() {
            $("#emailForm").submit(function(event) {
                event.preventDefault(); // Ngăn reload trang

                $("#loading").show(); // Hiển thị loading
                $("#successMessage, #errorMessage").hide(); // Ẩn thông báo cũ

                var formData = new FormData(this);

                $.ajax({
                    url: "{{ route('send.mail.ajax') }}",
                    method: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        $("#loading").hide();
                        if (response.success) {
                            $("#successMessage").show();
                            $("#emailForm")[0].reset();
                        } else {
                            $("#errorMessage").text(response.error).show();
                        }
                    },
                    error: function(xhr) {
                        $("#loading").hide();
                        $("#errorMessage").text("❌ Lỗi gửi email!").show();
                    }
                });
            });
        });
    </script>
</head>
<body>

<style type="text/css">
    *{
        margin: 0;
        padding: 0;
    }
    body{
        font-family: "Inter", sans-serif;
        font-size: 16px;
    }
    .form-send-mail{
        max-width: 600px;
        margin: 50px auto;
        background: #efefef;
        padding: 50px;
        border-radius: 10px;
    }
    .form-send-mail h2{
        text-align: center;
        padding-bottom: 20px;
    }
    .form-send-mail label{
        padding-bottom: 5px;
        display: block;
        font-size: 14px;
    }
    .form-send-mail label span{
        font-size: 12px;
        color: brown;
    }
    .item-field{
        padding-bottom: 20px;
    }
    .item-field input[type='text'], textarea{
        border: 0;
        width: 93%;
        border-radius: 8px;
        padding: 8px 20px;
        resize: vertical;
    }
    .form-send-mail button{
        background: #000;
        color: #fff;
        padding: 10px 30px;
        border-radius: 8px;
        border: 0;
        cursor: pointer;
        font-weight: 600;
    }
    .form-send-mail button:hover{
        background: brown;
    }
    .btn-submit{
        text-align: right;
    }
    .message-success{
        background-color: green;
        color: #fff;
        border-radius: 8px;
        padding: 5px 10px;
        text-align: center;
        margin-bottom: 20px;
        font-size: 12px;
    }
    .alert ul{

    }
    .alert ul li{
        list-style: none;
        background-color: red;
        color: #fff;
        font-size: 12px;
        padding: 5px 10px;
        margin-bottom: 10px;
        border-radius: 8px;
    }
    #loading {
        display: none;
        text-align: center;
        font-size: 18px;
        color: #ff4500;
    }
</style>

<div class="form-send-mail">
    <h2>Form Gửi Email</h2>
    @if(session('success'))
        <p class="message-success">{{ session('success') }}</p>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <form action="{{ route('send.mail') }}" id="emailForm" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="item-field">
            <label>Danh sách Email (<span>cách nhau bằng dấu phẩy</span>):</label>
            <input type="text" name="emails" value="{{ old('emails') }}">
        </div>
        <div class="item-field">
            <label>Danh sách CC (<span>cách nhau bằng dấu phẩy, không bắt buộc</span>):</label>
            <input type="text" placeholder="email1@exam.com,email2@exam.com" name="cc" value="{{ old('cc') }}">
        </div>
        <div class="item-field">
            <label>Danh sách BCC (<span>cách nhau bằng dấu phẩy, không bắt buộc</span>):</label>
            <textarea name="bcc" placeholder="email1@exam.com,email2@exam.com" rows="5" cols="50">{{ old('bcc') }}</textarea>
        </div>
        <div class="item-field">
            <label>Tiêu đề Email:</label>
            <input type="text" name="subject" value="{{ old('subject') }}">
        </div>
        <div class="item-field">
            <label>Nội dung Email:</label>
            <textarea id="myTextarea" name="message" rows="5" cols="50">{{ old('message') }}</textarea>
        </div>
        <div class="item-field">
            <label>Đính kèm file (tùy chọn):</label>
            <input type="file" name="attachments[]" multiple>
        </div>
        <div class="btn-submit">
            <button type="submit">Gửi Email</button>
        </div>
    </form>

    <div class="ajax-alert">
        <p id="loading">⏳ Đang gửi email, vui lòng chờ đến khi tiến trình kết thúc, không được tắt trình duyệt...</p>
        <p id="successMessage" style="color: green; display: none;">✅ Email đã được gửi thành công !</p>
        <p id="errorMessage" style="color: red; display: none;"></p>
    </div>

</div>
</body>
</html>
