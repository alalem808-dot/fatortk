<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Unit;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $tenantId = auth()->user()->tenant_id;
        $categories = Category::where('tenant_id', $tenantId)->withCount('products')->latest()->get();
        $units = Unit::where('tenant_id', $tenantId)->withCount('products')->latest()->get();
        return view('categories.index', compact('categories', 'units'));
    }

    // ===== Categories =====
    public function storeCategory(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);
        Category::create(['tenant_id' => auth()->user()->tenant_id, 'name' => $request->name]);
        return back()->with('success', 'تم إضافة الفئة بنجاح.');
    }

    public function updateCategory(Request $request, Category $category)
    {
        abort_if($category->tenant_id !== auth()->user()->tenant_id, 403);
        $request->validate(['name' => 'required|string|max:255']);
        $category->update(['name' => $request->name]);
        return back()->with('success', 'تم تعديل الفئة.');
    }

    public function destroyCategory(Category $category)
    {
        abort_if($category->tenant_id !== auth()->user()->tenant_id, 403);
        $category->delete();
        return back()->with('success', 'تم حذف الفئة.');
    }

    // ===== Units =====
    public function storeUnit(Request $request)
    {
        $request->validate(['name' => 'required|string|max:100', 'symbol' => 'nullable|string|max:20']);
        Unit::create(['tenant_id' => auth()->user()->tenant_id, 'name' => $request->name, 'symbol' => $request->symbol]);
        return back()->with('success', 'تم إضافة وحدة القياس بنجاح.');
    }

    public function updateUnit(Request $request, Unit $unit)
    {
        abort_if($unit->tenant_id !== auth()->user()->tenant_id, 403);
        $request->validate(['name' => 'required|string|max:100', 'symbol' => 'nullable|string|max:20']);
        $unit->update(['name' => $request->name, 'symbol' => $request->symbol]);
        return back()->with('success', 'تم تعديل وحدة القياس.');
    }

    public function destroyUnit(Unit $unit)
    {
        abort_if($unit->tenant_id !== auth()->user()->tenant_id, 403);
        $unit->delete();
        return back()->with('success', 'تم حذف وحدة القياس.');
    }
}
