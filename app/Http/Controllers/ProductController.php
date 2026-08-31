<?php

namespace App\Http\Controllers;

use App\Exports\ProductsTemplateExport;
use App\Imports\ProductsImport;
use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

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
        $tenantId         = auth()->user()->tenant_id;
        $categories       = Category::where('tenant_id', $tenantId)->get();
        $units            = Unit::where('tenant_id', $tenantId)->get();
        $warehouses       = view()->shared('userWarehouses') ?? collect();
        $defaultWarehouse = auth()->user()->getDefaultWarehouse();

        return view('products.create', compact('categories', 'units', 'warehouses', 'defaultWarehouse'));
    }

    public function store(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        $request->validate([
            'name'        => 'required|string|max:255',
            'unit_price'  => 'required|numeric|min:0',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'warehouse_id' => ['nullable', \Illuminate\Validation\Rule::exists('warehouses', 'id')->where('tenant_id', $tenantId)],
        ]);

        $data = $request->except(['image', 'warehouse_id']);
        $data['tenant_id'] = $tenantId;

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $product = Product::create($data);

        // تحديد المخزن
        $warehouseId = $request->warehouse_id
            ?? \App\Models\Warehouse::getDefault($tenantId)?->id;

        if ($product->stock_quantity > 0 && $warehouseId) {
            // تسجيل الكمية الافتتاحية في warehouse_stocks مباشرة (بدون move لتجنب الازدواجية)
            \App\Models\WarehouseStock::updateOrCreate(
                ['warehouse_id' => $warehouseId, 'product_id' => $product->id],
                ['quantity' => $product->stock_quantity]
            );

            // تسجيل حركة المخزون للمراجعة
            \App\Models\StockMovement::create([
                'tenant_id'       => $tenantId,
                'product_id'      => $product->id,
                'warehouse_id'    => $warehouseId,
                'type'            => 'in',
                'quantity'        => $product->stock_quantity,
                'quantity_before' => 0,
                'quantity_after'  => $product->stock_quantity,
                'reference_type'  => 'manual',
                'reference_id'    => null,
                'notes'           => 'رصيد افتتاحي',
                'created_by'      => auth()->id(),
            ]);
        } elseif ($warehouseId) {
            // إنشاء سجل بكمية 0 حتى يظهر المنتج في المخزن
            \App\Models\WarehouseStock::firstOrCreate(
                ['warehouse_id' => $warehouseId, 'product_id' => $product->id],
                ['quantity' => 0]
            );
        }

        if (request()->expectsJson()) {
            return response()->json($product);
        }

        return redirect()->route('products.index')->with('success', 'تم إضافة المنتج.');
    }

    public function show(Product $product)
    {
        abort_if($product->tenant_id !== auth()->user()->tenant_id, 403);
        $product->load(['category', 'stockMovements' => fn($q) => $q->latest()->take(20)]);

        // كميات المنتج في كل مخزن
        $warehouseStocks = \App\Models\WarehouseStock::where('product_id', $product->id)
            ->with('warehouse')
            ->get();

        return view('products.show', compact('product', 'warehouseStocks'));
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

        $request->validate([
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        $data = $request->except('image');
        if ($request->hasFile('image')) {
            // حذف الصورة القديمة قبل رفع الجديدة (OPS-04)
            if ($product->image) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($product->image);
            }
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
        $request->validate([
            'quantity'     => 'required|numeric',
            'type'         => 'required|in:in,out,adjustment',
            'notes'        => 'nullable|string',
            'warehouse_id' => 'nullable|exists:warehouses,id',
        ]);

        // تحديد المخزن: المحدد يدوياً أو مخزن المستخدم الافتراضي
        $warehouseId = $request->warehouse_id
            ?? auth()->user()->getDefaultWarehouse()?->id;

        $this->stockService->move(
            $product,
            $request->type,
            abs((float) $request->quantity),
            'manual',
            null,
            $request->notes,
            $warehouseId
        );

        return back()->with('success', 'تم تعديل المخزون.');
    }

    public function importForm()
    {
        $warehouses = \App\Models\Warehouse::where('tenant_id', auth()->user()->tenant_id)
            ->where('is_active', true)->get();
        return view('products.import', compact('warehouses'));
    }

    public function import(Request $request)
    {
        $request->validate([
            'file'         => 'required|file|mimes:xlsx,xls,csv|max:5120',
            'warehouse_id' => 'nullable|exists:warehouses,id',
        ]);

        $tenantId    = auth()->user()->tenant_id;
        $warehouseId = $request->warehouse_id;

        Excel::import(new ProductsImport($tenantId, $warehouseId), $request->file('file'));

        return redirect()->route('products.index')->with('success', 'تم استيراد المنتجات بنجاح.');
    }

    public function downloadTemplate()
    {
        return Excel::download(new ProductsTemplateExport(), 'products_template.xlsx');
    }
}
