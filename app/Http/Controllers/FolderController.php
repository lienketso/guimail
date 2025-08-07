<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Company;
use App\Models\Folder;
use App\Models\File;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use SimpleXMLElement;

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
            'parent_id' => 'nullable', // Có thể là string hoặc integer
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

        // Kiểm tra nếu request có Content-Type là application/json (từ manager) hay form (từ tree)
        if ($request->header('Content-Type') === 'application/json') {
            return response()->json([
                'success' => true,
                'message' => 'Thư mục đã được tạo thành công',
                'folder' => [
                    'id' => $folder->id,
                    'name' => $folder->name,
                    'parent_id' => $folder->parent_id
                ]
            ]);
        } else {
            return response()->json([
                'id' => 'folder_' . $folder->id,
                'parent' => $folder->parent_id ? 'folder_' . $folder->parent_id : '#',
                'text' => $folder->name,
                'type' => 'folder'
            ]);
        }
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
            'a_attr' => ['href' => route('folders.download',$fileModel->id), 'target' => '_blank']
//            'a_attr' => ['href' => Storage::url($fileModel->path), 'target' => '_blank']
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

    // Hàm đệ quy lấy cây thư mục
    private function getFolderTree($folders, $parentId = null)
    {
        $branch = [];
        foreach ($folders as $folder) {
            if ($folder->parent_id == $parentId) {
                $children = $this->getFolderTree($folders, $folder->id);
                $folder->children_tree = $children;
                $branch[] = $folder;
            }
        }
        return $branch;
    }

    public function managerView(Request $request)
    {
        $tax_code = $request->input('tax_code');
        $company = Company::where('tax_code', $tax_code)->firstOrFail();

        $folders = Folder::where('company_id', $company->id)
            ->orderBy('parent_id')
            ->orderBy('name','desc')
            ->get();

        $folderTree = $this->getFolderTree($folders);

        return view('folders.manager', compact('company', 'folderTree'));
    }

    public function uploadXml(Request $request, $companyId)
    {
        $request->validate([
            'xml_files.*' => 'required|file|mimes:xml',
        ]);

        $company = Company::findOrFail($companyId);

        foreach ($request->file('xml_files') as $file) {
            $xmlContent = file_get_contents($file->getRealPath());
            try {
                $xml = new SimpleXMLElement($xmlContent);

                // Lấy mã số thuế từ XML
                $mst = (string)($xml->HSoKhaiThue->TTinChung->TTinTKhaiThue->NNT->mst ?? '');
                if (empty($mst) || $mst !== $company->tax_code) {
                    // Nếu không đúng mã số thuế thì bỏ qua file này
                    continue;
                }

                // Lấy thông tin cần thiết từ XML
                $soLan = (string)$xml->HSoKhaiThue->TTinChung->TTinTKhaiThue->TKhaiThue->soLan ?? '0';
                $ky = $xml->HSoKhaiThue->TTinChung->TTinTKhaiThue->TKhaiThue->KyKKhaiThue;
                $ngaykhai = $xml->HSoKhaiThue->TTinChung->TTinTKhaiThue->TKhaiThue->ngayLapTKhai;
                $ngaynop = \Carbon\Carbon::parse($ngaykhai)->format('Y-m-d');
                $kieuKy = (string)$ky->kieuKy ?? '';
                $kyKKhai = (string)$ky->kyKKhai ?? '';
                $nam = '';
                $quy = '';
                if ($kieuKy == 'Q' && preg_match('/(\d)\/(\d{4})/', $kyKKhai, $m)) {
                    $quy = 'Quý ' . $m[1];
                    $nam = $m[2];
                } elseif ($kieuKy == 'T' && preg_match('/(\d{2})\/(\d{4})/', $kyKKhai, $m)) {
                    $quy = 'Tháng ' . $m[1];
                    $nam = $m[2];
                } else {
                    $nam = date('Y');
                }

                $maTKhai = (string)$xml->HSoKhaiThue->TTinChung->TTinTKhaiThue->TKhaiThue->maTKhai ?? '';
                $subFolderName = '';
                if ($maTKhai === '842') {
                    $subFolderName = 'VAT';
                } elseif ($maTKhai === '892') {
                    $subFolderName = 'TNDN';
                } elseif ($maTKhai === '402' || $maTKhai==='684') {
                    $subFolderName = 'BCTC';
                } elseif ($maTKhai === '953') {
                    $subFolderName = 'TNCN';
                } else{
                    $subFolderName = 'Báo cáo khác';
                }

                // 1. Tìm hoặc tạo folder Năm
                $yearFolder = Folder::firstOrCreate([
                    'name' => $nam,
                    'parent_id' => null,
                    'company_id' => $company->id,
                ]);

                // 2. Nếu có subFolderName thì tạo folder con (VAT/TNDN/Báo cáo khác)
                $parentForLan = $yearFolder;
                if ($subFolderName) {
                    $subFolder = Folder::firstOrCreate([
                        'name' => $subFolderName,
                        'parent_id' => $yearFolder->id,
                        'company_id' => $company->id,
                    ]);
                    $parentForLan = $subFolder;
                }

                // 3. Tìm hoặc tạo folder Quý/Tháng (nếu có)
                $quyFolder = null;
                if ($quy) {
                    $quyFolder = Folder::firstOrCreate([
                        'name' => $quy,
                        'parent_id' => $parentForLan->id,
                        'company_id' => $company->id,
                    ]);
                    $parentForLan = $quyFolder;
                }

                // 4. Tìm hoặc tạo folder Lần
                $lanFolder = Folder::firstOrCreate([
                    'name' => 'Lần ' . $soLan,
                    'parent_id' => $parentForLan->id,
                    'company_id' => $company->id,
                    'ngay_nop' => $ngaynop,
                ]);

                // 6. Kiểm tra trùng tên file trong cùng folder Lần
                $fileName = $file->getClientOriginalName();
                $fileExists = \App\Models\File::where('folder_id', $lanFolder->id)
                    ->where('name', $fileName)
                    ->exists();
                if ($fileExists) {
                    return back()->with('error', 'File "' . $fileName . '" đã tồn tại trong thư mục này!');
                }

                // 7. Lưu file vật lý vào đúng disk public
                $folderPath = "company_{$company->id}/{$nam}";
                if ($subFolderName) $folderPath .= "/{$subFolderName}";
                if ($quy) $folderPath .= "/{$quy}";
                $folderPath .= "/Lan_{$soLan}";
                $path = $file->storeAs($folderPath, $fileName, 'public');

                // 8. Tạo bản ghi file
                \App\Models\File::create([
                    'name' => $fileName,
                    'folder_id' => $lanFolder->id,
                    'path' => $path,
                ]);

            } catch (\Exception $e) {
                return back()->with('error', 'File XML không hợp lệ hoặc lỗi: ' . $e->getMessage());
            }
        }

        return back()->with('success', 'Upload thành công!');
    }

    public function setNgayNop(Request $request, $folderId)
    {
        $request->validate([
            'ngay_nop' => 'required|date',
        ]);
        $folder = \App\Models\Folder::findOrFail($folderId);
        $folder->ngay_nop = $request->ngay_nop;
        $folder->save();
        return response()->json(['success' => true]);
    }

    public function yearlyManagerView(Request $request)
    {
        $tax_code = $request->input('tax_code');
        $company = Company::where('tax_code', $tax_code)->firstOrFail();

        // Lấy tất cả các năm có thư mục gốc (parent_id = null)
        $years = Folder::where('company_id', $company->id)
            ->whereNull('parent_id')
            ->orderByDesc('name')
            ->pluck('name')
            ->toArray();

        // Chọn năm, mặc định là năm mới nhất
        $selectedYear = $request->input('year', $years[0] ?? null);

        // Lấy folder năm đã chọn
        $yearFolder = Folder::where('company_id', $company->id)
            ->whereNull('parent_id')
            ->where('name', $selectedYear)
            ->first();

        $folderTree = [];
        if ($yearFolder) {
            $folders = Folder::where('company_id', $company->id)->get();
            $folderTree = $this->getFolderTree($folders, $yearFolder->id);
        }

        // Tạo biến thống kê
        $reportTypes = ['VAT', 'BCTC', 'TNDN', 'TNCN','Báo cáo khác'];
        $quarters = ['Quý 1', 'Quý 2', 'Quý 3', 'Quý 4'];
        $folderStats = [];

        foreach ($reportTypes as $type) {
            $folderStats[$type] = [];

            if ($type === 'VAT') {
                // Mặc định khởi tạo quý 1 -> quý 4
                foreach ($quarters as $quarter) {
                    $folderStats[$type][$quarter] = [
                        'count' => 0,
                        'dates' => [],
                    ];
                }
            }
        }

        // Duyệt dữ liệu folder thực tế
        foreach ($folderTree as $parent) {
            $parentName = $parent->name;
            if (in_array($parentName, $reportTypes)) {
                if (!empty($parent->children)) {
                    foreach ($parent->children as $child) {
                        $childName = $child->name;

                        if ($parentName === 'VAT' && in_array($childName, $quarters)) {
                            // Trường hợp VAT + Quý
                            $dates = [];
                            $count = 0;
                            if (!empty($child->children)) {
                                $count = count($child->children);
                                foreach ($child->children as $lanFolder) {
                                    if (!empty($lanFolder->ngay_nop)) {
                                        $dates[] = \Carbon\Carbon::parse($lanFolder->ngay_nop)->format('d-m-Y');
                                    }
                                }
                            }
                            $folderStats[$parentName][$childName] = [
                                'count' => $count,
                                'dates' => $dates,
                            ];
                        } else {
                            // Trường hợp còn lại: hiển thị theo năm con
                            $yearChildName = $child->name;
                            $count = !empty($child->children) ? count($child->children) : 0;

                            if (!isset($folderStats[$parentName][$yearChildName])) {
                                $folderStats[$parentName][$yearChildName] = [
                                    'count' => 0,
                                ];
                            }

//                            $folderStats[$parentName][$yearChildName]['count'] = $count;
                            $folderStats[$parentName][$yearChildName]['dates'] = [\Carbon\Carbon::parse($child->ngay_nop)->format('d-m-Y')];
                            // Bạn có thể thêm `dates` nếu cần ở đây
                        }
                    }
                }
            }
        }

//        dd($folderStats);

        return view('folders.yearly_manager', [
            'company' => $company,
            'tax_code' => $tax_code,
            'selectedYear' => $selectedYear,
            'years' => $years,
            'folderTree' => $folderTree,
            'folderStats' => $folderStats,
        ]);
    }

}
