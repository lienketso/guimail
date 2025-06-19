<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendEmail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
class MailController extends Controller
{
    public function showForm()
    {
        return view('mail.form');
    }

    public function sendMail(Request $request)
    {
        $request->validate([
            'emails' => 'required',
            'subject' => 'required',
            'message' => 'required',
            'attachments.*' => 'file|max:2048', // Giới hạn 2MB mỗi file
        ]);

        // Xử lý danh sách email
        $emails = array_filter(array_map('trim', explode(',', $request->emails)));
        $ccEmails = array_filter(array_map('trim', explode(',', $request->cc ?? '')));
        $bccEmails = array_filter(array_map('trim', explode(',', $request->bcc ?? '')));

        // Lưu file vào storage
        $attachments = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('attachments'); // Lưu file vào storage/app/attachments
                $attachments[] = storage_path("app/{$path}");
            }
        }

        $chunks = array_chunk($emails, 30); // Chia thành nhóm 30 email

        foreach ($chunks as $index => $chunk) {
            $data = [
                'subject' => $request->subject,
                'message' => $request->message,
                'attachments' => $attachments,
            ];

            $email = Mail::to(array_shift($chunk));

            if (!empty($chunk)) {
                $email->cc($chunk); // Thêm CC từ danh sách email chính
            }

            if (!empty($ccEmails)) {
                $email->cc($ccEmails); // Thêm CC từ ô nhập liệu
            }

            if (!empty($bccEmails)) {
                $email->bcc($bccEmails); // Thêm BCC từ ô nhập liệu
            }

            $email->send(new SendEmail($data));

            sleep(30); // Chờ 30s
        }

        return back()->with('success', 'Email đã được gửi thành công!');
    }


    public function sendMailAjax(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'emails' => ['required', function ($attribute, $value, $fail) {
                $emails = array_map('trim', explode(',', $value));
                foreach ($emails as $email) {
                    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $fail("Email '$email' không hợp lệ.");
                    }
                }
            }],
            'cc' => [function ($attribute, $value, $fail) {
                if ($value) {
                    $ccEmails = array_map('trim', explode(',', $value));
                    foreach ($ccEmails as $email) {
                        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                            $fail("Email CC '$email' không hợp lệ.");
                        }
                    }
                }
            }],
            'bcc' => [function ($attribute, $value, $fail) {
                if ($value) {
                    $bccEmails = array_map('trim', explode(',', $value));
                    foreach ($bccEmails as $email) {
                        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                            $fail("Email BCC '$email' không hợp lệ.");
                        }
                    }
                }
            }],
            'subject' => 'required',
            'message' => 'required',
            'attachments.*' => 'file|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'error' => $validator->errors()->first()]);
        }

        try {
            $emails = array_filter(array_map('trim', explode(',', $request->emails)));
            $ccEmails = array_filter(array_map('trim', explode(',', $request->cc ?? '')));
            $bccEmails = array_filter(array_map('trim', explode(',', $request->bcc ?? '')));

            // Lưu file tạm thời
            $attachments = [];
            if ($request->hasFile('attachment')) {
                foreach ($request->file('attachment') as $file) {
                    $path = $file->store('attachments', 'public');
                    $attachments[] = Storage::path($path);
                    // $attachments[] = storage_path('app/public/' . $path);
                    // $attachments[] = Storage::disk('public')->path($path);
                }
            }

            // Kiểm tra trước khi gọi SendEmail
            if (!is_array($attachments)) {
                \Log::error('Lỗi: $attachments không phải là mảng!');
                $attachments = [];
            }

            // dd($attachments);

            Mail::to(array_shift($emails))
                ->cc($ccEmails)
                ->bcc($bccEmails)
                ->send(new SendEmail($request->subject, $request->message), function ($message) use ($attachments) {
                    foreach ($attachments as $file) {
                        if (is_string($file) && file_exists($file)) {
                            $message->attach($file);
                        }
                    }
                });

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => 'Lỗi khi gửi email: ' . $e->getMessage()]);
        }
    }

}
