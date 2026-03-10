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
            $file = $request->file('excel_file');
            $path = $file->store('temp_excel');

            $import = new \App\Imports\HeaderImport();
            Excel::import($import, $file);
            $headers = $import->header;

            // Đọc lại toàn bộ file từ storage để lấy data rows và check trùng
            $allRows = [];
            $fullPath = Storage::disk('local')->path($path);
            if (is_file($fullPath)) {
                $loaded = Excel::toArray([], $fullPath);
                $allRows = $loaded[0] ?? [];
            }
            if (empty($allRows)) {
                $loaded = Excel::toArray([], $path, 'local');
                $allRows = $loaded[0] ?? [];
            }
            $headerRow = $allRows[0] ?? [];
            $dataRows = array_slice($allRows, 1);

            $headerMap = [];
            foreach ($headerRow as $idx => $h) {
                $key = normalize_excel_header($h ?? '');
                $headerMap[$key !== '' ? $key : ('__col_' . $idx)] = $idx;
            }

            $productNameIndex = findColumnIndex($headerMap, ['Tên hàng hóa', ' dịch vụ', 'Tên sản phẩm', 'Tên hàng', 'Product name', 'product name']);
            $taxCodeIndex     = findColumnIndex($headerMap, ['MST người bán', 'Tên MST người bán', 'MST', 'Mã số thuế', 'Ma so thue', 'Tax code']);
            $unitIndex        = findColumnIndex($headerMap, ['Đơn vị tính', 'Unit']);
            $priceIndex       = findColumnIndex($headerMap, ['Đơn giá', 'Price']);
            if ($productNameIndex === null) {
                $productNameIndex = 0;
            }
            if ($taxCodeIndex === null) {
                $taxCodeIndex = 1;
            }

            $products = ProductImport::select('id', 'product_name', 'material_code', 'tax_code', 'unit', 'price')->get();
            $duplicates = [];

            foreach ($dataRows as $dataIndex => $row) {
                $productName = trim((string) ($row[$productNameIndex] ?? ''));
                if ($productName === '') {
                    continue;
                }
                $taxCodeRaw = $row[$taxCodeIndex] ?? null;
                $taxCode = $taxCodeRaw !== null && $taxCodeRaw !== '' ? (string) $taxCodeRaw : null;
                $unit   = $row[$unitIndex] ?? null;
                $price  = parsePrice($row[$priceIndex] ?? null);

                $duplicate = $this->productSimilarityService->findDuplicateProduct(
                    $productName,
                    $products,
                    90,
                    $taxCode
                );

                if ($duplicate['matched']) {
                    $db = $duplicate['product'];
                    $duplicates[] = [
                        'row_index' => $dataIndex,
                        'similarity' => round($duplicate['similarity'], 1),
                        'excel' => [
                            'product_name' => $productName,
                            'tax_code'     => $taxCode,
                            'unit'         => $unit,
                            'price'        => $price,
                        ],
                        'db' => [
                            'id'            => $db->id,
                            'product_name'  => $db->product_name,
                            'material_code' => $db->material_code,
                            'tax_code'      => $db->tax_code,
                            'unit'          => $db->unit,
                            'price'         => $db->price,
                        ],
                    ];
                }
            }

            return response()->json([
                'status'     => true,
                'headers'    => $headers,
                'file_path'  => $path,
                'duplicates' => $duplicates,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
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
        $rows = array_values($rows); // index 0,1,2... để khớp duplicate_choices.row_index

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

        $duplicateChoices = [];
        foreach ($request->input('duplicate_choices', []) as $c) {
            $duplicateChoices[(int) ($c['row_index'] ?? -1)] = $c;
        }

        foreach ($rows as $dataRowIndex => $row) {

            $productName = trim($row[$productNameIndex] ?? '');
            $taxCode = $row[$taxCodeIndex] ?? null;
            $unit = $row[$unitIndex] ?? null;
            $price = parsePrice($row[$priceIndex] ?? null);
            if (!$productName) {
                continue;
            }

            // Nếu user đã chọn "Dùng DB" cho dòng trùng → dùng dữ liệu từ DB để export
            if (isset($duplicateChoices[$dataRowIndex]) && ($duplicateChoices[$dataRowIndex]['use'] ?? '') === 'db') {
                $dbId = (int) ($duplicateChoices[$dataRowIndex]['db_id'] ?? 0);
                $dbProduct = $dbId ? ProductImport::find($dbId) : null;
                if ($dbProduct) {
                    $virtualRow = $row;
                    $virtualRow[$productNameIndex] = $dbProduct->product_name;
                    $virtualRow[$taxCodeIndex] = $dbProduct->tax_code;
                    if ($unitIndex !== null) {
                        $virtualRow[$unitIndex] = $dbProduct->unit;
                    }
                    if ($priceIndex !== null) {
                        $virtualRow[$priceIndex] = $dbProduct->price;
                    }
                    if ($materialCodeIndex !== null) {
                        $virtualRow[$materialCodeIndex] = $dbProduct->material_code;
                    }
                    $newRow = [];
                    foreach ($selectedIndexes as $idx) {
                        $newRow[] = $virtualRow[$idx] ?? null;
                    }
                    $filteredData[] = $newRow;
                    continue;
                }
            }

            /*
            kiểm tra trùng
            */
            $duplicate = $this->productSimilarityService
                ->findDuplicateProduct($productName, $products, 90, $taxCode);
            if ($duplicate['matched']) {
                /*
                lấy mã DB
                */
                $materialCode = $duplicate['product']->material_code;
            } else {
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
