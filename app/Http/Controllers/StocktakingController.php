<?php
namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use App\Models\StocktakingSession;
use App\Models\StocktakingItem;
use App\Services\StockService;
use Illuminate\Http\Request;

class StocktakingController extends Controller
{
    public function __construct(private StockService $stockService) {}

    public function index()
    {
        $sessions = StocktakingSession::where('tenant_id', auth()->user()->tenant_id)
            ->with('warehouse')
            ->withCount('items')
            ->latest()
            ->paginate(15);
        return view('stocktaking.index', compact('sessions'));
    }

    public function create()
    {
        $tenantId  = auth()->user()->tenant_id;
        // مخازن المستخدم فقط
        $warehouses       = view()->shared('userWarehouses') ?? collect();
        $defaultWarehouse = auth()->user()->getDefaultWarehouse();

        return view('stocktaking.create', compact('warehouses', 'defaultWarehouse'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'         => 'required|string|max:255',
            'date'         => 'required|date',
            'warehouse_id' => 'required|exists:warehouses,id',
        ]);

        $tenantId    = auth()->user()->tenant_id;
        $warehouseId = $request->warehouse_id;

        // التحقق أن المخزن يخص هذا الـ tenant
        $warehouse = Warehouse::where('id', $warehouseId)
            ->where('tenant_id', $tenantId)
            ->firstOrFail();

        $session = StocktakingSession::create([
            'tenant_id'    => $tenantId,
            'warehouse_id' => $warehouseId,
            'name'         => $request->name,
            'date'         => $request->date,
            'notes'        => $request->notes,
            'status'       => 'draft',
            'created_by'   => auth()->id(),
        ]);

        // جلب المنتجات الموجودة في هذا المخزن تحديداً
        $warehouseStocks = WarehouseStock::where('warehouse_id', $warehouseId)
            ->whereHas('product', fn($q) => $q->where('tenant_id', $tenantId)->where('status', 'active'))
            ->with('product')
            ->get();

        if ($warehouseStocks->isEmpty()) {
            // BUG-06 Fix: لا نضيف كل المنتجات عند غياب المخزون
            // بدلاً من ذلك نضيف فقط المنتجات التي لها سجل في هذا المستودع (حتى لو صفر)
            // أو نبدأ بجلسة فارغة يملؤها المستخدم يدوياً
            $products = Product::where('tenant_id', $tenantId)
                ->where('status', 'active')
                ->whereHas('warehouseStocks', fn($q) => $q->where('warehouse_id', $warehouseId))
                ->get();

            foreach ($products as $product) {
                $qty = (float) WarehouseStock::where('warehouse_id', $warehouseId)
                    ->where('product_id', $product->id)
                    ->value('quantity') ?? 0;

                StocktakingItem::create([
                    'session_id' => $session->id,
                    'product_id' => $product->id,
                    'system_qty' => $qty,
                    'actual_qty' => $qty,
                    'difference' => 0,
                ]);
            }
        } else {
            foreach ($warehouseStocks as $stock) {
                StocktakingItem::create([
                    'session_id' => $session->id,
                    'product_id' => $stock->product_id,
                    'system_qty' => $stock->quantity,
                    'actual_qty' => $stock->quantity,
                    'difference' => 0,
                ]);
            }
        }

        return redirect()->route('stocktaking.show', $session)
            ->with('success', 'تم إنشاء جلسة الجرد للمخزن: ' . $warehouse->name);
    }

    public function show(StocktakingSession $stocktaking)
    {
        abort_if($stocktaking->tenant_id !== auth()->user()->tenant_id, 403);
        $stocktaking->load('items.product', 'warehouse');
        return view('stocktaking.show', compact('stocktaking'));
    }

    public function update(Request $request, StocktakingSession $stocktaking)
    {
        abort_if($stocktaking->tenant_id !== auth()->user()->tenant_id, 403);
        abort_if($stocktaking->status === 'confirmed', 403, 'لا يمكن تعديل جلسة مؤكدة.');

        foreach ($request->items as $itemId => $data) {
            $item = StocktakingItem::find($itemId);
            if ($item && $item->session_id === $stocktaking->id) {
                $actual = (float) $data['actual_qty'];
                $item->update([
                    'actual_qty' => $actual,
                    'difference' => $actual - $item->system_qty,
                ]);
            }
        }

        return back()->with('success', 'تم حفظ الكميات.');
    }

    public function confirm(StocktakingSession $stocktaking)
    {
        abort_if($stocktaking->tenant_id !== auth()->user()->tenant_id, 403);
        abort_if($stocktaking->status === 'confirmed', 403);

        $stocktaking->load('items.product', 'warehouse');
        $warehouseId = $stocktaking->warehouse_id;

        foreach ($stocktaking->items as $item) {
            if ($item->difference != 0 && $item->product) {
                // BUG-06 Fix: تخطّي العناصر التي system_qty=0 و actual_qty=0
                // هذه منتجات لا علاقة لها بهذا المستودع فعلياً
                if ((float) $item->system_qty <= 0 && (float) $item->actual_qty <= 0) {
                    continue;
                }

                // تسوية مخزون المخزن المحدد فقط
                if ($warehouseId) {
                    $this->stockService->move(
                        $item->product,
                        'adjustment',
                        (float) $item->actual_qty,
                        'stocktaking',
                        $stocktaking->id,
                        'تسوية جرد: ' . $stocktaking->name,
                        $warehouseId
                    );
                } else {
                    $this->stockService->move(
                        $item->product,
                        'adjustment',
                        (float) $item->actual_qty,
                        'stocktaking',
                        $stocktaking->id,
                        'تسوية جرد: ' . $stocktaking->name
                    );
                }
            }
        }

        $stocktaking->update(['status' => 'confirmed']);
        return redirect()->route('stocktaking.index')
            ->with('success', 'تم اعتماد الجرد وتطبيق التسويات على المخزن.');
    }
}
