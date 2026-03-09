<?php

namespace App\Http\Controllers;

use App\Imports\HeaderImport;
use App\Exports\DynamicExcelExport;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use App\Services\MaterialCodeService;
use App\Services\ProductSimilarityService;
use App\Models\ProductImport;
class ToolController extends Controller
{
    protected $materialCodeService;
    protected $productSimilarityService;
    public function __construct(MaterialCodeService $materialCodeService, ProductSimilarityService $productSimilarityService)
    {
        $this->materialCodeService = $materialCodeService;
        $this->productSimilarityService = $productSimilarityService;
    }
    public function renderFile()
    {
        return view('tools.render-file');
    }
    public function import(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|mimes:xlsx,xls'
        ]);
        try {
            $request->validate([
                'excel_file' => 'required|mimes:xlsx,xls'
            ]);

            $file = $request->file('excel_file');

            $path = $file->store('temp_excel');

            $import = new \App\Imports\HeaderImport();
            Excel::import($import, $file);

            return response()->json([
                'status' => true,
                'headers' => $import->header,
                'file_path' => $path,
            ]);
        }catch (\Exception $e){
            return response()->json(['error'=>$e->getMessage()]);
        }

    }


    private function isMaterialCodeColumn($header)
    {
        $header = strtolower(trim($header));

        $keywords = [
            'mã vt',
            'ma vt',
            'mã vật tư',
            'ma vat tu',
            'mavt',
            'material code'
        ];

        foreach ($keywords as $keyword) {
            if (str_contains($header, $keyword)) {
                return true;
            }
        }

        return false;
    }
    //export
    public function export(Request $request)
    {
        $request->validate([
            'fields' => 'required|array',
            'file_path' => 'required'
        ]);

        $filePath = $request->file_path;

        if (!Storage::disk('local')->exists($filePath)) {
            return response()->json(['error' => 'File không tồn tại'], 400);
        }

        // Đọc file
        $rows = Excel::toArray([], $filePath, 'local')[0] ?? [];

        if (empty($rows)) {
            return response()->json(['error' => 'File rỗng'], 400);
        }

        $headers = array_map(fn($h) => trim($h), $rows[0]);
        unset($rows[0]);

        /*
        |--------------------------------------------------------------------------
        | 1️⃣ Map header -> index (chỉ loop 1 lần)
        |--------------------------------------------------------------------------
        */
        $headerMap = [];

        foreach ($headers as $index => $header) {
            $normalized = normalize_excel_header($header);
            $headerMap[$normalized] = $index;
        }

        /*
        |--------------------------------------------------------------------------
        | 2️⃣ Detect các cột quan trọng
        |--------------------------------------------------------------------------
        */
        $unitIndex = findColumnIndex($headerMap, ['Đơn vị tính']);
        $priceIndex = findColumnIndex($headerMap, ['Đơn giá']);
        $productNameIndex = findColumnIndex($headerMap, ['Tên hàng hóa',' dịch vụ']);
        $taxCodeIndex     = findColumnIndex($headerMap, ['MST người bán', 'Tên MST người bán']);
        $materialCodeIndex= findColumnIndex($headerMap, [
            'mã vt', 'ma vt', 'mã vật tư', 'ma vat tu', 'material code'
        ]);

        /*
        |--------------------------------------------------------------------------
        | 3️⃣ Map selected fields -> index
        |--------------------------------------------------------------------------
        */
        $selectedIndexes = [];

        foreach ($request->fields as $field) {
            $normalized = normalize_excel_header($field);
            if (isset($headerMap[$normalized])) {
                $selectedIndexes[] = $headerMap[$normalized];
            }
        }

        /*
        |--------------------------------------------------------------------------
        | 4️⃣ Build dữ liệu export
        |--------------------------------------------------------------------------
        */
        $products = ProductImport::select(
            'id',
            'product_name',
            'material_code',
            'tax_code'
        )->get();
        $usedMaterialCodes = $products
            ->pluck('material_code')
            ->filter()
            ->map(fn($code) => strtoupper(trim((string) $code)))
            ->flip()
            ->all();
        $newProducts = [];
        $filteredData = [];

        foreach ($rows as $row) {

            $productName = trim($row[$productNameIndex] ?? '');
            $taxCode = $row[$taxCodeIndex] ?? null;
            $unit = $row[$unitIndex] ?? null;
            $price = parsePrice($row[$priceIndex] ?? null);
            if(!$productName){
                continue;
            }
            /*
            kiểm tra trùng
            */
            $duplicate = $this->productSimilarityService
                ->findDuplicateProduct($productName, $products, 99, $taxCode);
            if($duplicate['matched']){
                /*
                lấy mã DB
                */
                $materialCode = $duplicate['product']->material_code;
            }else{
                /*
                sinh mã mới
                */
                $materialCode = $this->materialCodeService
                    ->generate($productName,$taxCode);

                $materialCode = strtoupper(trim((string) $materialCode));
                $baseCode = $materialCode;
                $counter = 1;
                while (isset($usedMaterialCodes[$materialCode])) {
                    $materialCode = $baseCode . str_pad((string) $counter, 2, '0', STR_PAD_LEFT);
                    $counter++;
                }
                $usedMaterialCodes[$materialCode] = true;
                /*
                thêm vào danh sách insert
                */
                $newProducts[] = [
                    'tax_code'=>$taxCode,
                    'material_code'=>$materialCode,
                    'product_name'=>$productName,
                    'unit'=>$unit,
                    'price'=>$price,
                    'created_at'=>now(),
                    'updated_at'=>now()
                ];
                /*
                thêm vào danh sách để tránh trùng tiếp
                */
                $products->push((object)[
                    'product_name'=>$productName,
                    'material_code'=>$materialCode
                ]);
            }

            /*
            build row export
            */

            $newRow=[];
            foreach ($selectedIndexes as $index){
                if($index==$materialCodeIndex){
                    $newRow[]=$materialCode;
                }else{
                    $newRow[]=$row[$index]??null;
                }
            }
            $filteredData[]=$newRow;
        }

        if (!empty($newProducts)) {
            // Insert theo lô để tránh câu lệnh quá lớn
            foreach (array_chunk($newProducts, 500) as $chunk) {
                ProductImport::insert($chunk);
            }
        }

        return Excel::download(
            new DynamicExcelExport($filteredData, $request->fields),
            'export-'.date('d-m-Y').'.xlsx'
        );
    }

}
