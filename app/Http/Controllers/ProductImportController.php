<?php

namespace App\Http\Controllers;

use App\Models\ProductImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Validator;

class ProductImportController extends Controller
{
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

        foreach ($rows as $index => $row) {
            // Bỏ qua dòng tiêu đề nếu đúng header mẫu
            if ($index === 0 && ($row[0] === 'Mã số thuế' || $row[1] === 'Mã vật tư')) {
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

            ProductImport::updateOrCreate(
                ['material_code' => $row[1]],
                [
                    'tax_code'     => $row[0],
                    'product_name' => $row[2],
                    'unit'         => $row[3] ?? null,
                    'price'        => $row[4] ?? null,
                ]
            );
        }

        if (count($errors)) {
            return redirect()->back()->with('success', 'Import hoàn thành với một số lỗi: ' . implode('; ', $errors));
        }

        return redirect()->back()->with('success', 'Import sản phẩm thành công!');
    }
}

