<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PosController extends Controller
{
    public function __construct(private StockService $stockService) {}

    public function index()
    {
        $tenantId      = auth()->user()->tenant_id;
        $userWarehouse = auth()->user()->getDefaultWarehouse();
        $userWarehouses = view()->shared('userWarehouses') ?? collect();

        // المنتجات النشطة مع كمياتها في المخزن
        $products = Product::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->get();

        // مخزون جميع المخازن المتاحة للمستخدم
        $warehouseStocks = [];
        foreach ($userWarehouses as $wh) {
            $warehouseStocks[$wh->id] = WarehouseStock::where('warehouse_id', $wh->id)
                ->pluck('quantity', 'product_id')
                ->map(fn($q) => (float) $q)
                ->toArray();
        }

        // العملاء للاختيار الاختياري
        $customers = Customer::where('tenant_id', $tenantId)->orderBy('name')->get();

        // طرق الدفع
        $paymentMethods = PaymentMethod::where('tenant_id', $tenantId)
            ->where('is_active', true)->get();

        $defaultCurrency = auth()->user()->tenant->getSetting('default_currency', 'SDG');

        return view('pos.index', compact(
            'products', 'customers', 'paymentMethods',
            'userWarehouses', 'userWarehouse', 'warehouseStocks', 'defaultCurrency'
        ));
    }

    public function checkout(Request $request)
    {
        $request->validate([
            'items'               => 'required|array|min:1',
            'items.*.product_id'  => 'nullable|exists:products,id',
            'items.*.description' => 'required|string',
            'items.*.quantity'    => 'required|numeric|min:0.001',
            'items.*.unit_price'  => 'required|numeric|min:0',
            'payment_amount'      => 'required|numeric|min:0.01',
            'payment_method'      => 'required|string',
            'warehouse_id'        => 'nullable|exists:warehouses,id',
        ]);

        $tenantId = auth()->user()->tenant_id;

        // التحقق من صحة طريقة الدفع
        $validMethod = \App\Models\PaymentMethod::where('tenant_id', $tenantId)
            ->where('code', $request->payment_method)
            ->where('is_active', true)
            ->exists();

        if (!$validMethod) {
            return response()->json(['success' => false, 'message' => 'طريقة الدفع غير صالحة.'], 422);
        }

        $warehouseId = $request->warehouse_id ?? auth()->user()->getDefaultWarehouse()?->id;
        $invoiceRef  = null;

        DB::transaction(function () use ($request, $tenantId, $warehouseId, &$invoiceRef) {
            // العميل: إذا اختار عميل موجود أو كتب اسماً جديداً
            $customerId = $request->customer_id;
            if (!$customerId && $request->filled('customer_name')) {
                // إنشاء عميل مؤقت
                $customer = Customer::firstOrCreate(
                    ['tenant_id' => $tenantId, 'name' => trim($request->customer_name)],
                    ['phone' => $request->customer_phone ?? null]
                );
                $customerId = $customer->id;
            }
            if (!$customerId) {
                // عميل عام افتراضي
                $customer = Customer::firstOrCreate(
                    ['tenant_id' => $tenantId, 'name' => 'عميل نقدي'],
                    []
                );
                $customerId = $customer->id;
            }

            $invoice = Invoice::create([
                'tenant_id'        => $tenantId,
                'invoice_number'   => Invoice::generateNumber($tenantId),
                'customer_id'      => $customerId,
                'warehouse_id'     => $warehouseId,
                'invoice_date'     => now()->toDateString(),
                'status'           => 'sent',
                'discount_amount'  => $request->discount_amount ?? 0,
                'discount_type'    => $request->discount_type ?? 'fixed',
                'currency'         => auth()->user()->tenant->getSetting('default_currency', 'SDG'),
                'exchange_rate'    => 1,
                'language'         => 'ar',
                'notes'            => $request->notes,
                'public_token'     => \Illuminate\Support\Str::uuid(),
                'created_by'       => auth()->id(),
            ]);

            foreach ($request->items as $item) {
                $lineTotal = $item['quantity'] * $item['unit_price'];
                $invoice->items()->create([
                    'product_id'  => $item['product_id'] ?? null,
                    'description' => $item['description'],
                    'quantity'    => $item['quantity'],
                    'unit_price'  => $item['unit_price'],
                    'tax_rate'    => $item['tax_rate'] ?? 0,
                    'discount'    => 0,
                    'total'       => $lineTotal,
                ]);
            }

            $invoice->load('items');
            $invoice->calculateTotals();

            // خصم المخزون
            $this->stockService->deductForInvoice($invoice, $warehouseId);

            // تسجيل الدفع
            $payAmount = min((float)$request->payment_amount, $invoice->total_amount);
            Payment::create([
                'tenant_id'      => $tenantId,
                'invoice_id'     => $invoice->id,
                'payment_date'   => now()->toDateString(),
                'amount'         => $payAmount,
                'payment_method' => $request->payment_method,
                'notes'          => 'بيع مباشر - POS',
            ]);

            $invoice->update(['paid_amount' => $payAmount]);

            if ($payAmount >= $invoice->total_amount - 0.001) {
                $invoice->update(['status' => 'paid']);
            } else {
                $invoice->update(['status' => 'partially_paid']);
            }

            $invoiceRef = $invoice->fresh(['customer', 'items', 'payments', 'tenant']);
        });

        // إرجاع JSON للـ POS interface
        return response()->json([
            'success'       => true,
            'invoice_id'    => $invoiceRef->id,
            'invoice_number'=> $invoiceRef->invoice_number,
            'total'         => $invoiceRef->total_amount,
            'paid'          => $invoiceRef->paid_amount,
            'change'        => max(0, (float)$request->payment_amount - $invoiceRef->total_amount),
            'pdf_url'       => route('invoices.pdf', $invoiceRef),
            'receipt_url'   => route('pos.receipt', $invoiceRef),
        ]);
    }

    public function receipt(Invoice $invoice)
    {
        abort_if($invoice->tenant_id !== auth()->user()->tenant_id, 403);
        $invoice->load(['customer', 'items', 'payments', 'tenant']);
        return view('pos.receipt', compact('invoice'));
    }
}
