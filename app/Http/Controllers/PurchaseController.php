<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\Warehouse;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PurchaseController extends Controller
{
    public function __construct(private StockService $stockService) {}

    public function index(Request $request)
    {
        $purchases = Purchase::where('tenant_id', auth()->user()->tenant_id)
            ->with(['supplier', 'warehouse'])
            ->when($request->search, fn($q) => $q->where('supplier_name', 'like', "%{$request->search}%")->orWhere('reference', 'like', "%{$request->search}%"))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->latest()->paginate(15);

        return view('purchases.index', compact('purchases'));
    }

    public function create()
    {
        $tenantId = auth()->user()->tenant_id;
        $products  = Product::where('tenant_id', $tenantId)->where('status', 'active')->get();
        $suppliers = \App\Models\Supplier::where('tenant_id', $tenantId)->get();

        // المخازن المتاحة للمستخدم (محقونة من TenantMiddleware)
        $warehouses       = view()->shared('userWarehouses') ?? collect();
        $defaultWarehouse = auth()->user()->getDefaultWarehouse();
        $defaultCurrency  = auth()->user()->tenant->getSetting('default_currency', 'SDG');

        return view('purchases.create', compact('products', 'suppliers', 'warehouses', 'defaultWarehouse', 'defaultCurrency'));
    }

    public function store(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        $request->validate([
            'purchase_date'  => 'required|date',
            'warehouse_id'   => ['nullable', Rule::exists('warehouses', 'id')->where('tenant_id', $tenantId)],
            'supplier_name'  => 'nullable|string|max:255',
            'items'          => 'required|array|min:1',
            'items.*.product_id'   => ['nullable', Rule::exists('products', 'id')->where('tenant_id', $tenantId)],
            'items.*.product_name' => 'required|string',
            'items.*.quantity'     => 'required|numeric|min:0.001',
            'items.*.unit_cost'    => 'required|numeric|min:0',
        ]);

        $subtotal  = collect($request->items)->sum(fn($i) => $i['quantity'] * $i['unit_cost']);
        $taxAmount = $request->tax_amount ?? 0;

        // تحديد المخزن
        $warehouseId = $request->warehouse_id
            ?? auth()->user()->getDefaultWarehouse()?->id;

        // استعلام واحد للمورد
        $supplier = $request->supplier_id ? \App\Models\Supplier::find($request->supplier_id) : null;

        $purchase = null;
        \Illuminate\Support\Facades\DB::transaction(function () use (
            $request, $tenantId, $subtotal, $taxAmount, $warehouseId, $supplier, &$purchase
        ) {
            $purchase = Purchase::create([
                'tenant_id'      => $tenantId,
                'supplier_id'    => $request->supplier_id ?? null,
                'warehouse_id'   => $warehouseId,
                'reference'      => $request->reference ?? ('PO-' . date('YmdHis')),
                'supplier_name'  => $request->supplier_name ?? $supplier?->name,
                'supplier_phone' => $request->supplier_phone ?? $supplier?->phone,
                'purchase_date'  => $request->purchase_date,
                'status'         => $request->status ?? 'pending',
                'subtotal'       => $subtotal,
                'tax_amount'     => $taxAmount,
                'total'          => $subtotal + $taxAmount,
                'paid_amount'    => 0,
                'payment_status' => 'unpaid',
                'currency'       => auth()->user()->tenant->getSetting('default_currency', 'SDG'),
                'exchange_rate'  => 1,
                'notes'          => $request->notes,
                'created_by'     => auth()->id(),
            ]);

            foreach ($request->items as $item) {
                $purchase->items()->create([
                    'product_id'   => $item['product_id'] ?? null,
                    'product_name' => $item['product_name'],
                    'quantity'     => $item['quantity'],
                    'unit_cost'    => $item['unit_cost'],
                    'total'        => $item['quantity'] * $item['unit_cost'],
                ]);
            }

            if ($purchase->status === 'received') {
                $this->addToStock($purchase);
            }
        });

        return redirect()->route('purchases.index')->with('success', 'تم إنشاء أمر الشراء.');
    }

    public function show(Purchase $purchase)
    {
        abort_if($purchase->tenant_id !== auth()->user()->tenant_id, 403);
        $purchase->load('items.product', 'returns.items', 'payments', 'warehouse', 'supplier', 'tenant');

        $tenantId = auth()->user()->tenant_id;
        $paymentMethods = \App\Models\PaymentMethod::where('tenant_id', $tenantId)->where('is_active', true)->get();

        return view('purchases.show', compact('purchase', 'paymentMethods'));
    }

    public function downloadPdf(Purchase $purchase)
    {
        abort_if($purchase->tenant_id !== auth()->user()->tenant_id, 403);
        $purchase->load('items.product', 'returns.items', 'warehouse', 'supplier', 'tenant');

        // جلب لوقو الشركة كـ base64
        $logo = '';
        if ($purchase->tenant->logo) {
            $abs = storage_path('app/public/' . ltrim($purchase->tenant->logo, '/'));
            if (!file_exists($abs)) {
                $abs = public_path('storage/' . ltrim($purchase->tenant->logo, '/'));
            }
            if (file_exists($abs)) {
                $info = getimagesize($abs);
                $mime = $info ? $info['mime'] : 'image/png';
                $logo = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($abs));
            }
        }

        $html = view('purchases.pdf', compact('purchase', 'logo'))->render();

        $fontDir = base_path('vendor/mpdf/mpdf/ttfonts');
        $mpdf = new \Mpdf\Mpdf([
            'mode'                   => 'utf-8',
            'format'                 => 'A4',
            'tempDir'                => storage_path('app/mpdf_tmp'),
            'allow_output_buffering' => true,
            'fontDir'                => [$fontDir],
            'fontdata'               => [
                'xbriyaz' => [
                    'R'          => 'XB Riyaz.ttf',
                    'B'          => 'XB RiyazBd.ttf',
                    'useOTL'     => 0xFF,
                    'useKashida' => 75,
                ],
            ],
            'default_font' => 'xbriyaz',
        ]);

        preg_match('/<style[^>]*>(.*?)<\/style>/si', $html, $cssMatch);
        $css  = $cssMatch[1] ?? '';
        $body = preg_replace('/<style[^>]*>.*?<\/style>/si', '', $html);
        if ($css) $mpdf->WriteHTML($css, \Mpdf\HTMLParserMode::HEADER_CSS);
        $mpdf->WriteHTML($body, \Mpdf\HTMLParserMode::HTML_BODY);

        $content  = $mpdf->Output('', 'S');
        $filename = 'purchase-' . $purchase->reference . '.pdf';

        return response($content, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function whatsapp(Purchase $purchase)
    {
        abort_if($purchase->tenant_id !== auth()->user()->tenant_id, 403);
        $purchase->load('supplier', 'items', 'tenant');

        $phone = preg_replace('/[^0-9+]/', '', $purchase->supplier?->phone ?? $purchase->supplier_phone ?? '');

        $message  = "أمر الشراء رقم: {$purchase->reference}\n";
        $message .= "التاريخ: {$purchase->purchase_date->format('Y-m-d')}\n";
        $message .= "عدد الأصناف: {$purchase->items->count()}\n";
        $message .= "الإجمالي: " . number_format($purchase->total, 2) . " {$purchase->currency}\n";
        if ($purchase->notes) {
            $message .= "ملاحظات: {$purchase->notes}\n";
        }
        $message .= "\n— " . ($purchase->tenant->company_name ?? 'فاتورتك');

        $url = 'https://wa.me/' . $phone . '?text=' . urlencode($message);
        return redirect($url);
    }

    public function updateStatus(Request $request, Purchase $purchase)
    {
        abort_if($purchase->tenant_id !== auth()->user()->tenant_id, 403);
        $request->validate(['status' => 'required|in:pending,received,cancelled']);

        $oldStatus = $purchase->status;
        $purchase->update(['status' => $request->status]);

        if ($request->status === 'received' && $oldStatus !== 'received') {
            try {
                $this->addToStock($purchase);
            } catch (\RuntimeException $e) {
                // تراجع عن تغيير الحالة إذا فشل المخزون
                $purchase->update(['status' => $oldStatus]);
                return back()->with('error', 'فشل تحديث المخزون: ' . $e->getMessage());
            }
        }

        return back()->with('success', 'تم تحديث الحالة.');
    }

    public function destroy(Purchase $purchase)
    {
        abort_if($purchase->tenant_id !== auth()->user()->tenant_id, 403);
        abort_if($purchase->status === 'received', 403, 'لا يمكن حذف أمر شراء مستلم.');
        $purchase->delete();
        return redirect()->route('purchases.index')->with('success', 'تم حذف أمر الشراء.');
    }

    private function addToStock(Purchase $purchase): void
    {
        $purchase->load('items');
        $warehouseId = $purchase->warehouse_id
            ?? Warehouse::getDefault($purchase->tenant_id)?->id;

        // PERF-01 Fix: نقرأ إعداد التكلفة مرة واحدة خارج الحلقة
        $method = auth()->user()->tenant->getSetting('cost_price_method', 'wac');

        // PERF-01 Fix: نجلب كل المنتجات المطلوبة باستعلام واحد
        $productIds = $purchase->items->pluck('product_id')->filter()->unique()->values();
        $products   = Product::whereIn('id', $productIds)->get()->keyBy('id');

        foreach ($purchase->items as $item) {
            if ($item->product_id && isset($products[$item->product_id])) {
                $product = $products[$item->product_id];
                $newCost = (float) $item->unit_cost;

                if ($method === 'latest') {
                    $avgCost = $newCost;
                } else {
                    // Weighted Average Cost
                    $oldQty  = (float) $product->stock_quantity;
                    $oldCost = (float) $product->cost_price;
                    $newQty  = (float) $item->quantity;
                    $total   = $oldQty + $newQty;
                    $avgCost = $total > 0
                        ? (($oldQty * $oldCost) + ($newQty * $newCost)) / $total
                        : $newCost;
                }

                $this->stockService->move(
                    $product, 'in', (float) $item->quantity,
                    'purchase', $purchase->id,
                    'استلام مشتريات: ' . $purchase->reference,
                    $warehouseId
                );
                $product->update(['cost_price' => round($avgCost, 4)]);
            }
        }
    }
}
