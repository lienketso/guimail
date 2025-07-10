<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    public function dashboard()
    {
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Bạn không có quyền truy cập!');
        }
        $companyCount = \App\Models\Company::count();
        $userCount = \App\Models\User::count();
        $fileCount = \App\Models\File::count();

        // Lấy 5 báo cáo nộp gần đây nhất
        $recentReports = \App\Models\File::with(['folder.company', 'folder.parent'])
            ->orderByDesc('created_at') // hoặc 'created_at' nếu không có 'ngay_nop'
            ->take(5)
            ->get();

        return view('admin.dashboard', compact('companyCount', 'userCount', 'fileCount', 'recentReports'));
    }
} 