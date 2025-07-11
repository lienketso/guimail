<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;


//Route::get('/', function () {
//    return view('verify');
//})->name('verify');

use App\Http\Controllers\AuthController;
use App\Http\Controllers\FolderController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserLoginLogController;
use App\Http\Controllers\ChatbotController;

Route::get('/', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/tree-taxcode', function() {
    return view('auth.taxcode-tree');
})->middleware('auth')->name('taxcode.tree.form');


Route::get('/taxcode', function() {
    return view('auth.taxcode');
})->middleware('auth')->name('taxcode.form');

Route::middleware('auth')->group(function () {
    Route::get('/folders', [FolderController::class, 'showTree'])->name('folders.tree');
    Route::get('/folders/data', [FolderController::class, 'index'])->name('folders.data');
    Route::post('/folders/store', [FolderController::class, 'store'])->name('folders.store');
    Route::post('/folders/upload', [FolderController::class, 'upload'])->name('folders.upload');
    Route::post('/folders/move', [FolderController::class, 'move'])->name('folders.move');
    Route::post('/folders/taxcode', function (Illuminate\Http\Request $request) {
        $tax_code = $request->input('tax_code');
        return redirect()->route('folders.tree', ['tax_code' => $tax_code]);
    })->name('folders.taxcode');

    Route::get('/folders/manager', [FolderController::class, 'managerView'])->name('folders.manager');
    Route::post('/folders/{company}/upload-xml', [FolderController::class, 'uploadXml'])->name('folders.uploadXml');

    Route::delete('/folders/{id}', [FolderController::class, 'destroy'])->name('folders.destroy');
    Route::patch('/folders/{id}', [FolderController::class, 'rename'])->name('folders.rename');
    Route::get('/companies', [CompanyController::class, 'index'])->name('companies.index');
    Route::get('/companies/create', [CompanyController::class, 'create'])->name('companies.create');
    Route::post('/companies', [CompanyController::class, 'store'])->name('companies.store');
    Route::get('/companies/{id}/edit', [CompanyController::class, 'edit'])->name('companies.edit');
    Route::put('/companies/{id}', [CompanyController::class, 'update'])->name('companies.update');
    Route::delete('/companies/{id}', [CompanyController::class, 'destroy'])->name('companies.destroy');
    Route::post('/companies/import', [CompanyController::class, 'import'])->name('companies.import');
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');


        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::get('/users/{id}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{id}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');

    Route::get('/folders/download/{id}', [FolderController::class, 'download'])->name('folders.download');
    Route::get('/logs/login', [UserLoginLogController::class, 'index'])->name('logs.login');
    Route::post('/folders/search-files', [FolderController::class, 'searchFiles'])->name('folders.searchFiles');
    Route::post('/folders/{folder}/ngay-nop', [FolderController::class, 'setNgayNop'])->name('folders.setNgayNop');
    Route::get('/folders/yearly-manager', [FolderController::class, 'yearlyManagerView'])->name('folders.yearly-manager');
    Route::post('/api/chatbot', [ChatbotController::class, 'handle'])->name('ai.support');
});
