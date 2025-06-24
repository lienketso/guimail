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
        return view('admin.dashboard', compact('companyCount', 'userCount', 'fileCount'));
    }
} 