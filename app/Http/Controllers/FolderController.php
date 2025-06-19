<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Company;
use App\Models\Folder;
use App\Models\File;
use Illuminate\Support\Facades\Auth;

class FolderController extends Controller
{
    public function showTree(Request $request)
    {
        $tax_code = $request->input('tax_code');
        $company = \App\Models\Company::where('tax_code', $tax_code)->first();
        $company_id = $company ? $company->id : null;
        return view('folders.tree', compact('tax_code', 'company_id','company'));
    }
    // Lấy cây thư mục theo mã số thuế
    public function index(Request $request)
    {
        $tax_code = $request->input('tax_code');
        $company = Company::where('tax_code', $tax_code)->firstOrFail();
        $folders = Folder::where('company_id', $company->id)
            ->orderBy('parent_id')
            ->orderBy('sort_order')
            ->get();

        // Chuyển dữ liệu sang dạng jsTree cần
        $data = $folders->map(function($folder) {
            return [
                'id' => $folder->id,
                'parent' => $folder->parent_id ? $folder->parent_id : '#',
                'text' => $folder->name,
                'company_id' => $folder->conpany_id,
            ];
        });

        return response()->json($data);
    }

    // Tạo thư mục mới
    public function store(Request $request)
    {
        $user = Auth::user();
        if ($user->role === 'user') {
            // User thường chỉ được thêm, không sửa/xóa
        }
        $request->validate([
            'name' => 'required',
            'company_id' => 'required|exists:companies,id',
            'parent_id' => 'nullable|exists:folders,id',
        ]);
        $company_id = $request->company_id;
        $maxOrder = Folder::where('parent_id', $request->parent_id)
            ->where('company_id', $company_id)
            ->max('sort_order');
        $folder = Folder::create([
            'name' => $request->name,
            'parent_id' => $request->parent_id,
            'company_id' => $company_id,
            'sort_order' => is_null($maxOrder) ? 0 : $maxOrder + 1
        ]);
        return response()->json($folder);
    }

    // Upload file vào thư mục
    public function upload(Request $request)
    {
        $user = Auth::user();
        if ($user->role === 'user') {
            // User thường chỉ được thêm
        }
        $request->validate([
            'folder_id' => 'required|exists:folders,id',
            'file' => 'required|file',
        ]);
        $file = $request->file('file');
        $path = $file->store('uploads/' . date('Y') . '/' . date('m'), 'public');
        $fileModel = File::create([
            'name' => $file->getClientOriginalName(),
            'folder_id' => $request->folder_id,
            'path' => $path,
        ]);
        return response()->json($fileModel);
    }

    // Di chuyển thư mục (drag & drop)
    public function move(Request $request)
    {
        $user = Auth::user();
        if ($user->role === 'user') {
            return response()->json(['error' => 'Không có quyền'], 403);
        }
        $request->validate([
            'id' => 'required|exists:folders,id',
            'parent_id' => 'nullable|exists:folders,id',
            'order' => 'array'
        ]);
        $folder = Folder::findOrFail($request->id);
        $folder->parent_id = $request->parent_id;
        $folder->save();

        // Cập nhật sort_order cho các node cùng cấp
        if ($request->has('order')) {
            foreach ($request->order as $index => $folderId) {
                Folder::where('id', $folderId)->update(['sort_order' => $index]);
            }
        }

        return response()->json($folder);
    }

    // Xóa folder
    public function destroy($id)
    {
        $user = Auth::user();
        if ($user->role === 'user') {
            return response()->json(['error' => 'Không có quyền'], 403);
        }
        $folder = Folder::findOrFail($id);
        $folder->delete();
        return response()->json(['success' => true]);
    }

    // Đổi tên folder
    public function rename(Request $request, $id)
    {
        $user = Auth::user();
        if ($user->role === 'user') {
            return response()->json(['error' => 'Không có quyền'], 403);
        }
        $request->validate(['text' => 'required']);
        $folder = Folder::findOrFail($id);
        $folder->name = $request->text;
        $folder->save();
        return response()->json(['success' => true]);
    }
} 