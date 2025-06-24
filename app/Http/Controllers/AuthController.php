<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        if (auth()->check()) {
            // Đã đăng nhập, chuyển hướng sang trang chính (ví dụ: nhập mã số thuế)
            return redirect()->route('taxcode.form');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $login = $request->input('email');
        $password = $request->input('password');
        // Kiểm tra là email hay số điện thoại
        $fieldType = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';
        $user = \App\Models\User::where($fieldType, $login)->first();
        if (!$user) {
            return back()->withErrors([
                'email' => 'Tài khoản không tồn tại hoặc thông tin không đúng.',
            ]);
        }
        if ($user->status !== 'active') {
            return back()->withErrors([
                'email' => 'Tài khoản của bạn đã bị khóa hoặc chưa được kích hoạt.',
            ]);
        }
        // Thử đăng nhập
        if (Auth::attempt([$fieldType => $login, 'password' => $password, 'status' => 'active'])) {
            $request->session()->regenerate();
            // Ghi log đăng nhập
            \App\Models\UserLoginLog::create([
                'user_id' => Auth::id(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'login_at' => now(),
            ]);
            return redirect()->route('taxcode.form');
        }
        return back()->withErrors([
            'email' => 'Email/SĐT hoặc mật khẩu không đúng.',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
} 