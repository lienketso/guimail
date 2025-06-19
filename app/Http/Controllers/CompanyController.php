<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Company;
use Illuminate\Support\Facades\Auth;

class CompanyController extends Controller
{
    public function index()
    {
        $companies = Company::all();
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
        $data = $request->only('name', 'tax_code', 'founded_year', 'phone', 'address', 'ceo_name', 'description');
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
        $data = $request->only('name', 'tax_code', 'founded_year', 'phone', 'address', 'ceo_name', 'description');
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

    private function authorizeAdmin()
    {
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Chỉ admin mới được thao tác!');
        }
    }
} 