<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Kiểm toán Việt Nam')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    {{-- <link rel="stylesheet" href="{{ asset('css/style.css') }}" /> --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
   <link rel="stylesheet" href="{{ asset('css/frontend.css') }}">
   <link rel="stylesheet" href="{{ asset('css/chatbot.css') }}">
</head>
<body>

<div class="content-main mt-4">
    @yield('content')
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
{{--<script src="{{ asset('js/chatbot.js') }}"></script>--}}
@yield('js')
@yield('js-init')
@stack('js')
@stack('js-init')

<script type="text/javascript">
    function showCareForm(type) {
        document.getElementById('careForm').style.display = 'block';
        document.querySelector('.btn-support').classList.remove('active');
        document.querySelector('.btn-feedback').classList.remove('active');
        if (type === 'support') {
            document.querySelector('.btn-support').classList.add('active');
            document.getElementById('task_type').value = 'support';
            document.getElementById('form-title-label').innerText = 'Tiêu đề';
            document.getElementById('form-title-input').placeholder = 'Nhập tiêu đề hỗ trợ';
            document.getElementById('form-content-label').innerText = 'Nội dung';
            document.getElementById('form-content-input').placeholder = 'Nhập nội dung cần hỗ trợ';
            document.getElementById('form-submit-btn').innerText = 'Gửi hỗ trợ';
        } else {
            document.querySelector('.btn-feedback').classList.add('active');
            document.getElementById('task_type').value = 'feedback';
            document.getElementById('form-title-label').innerText = 'Tiêu đề';
            document.getElementById('form-title-input').placeholder = 'Nhập tiêu đề phản ánh';
            document.getElementById('form-content-label').innerText = 'Nội dung';
            document.getElementById('form-content-input').placeholder = 'Nhập nội dung phản ánh dịch vụ';
            document.getElementById('form-submit-btn').innerText = 'Gửi phản ánh';
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('supportForm');
        const successBox = document.getElementById('support-success');
        const continueBtn = document.getElementById('support-continue');
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(form);
            fetch("{{ route('support.request') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        form.reset();
                        successBox.style.display = 'block';
                        $('#careForm').hide();
                    } else if (data.errors) {
                        // Hiển thị lỗi nếu cần
                        alert(data.message);
                    }
                })
                .catch(() => {
                    alert('Có lỗi xảy ra, vui lòng thử lại!');
                });
        });
        continueBtn.addEventListener('click', function() {
            successBox.style.display = 'none';
            $('#careForm').show();
            form.reset();
        });
    });
</script>

</body>
</html>
