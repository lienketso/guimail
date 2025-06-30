<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Company;
use App\Models\Folder;
use App\Models\File;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FolderController extends Controller
{
    public function showTree(Request $request)
    {
        $tax_code = $request->input('tax_code');
        $company = \App\Models\Company::where('tax_code', $tax_code)->first();
        if (!$company) {
            return redirect()->route('taxcode.form')->withErrors(['tax_code' => 'Không tìm thấy công ty với mã số thuế này!']);
        }
        $company_id = $company->id;
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

        $folderData = $folders->map(function($folder) {
            return [
                'id' => 'folder_' . $folder->id,
                'parent' => $folder->parent_id ? 'folder_' . $folder->parent_id : '#',
                'text' => $folder->name,
                'type' => 'folder'
            ];
        });

        $folderIds = $folders->pluck('id');
        $files = File::whereIn('folder_id', $folderIds)->get();

        $fileData = $files->map(function($file) {
            return [
                'id' => 'file_' . $file->id,
                'parent' => 'folder_' . $file->folder_id,
                'text' => $file->name,
                'type' => 'file',
                'a_attr' => ['href' => route('folders.download', $file->id)]
            ];
        });

        $data = $folderData->concat($fileData);
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
            'parent_id' => 'nullable|string', // Sẽ có dạng 'folder_xx' hoặc '#'
        ]);
        
        $parentId = $request->parent_id;
        if ($parentId && $parentId !== '#') {
            $parentId = str_replace('folder_', '', $parentId);
        } else {
            $parentId = null;
        }

        $company_id = $request->company_id;
        $maxOrder = Folder::where('parent_id', $parentId)
            ->where('company_id', $company_id)
            ->max('sort_order');

        $folder = Folder::create([
            'name' => $request->name,
            'parent_id' => $parentId,
            'company_id' => $company_id,
            'sort_order' => is_null($maxOrder) ? 0 : $maxOrder + 1
        ]);
        return response()->json([
            'id' => 'folder_' . $folder->id,
            'parent' => $folder->parent_id ? 'folder_' . $folder->parent_id : '#',
            'text' => $folder->name,
            'type' => 'folder'
        ]);
    }

    // Upload file vào thư mục
    public function upload(Request $request)
    {
        $user = Auth::user();
        if ($user->role === 'user') {
            // User thường chỉ được thêm
        }
        $request->validate([
            'folder_id' => 'required|string',
            'file' => 'required|file',
        ]);

        $folderId = str_replace('folder_', '', $request->folder_id);
        
        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();
        $filename = pathinfo($originalName, PATHINFO_FILENAME);
        $extension = $file->getClientOriginalExtension();
        $safeFilename = Str::slug($filename) . '.' . $extension;

        $path = $file->storeAs('uploads/' . date('Y') . '/' . date('m'), $safeFilename, 'public');
        
        $fileModel = File::create([
            'name' => $originalName,
            'folder_id' => $folderId,
            'path' => $path,
        ]);
        
        return response()->json([
            'id' => 'file_' . $fileModel->id,
            'parent' => 'folder_' . $fileModel->folder_id,
            'text' => $fileModel->name,
            'type' => 'file',
            'a_attr' => ['href' => Storage::url($fileModel->path), 'target' => '_blank']
        ]);
    }

    // Di chuyển thư mục (drag & drop)
    public function move(Request $request)
    {
        $id = $request->id;
        $parent_id = $request->parent_id;

        if (Str::startsWith($id, 'file_')) {
            // Xử lý file
            $fileId = (int)Str::after($id, 'file_');
            $file = File::findOrFail($fileId);

            // Lấy id thư mục cha mới (nếu có)
            $folderId = $parent_id == '#' ? null : (int)Str::after($parent_id, 'folder_');
            $file->folder_id = $folderId;
            $file->save();
        } elseif (Str::startsWith($id, 'folder_')) {
            // Xử lý folder
            $folderId = (int)Str::after($id, 'folder_');
            $folder = Folder::findOrFail($folderId);

            $parentFolderId = $parent_id == '#' ? null : (int)Str::after($parent_id, 'folder_');
            $folder->parent_id = $parentFolderId;
            $folder->save();

            // Cập nhật thứ tự cho các con nếu có
            if (is_array($request->order)) {
                foreach ($request->order as $index => $childId) {
                    if (Str::startsWith($childId, 'folder_')) {
                        Folder::where('id', (int)Str::after($childId, 'folder_'))->update(['sort_order' => $index]);
                    }
                }
            }
        }

        return response()->json(['success' => true]);
    }

    // Xóa folder hoặc file
    public function destroy($id)
    {
        $user = Auth::user();
        if ($user->role === 'user') {
            return response()->json(['error' => 'Không có quyền'], 403);
        }

        list($type, $nodeId) = explode('_', $id);

        if ($type === 'folder') {
            $folder = Folder::with('files', 'children')->findOrFail($nodeId);
            // Cần xóa đệ quy file và thư mục con
            $this->deleteFolderRecursive($folder);
        } elseif ($type === 'file') {
            $file = File::findOrFail($nodeId);
            Storage::disk('public')->delete($file->path);
            $file->delete();
        }

        return response()->json(['success' => true]);
    }
    
    private function deleteFolderRecursive(Folder $folder)
    {
        foreach ($folder->children as $child) {
            $this->deleteFolderRecursive($child);
        }

        foreach ($folder->files as $file) {
            Storage::disk('public')->delete($file->path);
            $file->delete();
        }

        $folder->delete();
    }


    // Đổi tên folder hoặc file
    public function rename(Request $request, $id)
    {
        $user = Auth::user();
        if ($user->role === 'user') {
            return response()->json(['error' => 'Không có quyền'], 403);
        }
        $request->validate(['text' => 'required']);

        list($type, $nodeId) = explode('_', $id);
        
        if ($type === 'folder') {
            $folder = Folder::findOrFail($nodeId);
            $folder->name = $request->text;
            $folder->save();
        } elseif ($type === 'file') {
            $file = File::findOrFail($nodeId);
            $file->name = $request->text;
            $file->save();
        }

        return response()->json(['success' => true]);
    }

    public function download($id)
    {
        $file = File::findOrFail($id);
        return \Storage::disk('public')->download($file->path, $file->name);
    }

    public function searchFiles(Request $request)
    {
        $request->validate([
            'tax_code' => 'required',
            'keyword' => 'required|string|min:1',
        ]);
        $company = \App\Models\Company::where('tax_code', $request->tax_code)->firstOrFail();
        $keyword = $request->keyword;
        $files = \App\Models\File::whereHas('folder', function($q) use ($company) {
            $q->where('company_id', $company->id);
        })
        ->where('name', 'like', '%' . $keyword . '%')
        ->orderByDesc('id')
        ->get();
        return view('folders.search_result', compact('files', 'keyword', 'company'));
    }
} 