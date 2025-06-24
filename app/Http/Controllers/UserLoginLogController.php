<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserLoginLog;

class UserLoginLogController extends Controller
{
    public function index()
    {
        // Chỉ admin mới có quyền xem
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Bạn không có quyền truy cập trang này.');
        }

        $logs = UserLoginLog::with('user')->latest()->paginate(15);
        return view('logs.login', compact('logs'));
    }
}
