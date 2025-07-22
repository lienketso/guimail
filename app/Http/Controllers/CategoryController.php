<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Categories;

class CategoryController extends Controller
{
    public function index()
    {

        $q = Categories::query();
        if(request('name')){
            $q->where('name', 'like', '%'.request('name').'%');
        }
        $categories = $q->orderBy('id', 'desc')->paginate(10);
        return view('categories.index', compact('categories'));
    }
    public function create()
    {
        return view('categories.create');
    }
    public function store(Request $request)
    {
        $request->merge(['slug' => str_slug($request->name,'-','')]);
        $category = Categories::create($request->all());
        return redirect()->route('categories.index')->with('success', 'Category created successfully');
    }
    public function edit($id)
    {
        $category = Categories::find($id);
        return view('categories.edit', compact('category'));
    }
    public function update(Request $request, $id)
    {
        $category = Categories::find($id);
        $request->merge(['slug' => str_slug($request->name,'-','')]);
        $category->update($request->all());
        return redirect()->route('categories.index')->with('success', 'Category updated successfully');
    }
    public function destroy($id)
    {
        $category = Categories::find($id);
        $category->delete();
        return redirect()->route('categories.index')->with('success', 'Category deleted successfully');
    }

}
