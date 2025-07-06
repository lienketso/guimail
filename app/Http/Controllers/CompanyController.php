<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Company;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Validator;

class CompanyController extends Controller
{
    public function index()
    {
        $this->authorizeAdmin();
        $query = Company::query();
        if (request('name')) {
            $query->where('name', 'like', '%' . request('name') . '%');
        }
        if (request('tax_code')) {
            $query->where('tax_code', 'like', '%' . request('tax_code') . '%');
        }
        $companies = $query->orderByDesc('id')->paginate(20)->withQueryString();
        return view('companies.index', compact('companies'));
    }

    public function create()
    {
        $this->authorizeAdmin();
        return view('companies.create');
    }

    public function store(Request $request)
    {
        $this->authorizeAdmin();
        $request->validate([
            'name' => 'required',
            'tax_code' => 'required|unique:companies,tax_code',
            'founded_year' => 'required|integer|min:1800|max:' . date('Y')
        ]);
        $data = $request->only('name', 'tax_code', 'founded_year', 'phone', 'address', 'ceo_name', 'description', 'email');
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('company_logos', 'public');
            $data['logo'] = $logoPath;
        }
        $company = Company::create($data);
        // Tạo folder cho từng năm từ founded_year đến năm hiện tại
        $start = (int)$company->founded_year;
        $end = (int)date('Y');
        for ($year = $start; $year <= $end; $year++) {
            \App\Models\Folder::create([
                'name' => (string)$year,
                'parent_id' => null,
                'company_id' => $company->id,
            ]);
        }
        return redirect()->route('companies.index')->with('success', 'Đã thêm công ty và tạo thư mục năm!');
    }

    public function edit($id)
    {
        $this->authorizeAdmin();
        $company = Company::findOrFail($id);
        return view('companies.edit', compact('company'));
    }

    public function update(Request $request, $id)
    {
        $this->authorizeAdmin();
        $company = Company::findOrFail($id);
        $request->validate([
            'name' => 'required',
            'tax_code' => 'required|unique:companies,tax_code,' . $company->id,
            'founded_year' => 'required|integer|min:1800|max:' . date('Y'),
        ]);
        $data = $request->only('name', 'tax_code', 'founded_year', 'phone', 'address', 'ceo_name', 'description', 'email');
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('company_logos', 'public');
            $data['logo'] = $logoPath;
        } else {
            $data['logo'] = $company->logo;
        }
        $company->update($data);
        return redirect()->route('companies.index')->with('success', 'Đã cập nhật công ty!');
    }

    public function destroy($id)
    {
        $this->authorizeAdmin();
        $company = Company::findOrFail($id);
        $company->delete();
        return redirect()->route('companies.index')->with('success', 'Đã xóa công ty!');
    }

    public function import(Request $request)
    {
        $this->authorizeAdmin();
        $request->validate([
            'file' => 'required|mimes:xlsx,xls'
        ]);

        $file = $request->file('file');
        $data = Excel::toArray([], $file);
        $rows = $data[0];
        $errors = [];
        foreach ($rows as $index => $row) {
            // Bỏ qua dòng tiêu đề nếu có
            if ($index === 0 && ($row[0] == 'Mã số thuế' || $row[1] == 'Tên công ty')) {
                continue;
            }
            $validator = Validator::make([
                'name' => $row[0] ?? null,
                'tax_code' => $row[1] ?? null,
                'founded_year' => $row[2] ?? null,
            ], [
                'name' => 'required',
                'tax_code' => 'required',
                'founded_year' => 'required',
            ]);
            if ($validator->fails()) {
                $errors[] = "Dòng " . ($index + 1) . ": " . implode(', ', $validator->errors()->all());
                continue;
            }
            $company = Company::updateOrCreate(
                ['tax_code' => $row[0]],
                [
                    'name' => $row[1],
                    'founded_year' => $row[2],
                    'address' => $row[3],
                    'phone' => $row[4],
                    'ceo_name' => $row[5]
                ]
            );
            // Tạo folder cho từng năm từ founded_year đến năm hiện tại
            $start = (int)$company->founded_year;
            $end = (int)date('Y');
            for ($year = $start; $year <= $end; $year++) {
                \App\Models\Folder::firstOrCreate([
                    'name' => (string)$year,
                    'parent_id' => null,
                    'company_id' => $company->id,
                ]);
            }
        }

        if (count($errors)) {
            return redirect()->back()->with('success', 'Import hoàn thành với một số lỗi: ' . implode('; ', $errors));
        }
        return redirect()->back()->with('success', 'Import thành công!');
    }

    private function authorizeAdmin()
    {
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Chỉ admin mới được thao tác!');
        }
    }
} 