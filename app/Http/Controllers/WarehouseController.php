<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use App\Models\Product;
use App\Models\WarehouseStock;
use App\Services\StockService;
use Illuminate\Http\Request;

class WarehouseController extends Controller
{
    public function __construct(private StockService $stockService) {}

    public function index()
    {
        $tenantId = auth()->user()->tenant_id;
        $user     = auth()->user();

        // Admin يرى كل المخازن، غيره يرى مخازنه فقط
        if ($user->isAdmin()) {
            $warehouses = Warehouse::where('tenant_id', $tenantId)
                ->withCount('stocks')
                ->get();
        } else {
            $userWarehouseIds = $user->warehouses()->pluck('warehouses.id')->toArray();

            if (empty($userWarehouseIds)) {
                // لم يُحدد مخازن = يرى كل المخازن النشطة
                $warehouses = Warehouse::where('tenant_id', $tenantId)
                    ->where('is_active', true)
                    ->withCount('stocks')
                    ->get();
            } else {
                $warehouses = Warehouse::where('tenant_id', $tenantId)
                    ->whereIn('id', $userWarehouseIds)
                    ->withCount('stocks')
                    ->get();
            }
        }

        return view('warehouses.index', compact('warehouses'));
    }

    public function store(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;
        $request->validate([
            'name'     => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
        ]);

        $isFirst = Warehouse::where('tenant_id', $tenantId)->count() === 0;
        $makeDefault = $isFirst || $request->boolean('is_default');

        if ($makeDefault) {
            Warehouse::where('tenant_id', $tenantId)->update(['is_default' => false]);
        }

        Warehouse::create([
            'tenant_id'  => $tenantId,
            'name'       => $request->name,
            'location'   => $request->location,
            'notes'      => $request->notes,
            'is_default' => $makeDefault,
            'is_active'  => true,
        ]);

        return back()->with('success', 'تم إضافة المخزن.');
    }

    public function update(Request $request, Warehouse $warehouse)
    {
        abort_if($warehouse->tenant_id !== auth()->user()->tenant_id, 403);
        $request->validate(['name' => 'required|string|max:255']);
        $warehouse->update($request->only('name', 'location', 'notes'));
        return back()->with('success', 'تم تحديث المخزن.');
    }

    public function setDefault(Warehouse $warehouse)
    {
        abort_if($warehouse->tenant_id !== auth()->user()->tenant_id, 403);
        Warehouse::where('tenant_id', $warehouse->tenant_id)->update(['is_default' => false]);
        $warehouse->update(['is_default' => true]);
        return back()->with('success', 'تم تعيين المخزن الافتراضي.');
    }

    public function toggle(Warehouse $warehouse)
    {
        abort_if($warehouse->tenant_id !== auth()->user()->tenant_id, 403);
        abort_if($warehouse->is_default && $warehouse->is_active, 403, 'لا يمكن تعطيل المخزن الافتراضي.');
        $warehouse->update(['is_active' => !$warehouse->is_active]);
        return back()->with('success', 'تم تحديث حالة المخزن.');
    }

    public function destroy(Warehouse $warehouse)
    {
        abort_if($warehouse->tenant_id !== auth()->user()->tenant_id, 403);
        abort_if($warehouse->is_default, 403, 'لا يمكن حذف المخزن الافتراضي.');
        abort_if($warehouse->stocks()->where('quantity', '>', 0)->exists(), 403, 'لا يمكن حذف مخزن يحتوي على مخزون.');
        $warehouse->delete();
        return back()->with('success', 'تم حذف المخزن.');
    }

    public function show(Warehouse $warehouse)
    {
        abort_if($warehouse->tenant_id !== auth()->user()->tenant_id, 403);

        $search = request('search');

        $stocks = WarehouseStock::where('warehouse_id', $warehouse->id)
            ->with('product.category')
            ->when($search, fn($q) => $q->whereHas('product', fn($p) =>
                $p->where('name', 'like', "%$search%")
                  ->orWhere('sku', 'like', "%$search%")
            ))
            ->where('quantity', '>=', 0)
            ->orderByDesc('quantity')
            ->paginate(20);

        return view('warehouses.show', compact('warehouse', 'stocks', 'search'));
    }

    public function transfer(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;
        $request->validate([
            'from_warehouse_id' => 'required|exists:warehouses,id',
            'to_warehouse_id'   => 'required|exists:warehouses,id|different:from_warehouse_id',
            'product_id'        => 'required|exists:products,id',
            'quantity'          => 'required|numeric|min:0.001',
        ]);

        $from = Warehouse::findOrFail($request->from_warehouse_id);
        $to   = Warehouse::findOrFail($request->to_warehouse_id);
        abort_if($from->tenant_id !== $tenantId || $to->tenant_id !== $tenantId, 403);

        $product = Product::findOrFail($request->product_id);
        abort_if($product->tenant_id !== $tenantId, 403);

        // BUG-09 Fix: نحذف التحقق من الكمية هنا (خارج transaction)
        // لأنه يُسبب race condition — طلب آخر قد يُقلل الكمية بين هذا التحقق والـ transaction
        // التحقق الحقيقي يحدث داخل StockService::transfer() مع lockForUpdate
        try {
            $this->stockService->transfer($product, $from->id, $to->id, $request->quantity, $request->notes);
        } catch (\RuntimeException $e) {
            // نحوّل RuntimeException من الـ Service إلى رسالة validation واضحة للمستخدم
            return back()->withErrors(['quantity' => $e->getMessage()]);
        }

        return back()->with('success', 'تم نقل المخزون بنجاح.');
    }
}
