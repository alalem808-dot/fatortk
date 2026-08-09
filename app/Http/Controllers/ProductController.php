<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Unit;
use App\Services\StockService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(private StockService $stockService) {}

    public function index(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;
        $products = Product::where('tenant_id', $tenantId)
            ->with('category')
            ->when($request->search, fn($q) => $q->where('name', 'like', "%{$request->search}%")->orWhere('sku', 'like', "%{$request->search}%"))
            ->when($request->category_id, fn($q) => $q->where('category_id', $request->category_id))
            ->when($request->low_stock, fn($q) => $q->whereColumn('stock_quantity', '<=', 'min_stock_alert'))
            ->paginate(15);

        $categories = Category::where('tenant_id', $tenantId)->get();
        return view('products.index', compact('products', 'categories'));
    }

    public function create()
    {
        $tenantId = auth()->user()->tenant_id;
        $categories = Category::where('tenant_id', $tenantId)->get();
        $units = Unit::where('tenant_id', $tenantId)->get();
        return view('products.create', compact('categories', 'units'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'       => 'required|string|max:255',
            'unit_price' => 'required|numeric|min:0',
        ]);

        $tenantId = auth()->user()->tenant_id;

        $data = $request->except('image');
        $data['tenant_id'] = $tenantId;

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $product = Product::create($data);

        if ($product->stock_quantity > 0) {
            $this->stockService->move($product, 'in', $product->stock_quantity, 'manual', null, 'رصيد افتتاحي');
        }

        return redirect()->route('products.index')->with('success', 'تم إضافة المنتج.');
    }

    public function edit(Product $product)
    {
        abort_if($product->tenant_id !== auth()->user()->tenant_id, 403);
        $tenantId = auth()->user()->tenant_id;
        $categories = Category::where('tenant_id', $tenantId)->get();
        $units = Unit::where('tenant_id', $tenantId)->get();
        return view('products.edit', compact('product', 'categories', 'units'));
    }

    public function update(Request $request, Product $product)
    {
        abort_if($product->tenant_id !== auth()->user()->tenant_id, 403);

        $data = $request->except('image');
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $product->update($data);
        return redirect()->route('products.index')->with('success', 'تم تحديث المنتج.');
    }

    public function destroy(Product $product)
    {
        abort_if($product->tenant_id !== auth()->user()->tenant_id, 403);
        $product->delete();
        return redirect()->route('products.index')->with('success', 'تم حذف المنتج.');
    }

    public function adjustStock(Request $request, Product $product)
    {
        abort_if($product->tenant_id !== auth()->user()->tenant_id, 403);
        $request->validate(['quantity' => 'required|integer', 'type' => 'required|in:in,out,adjustment', 'notes' => 'nullable|string']);
        $this->stockService->move($product, $request->type, $request->quantity, 'manual', null, $request->notes);
        return back()->with('success', 'تم تعديل المخزون.');
    }
}
