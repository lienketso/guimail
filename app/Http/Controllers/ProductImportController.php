<?php

namespace App\Http\Controllers;

use App\Models\ProductImport;
use App\Services\ProductSimilarityService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Validator;

class ProductImportController extends Controller
{
    public function __construct(
        protected ProductSimilarityService $productSimilarityService
    ) {}

    public function index(Request $request)
    {
        $query = ProductImport::query();

        if ($search = $request->get('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('product_name', 'like', '%' . $search . '%')
                    ->orWhere('material_code', 'like', '%' . $search . '%')
                    ->orWhere('tax_code', 'like', '%' . $search . '%');
            });
        }

        $products = $query->orderByDesc('id')->paginate(20)->withQueryString();

        return view('products.index', compact('products'));
    }

    public function create()
    {
        return view('products.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'tax_code'      => 'required|string|max:255',
            'material_code' => 'required|string|max:255|unique:product_imports,material_code',
            'product_name'  => 'required|string|max:255',
            'unit'          => 'nullable|string|max:255',
            'price'         => 'nullable|numeric',
        ]);

        $products = ProductImport::select('id', 'product_name', 'material_code', 'tax_code')->get();
        $duplicate = $this->productSimilarityService->findDuplicateProduct(
            $data['product_name'],
            $products,
            99,
            $data['tax_code']
        );

        if ($duplicate['matched']) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['product_name' => 'Sản phẩm trùng hoặc tương tự đã tồn tại cho MST này (độ tương đồng: ' . round($duplicate['similarity'], 1) . '%).']);
        }

        ProductImport::create($data);

        return redirect()
            ->route('products.index')
            ->with('success', 'Thêm sản phẩm thành công');
    }

    public function edit($id)
    {
        $product = ProductImport::findOrFail($id);

        return view('products.edit', compact('product'));
    }

    public function update(Request $request, $id)
    {
        $product = ProductImport::findOrFail($id);

        $data = $request->validate([
            'tax_code'      => 'required|string|max:255',
            'material_code' => 'required|string|max:255|unique:product_imports,material_code,' . $product->id,
            'product_name'  => 'required|string|max:255',
            'unit'          => 'nullable|string|max:255',
            'price'         => 'nullable|numeric',
        ]);

        $products = ProductImport::where('id', '!=', $product->id)
            ->select('id', 'product_name', 'material_code', 'tax_code')
            ->get();
        $duplicate = $this->productSimilarityService->findDuplicateProduct(
            $data['product_name'],
            $products,
            99,
            $data['tax_code']
        );

        if ($duplicate['matched']) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['product_name' => 'Sản phẩm trùng hoặc tương tự đã tồn tại cho MST này (độ tương đồng: ' . round($duplicate['similarity'], 1) . '%).']);
        }

        $product->update($data);

        return redirect()
            ->route('products.index')
            ->with('success', 'Cập nhật sản phẩm thành công');
    }

    public function destroy($id)
    {
        $product = ProductImport::findOrFail($id);
        $product->delete();

        return redirect()
            ->route('products.index')
            ->with('success', 'Xoá sản phẩm thành công');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls'
        ]);

        $file = $request->file('file');
        $data = Excel::toArray([], $file);
        $rows = $data[0] ?? [];
        $errors = [];

        $products = ProductImport::select('id', 'product_name', 'material_code', 'tax_code', 'unit', 'price')->get();

        foreach ($rows as $index => $row) {
            // Bỏ qua dòng tiêu đề nếu đúng header mẫu
            if ($index === 0 && (($row[0] ?? '') === 'Mã số thuế' || ($row[1] ?? '') === 'Mã VT')) {
                continue;
            }

            $validator = Validator::make([
                'tax_code'      => $row[0] ?? null,
                'material_code' => $row[1] ?? null,
                'product_name'  => $row[2] ?? null,
            ], [
                'tax_code'      => 'required',
                'material_code' => 'required',
                'product_name'  => 'required',
            ]);

            if ($validator->fails()) {
                $errors[] = "Dòng " . ($index + 1) . ": " . implode(', ', $validator->errors()->all());
                continue;
            }

            $taxCode = $row[0];
            $productName = trim($row[2] ?? '');

            $duplicate = $this->productSimilarityService->findDuplicateProduct(
                $productName,
                $products,
                99,
                $taxCode
            );

            if ($duplicate['matched']) {
                // Trùng: cập nhật bản ghi đã có (unit, price) thay vì thêm mới
                $existing = $duplicate['product'];
                $existing->update([
                    'unit'  => $row[3] ?? $existing->unit,
                    'price' => parsePrice($row[4] ?? null) ?? $existing->price,
                ]);
                continue;
            }

            // Không trùng: thêm mới và đẩy vào danh sách để các dòng sau so trùng
            $newProduct = ProductImport::create([
                'tax_code'     => $taxCode,
                'material_code'=> $row[1],
                'product_name' => $productName,
                'unit'         => $row[3] ?? null,
                'price'        => parsePrice($row[4] ?? null),
            ]);
            $products->push($newProduct);
        }

        if (count($errors)) {
            return redirect()->back()->with('success', 'Import hoàn thành với một số lỗi: ' . implode('; ', $errors));
        }

        return redirect()->back()->with('success', 'Import sản phẩm thành công!');
    }
}

