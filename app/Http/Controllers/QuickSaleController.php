<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Setting;
use App\Models\WarehouseStock;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class QuickSaleController extends Controller
{
    public function __construct(private StockService $stockService) {}

    public function index()
    {
        $tenantId       = auth()->user()->tenant_id;
        $tenant         = auth()->user()->tenant;
        $products       = Product::where('tenant_id', $tenantId)->where('status', 'active')->get();
        $customers      = Customer::where('tenant_id', $tenantId)->get();
        $paymentMethods = PaymentMethod::where('tenant_id', $tenantId)->where('is_active', true)->get();
        $defaultCurrency = $tenant->getSetting('default_currency', 'SDG');

        $userWarehouses  = view()->shared('userWarehouses') ?? collect();
        $userWarehouse   = auth()->user()->getDefaultWarehouse();

        $warehouseStocks = [];
        foreach ($userWarehouses as $wh) {
            $stocks = WarehouseStock::where('warehouse_id', $wh->id)
                ->pluck('quantity', 'product_id')
                ->toArray();
            $warehouseStocks[$wh->id] = array_map('floatval', $stocks);
        }

        // العميل النقدي الافتراضي
        $cashCustomerId = (int) $tenant->getSetting('quick_sale_customer_id', 0);

        return view('quick-sale.index', compact(
            'products', 'customers', 'paymentMethods', 'defaultCurrency',
            'userWarehouses', 'userWarehouse', 'warehouseStocks', 'cashCustomerId'
        ));
    }

    public function store(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        $request->validate([
            'items'                => 'required|array|min:1',
            'items.*.product_id'   => ['nullable', Rule::exists('products', 'id')->where('tenant_id', $tenantId)],
            'items.*.description'  => 'required|string',
            'items.*.quantity'     => 'required|numeric|min:0.001',
            'items.*.unit_price'   => 'required|numeric|min:0',
            'items.*.tax_rate'     => 'nullable|numeric|min:0|max:100',
            'payment_method'       => ['required', Rule::exists('payment_methods', 'code')->where('tenant_id', $tenantId)],
            'customer_id'          => ['nullable', Rule::exists('customers', 'id')->where('tenant_id', $tenantId)],
            'walk_in_name'         => 'nullable|string|max:255',
            'warehouse_id'         => ['nullable', Rule::exists('warehouses', 'id')->where('tenant_id', $tenantId)],
            'discount_amount'      => 'nullable|numeric|min:0',
            'discount_type'        => 'nullable|in:fixed,percent',
            'paid_amount'          => 'nullable|numeric|min:0',
            'notes'                => 'nullable|string|max:1000',
        ]);

        $tenant     = auth()->user()->tenant;
        $invoiceRef = null;

        // تحديد العميل: المحدد أو النقدي الافتراضي
        $customerId = $request->customer_id
            ?? (int) $tenant->getSetting('quick_sale_customer_id', 0)
            ?: null;

        // إذا لم يوجد عميل نقدي، أنشئه الآن
        if (!$customerId) {
            $cashCustomer = Customer::create([
                'tenant_id' => $tenantId,
                'name'      => 'عميل نقدي',
                'notes'     => 'عميل افتراضي للمبيعات المباشرة',
            ]);
            Setting::updateOrCreate(
                ['tenant_id' => $tenantId, 'key' => 'quick_sale_customer_id'],
                ['value' => $cashCustomer->id]
            );
            $customerId = $cashCustomer->id;
        }

        // بناء ملاحظة تجمع اسم الزبون + ملاحظات المستخدم
        $walkInName = trim($request->walk_in_name ?? '');
        $noteParts  = [];
        if ($walkInName)          $noteParts[] = 'اسم الزبون: ' . $walkInName;
        if ($request->filled('notes')) $noteParts[] = $request->notes;
        $finalNotes = implode(' | ', $noteParts) ?: null;

        $warehouseId = $request->warehouse_id
            ?? auth()->user()->getDefaultWarehouse()?->id;

        $paymentMethod = $request->payment_method;
        $isCredit      = $paymentMethod === 'credit';

        try {
            DB::transaction(function () use ($request, $tenantId, $tenant, $customerId, $warehouseId, $paymentMethod, $isCredit, $finalNotes, &$invoiceRef) {
                $invoice = Invoice::create([
                    'tenant_id'        => $tenantId,
                    'invoice_number'   => Invoice::generateNumber($tenantId),
                    'customer_id'      => $customerId,
                    'template_id'      => null,
                    'warehouse_id'     => $warehouseId,
                    'invoice_date'     => now()->toDateString(),
                    'due_date'         => now()->toDateString(),
                    'status'           => $isCredit ? 'sent' : 'paid',
                    'discount_amount'  => $request->discount_amount ?? 0,
                    'discount_type'    => $request->discount_type ?? 'fixed',
                    'currency'         => $tenant->getSetting('default_currency', 'SDG'),
                    'exchange_rate'    => 1,
                    'language'         => 'ar',
                    'notes'            => $finalNotes,
                    'terms_conditions' => null,
                    'public_token'     => Str::uuid(),
                    'created_by'       => auth()->id(),
                ]);

                foreach ($request->items as $item) {
                    $invoice->items()->create([
                        'product_id'  => $item['product_id'] ?? null,
                        'description' => $item['description'],
                        'quantity'    => $item['quantity'],
                        'unit_price'  => $item['unit_price'],
                        'tax_rate'    => $item['tax_rate'] ?? 0,
                        'discount'    => 0,
                        'total'       => $item['quantity'] * $item['unit_price'],
                    ]);
                }

                $invoice->load('items');
                $invoice->calculateTotals();

                // خصم المخزون فوراً
                $this->stockService->deductForInvoice($invoice, $warehouseId);

                // تسجيل الدفعة
                $paidAmount = $isCredit ? 0 : min(
                    (float) ($request->paid_amount ?? $invoice->total_amount),
                    $invoice->total_amount
                );

                if ($paidAmount > 0) {
                    Payment::create([
                        'tenant_id'      => $tenantId,
                        'invoice_id'     => $invoice->id,
                        'payment_date'   => now()->toDateString(),
                        'amount'         => $paidAmount,
                        'payment_method' => $paymentMethod,
                        'notes'          => 'بيع مباشر',
                    ]);
                    $invoice->update(['paid_amount' => $paidAmount]);

                    // تحديث الحالة بدقة بعد تسجيل الدفعة
                    $invoice->refresh();
                    if ($invoice->paid_amount >= $invoice->total_amount - 0.001) {
                        $invoice->update(['status' => 'paid']);
                    } elseif ($invoice->paid_amount > 0) {
                        $invoice->update(['status' => 'partially_paid']);
                    }
                }

                $invoiceRef = $invoice;
            });
        } catch (\RuntimeException $e) {
            return back()->withErrors(['items' => $e->getMessage()])->withInput();
        }

        return redirect()->route('quick-sale.receipt', $invoiceRef)
            ->with('success', 'تم إتمام البيع بنجاح.');
    }

    public function receipt(Invoice $invoice)
    {
        abort_if($invoice->tenant_id !== auth()->user()->tenant_id, 403);
        $invoice->load(['customer', 'items.product', 'payments', 'tenant']);
        return view('quick-sale.receipt', compact('invoice'));
    }

    public function productSearch(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;
        $q        = $request->q;

        $products = Product::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->where(fn($query) =>
                $query->where('name', 'like', "%{$q}%")
                      ->orWhere('barcode', $q)
                      ->orWhere('sku', 'like', "%{$q}%")
            )
            ->limit(10)
            ->get(['id', 'name', 'unit_price', 'tax_rate', 'unit', 'stock_quantity', 'barcode']);

        return response()->json($products);
    }
}
