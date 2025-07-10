<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ChatbotController extends Controller
{
    public function handle(Request $request)
    {
        $question = mb_strtolower($request->input('message', ''));

        // Danh sách câu hỏi và câu trả lời mẫu
        $faq = [
            'hướng dẫn đăng nhập' => 'Bạn vào trang đăng nhập, nhập tài khoản và mật khẩu rồi nhấn Đăng nhập.',
            'cách tạo công ty' => 'Vào menu Quản lý công ty, nhấn nút Thêm mới, điền thông tin và lưu lại.',
            'cách tạo thư mục' => 'Vào menu Quản lý thư mục, nhấn Thêm mới, nhập tên thư mục và lưu.',
            'liên hệ hỗ trợ' => 'Bạn có thể liên hệ admin qua email support@guimail.local.',
            // Thêm các câu hỏi khác tại đây
        ];

        $answer = 'Xin lỗi, tôi chưa hiểu câu hỏi của bạn. Bạn vui lòng hỏi lại hoặc liên hệ hỗ trợ.';

        foreach ($faq as $key => $val) {
            if (strpos($question, $key) !== false) {
                $answer = $val;
                break;
            }
        }

        return response()->json([
            'answer' => $answer
        ]);
    }
} 