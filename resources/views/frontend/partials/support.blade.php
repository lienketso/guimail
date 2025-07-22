<div class="customer-care-box p-4">
    <h4 class="mb-4" style="color:#604188;"><i class="fa fa-headset me-2"></i>Hỗ trợ khách hàng
    </h4>
    <div class="d-flex gap-3 mb-4">
        <button class="btn-care btn-support" onclick="showCareForm('support')"><i
                class="fa fa-comment"></i> Hỗ trợ công việc</button>
        <button class="btn-care btn-feedback" onclick="showCareForm('feedback')"><i
                class="fa fa-smile"></i> Phản ánh dịch vụ</button>
    </div>
    <div id="careForm" class="care-form" style="display:none;">
        <form id="supportForm">
            <input type="hidden" name="task_type" id="task_type" value="support">
            <div class="mb-3">
                <label class="form-label" id="form-title-label">Tiêu đề</label>
                <input type="text" class="form-control" name="title"
                    placeholder="Nhập tiêu đề hỗ trợ" id="form-title-input" required>
            </div>
            <div class="mb-3">
                <label class="form-label" id="form-content-label">Nội dung</label>
                <textarea class="form-control" name="content" rows="4" placeholder="Nhập nội dung cần hỗ trợ"
                    id="form-content-input"></textarea>
            </div>
            <button type="submit" class="btn btn-submit" id="form-submit-btn">Gửi hỗ trợ</button>
        </form>

    </div>

    <div id="support-success" style="display:none;">
        <div class="alert alert-success mt-3">Cảm ơn bạn đã gửi thông tin, chúng tôi sẽ xử lý sớm
            nhất có thể!</div>
        <button type="button" class="btn btn-primary" id="support-continue">Tiếp tục gửi</button>
    </div>

</div>