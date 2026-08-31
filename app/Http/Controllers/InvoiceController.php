<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Customer;
use App\Models\Product;
use App\Models\InvoiceTemplate;
use App\Services\PDFService;
use App\Services\WhatsAppService;
use App\Services\EmailService;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class InvoiceController extends Controller
{
    public function __construct(
        private PDFService $pdfService,
        private WhatsAppService $whatsAppService,
        private EmailService $emailService,
        private StockService $stockService,
    ) {}

    public function index(Request $request)
    {
        $user = auth()->user();
        $tenantId = $user->tenant_id;

        $query = Invoice::where('tenant_id', $tenantId)
            ->with('customer')
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->search, fn($q) => $q->where('invoice_number', 'like', "%{$request->search}%")
                ->orWhereHas('customer', fn($q2) => $q2->where('name', 'like', "%{$request->search}%")));

        // المستخدمون غير الـ admin يرون فواتيرهم فقط
        if ($user->role !== 'admin' && !$user->hasRole('admin')) {
            $query->where('created_by', $user->id);
        }

        $invoices = $query->latest()->paginate(15);
        return view('invoices.index', compact('invoices'));
    }

    public function create()
    {
        $tenantId        = auth()->user()->tenant_id;
        $customers       = Customer::where('tenant_id', $tenantId)->get();
        $products        = Product::where('tenant_id', $tenantId)->where('status', 'active')->get();
        $templates       = InvoiceTemplate::where('tenant_id', $tenantId)->get();
        $defaultCurrency = auth()->user()->tenant->getSetting('default_currency', 'SDG');

        // مخازن المستخدم المتاحة
        $userWarehouses  = view()->shared('userWarehouses') ?? collect();
        $userWarehouse   = auth()->user()->getDefaultWarehouse();

        // مخزون كل منتج في كل مخزن متاح للمستخدم
        $warehouseStocks = [];
        foreach ($userWarehouses as $wh) {
            $stocks = \App\Models\WarehouseStock::where('warehouse_id', $wh->id)
                ->pluck('quantity', 'product_id')
                ->toArray();
            $warehouseStocks[$wh->id] = array_map('floatval', $stocks);
        }

        // إذا لم يكن هناك مخازن، جلب الكمية الكلية مباشرة
        $warehouseStockMap = [];
        if ($userWarehouse) {
            $warehouseStockMap = $warehouseStocks[$userWarehouse->id] ?? [];
        }

        // طرق الدفع للدفعة الأولية
        $paymentMethods = \App\Models\PaymentMethod::where('tenant_id', $tenantId)
            ->where('is_active', true)->get();

        return view('invoices.create', compact(
            'customers', 'products', 'templates', 'defaultCurrency',
            'userWarehouses', 'userWarehouse', 'warehouseStocks',
            'warehouseStockMap', 'paymentMethods'
        ));
    }

    public function store(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        $request->validate([
            'customer_id'         => ['required', Rule::exists('customers', 'id')->where('tenant_id', $tenantId)],
            'invoice_date'        => 'required|date',
            'items'               => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.quantity'    => 'required|numeric|min:0.01',
            'items.*.unit_price'  => 'required|numeric|min:0',
            'items.*.product_id'  => ['nullable', Rule::exists('products', 'id')->where('tenant_id', $tenantId)],
            'initial_payment'     => 'nullable|numeric|min:0.01',
            'initial_payment_method' => 'nullable|string',
            'warehouse_id'        => ['nullable', Rule::exists('warehouses', 'id')->where('tenant_id', $tenantId)],
        ]);

        $invoiceRef = null;

        try {
            DB::transaction(function () use ($request, $tenantId, &$invoiceRef) {
            $invoice = Invoice::create([
                'tenant_id'        => $tenantId,
                'invoice_number'   => Invoice::generateNumber($tenantId),
                'customer_id'      => $request->customer_id,
                'template_id'      => $request->template_id,
                'invoice_date'     => $request->invoice_date,
                'due_date'         => $request->due_date,
                'status'           => $request->status ?? 'draft',
                'discount_amount'  => $request->discount_amount ?? 0,
                'discount_type'    => $request->discount_type ?? 'fixed',
                'currency'         => auth()->user()->tenant->getSetting('default_currency', 'SDG'),
                'exchange_rate'    => 1,
                'language'         => $request->language ?? 'ar',
                'notes'            => $request->notes,
                'terms_conditions' => $request->terms_conditions,
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
                    'discount'    => $item['discount'] ?? 0,
                    'total'       => $lineTotal,
                ]);
            }

            $invoice->load('items');
            $invoice->calculateTotals();

            // تحديد المخزن: المحدد يدوياً أو مخزن المستخدم الافتراضي
            $warehouseId = $request->warehouse_id
                ?? auth()->user()->getDefaultWarehouse()?->id;

            // حفظ warehouse_id على الفاتورة لاستخدامه لاحقاً في التعديل وتغيير الحالة
            $invoice->update(['warehouse_id' => $warehouseId]);

            // خصم المخزون عند الإرسال
            if (in_array($invoice->status, ['sent', 'paid', 'partially_paid'])) {
                $this->stockService->deductForInvoice($invoice, $warehouseId);
            }

            // تسجيل الدفعة الأولية إن وُجدت
            if ($request->filled('initial_payment') && (float)$request->initial_payment > 0) {
                $payAmount = min((float)$request->initial_payment, $invoice->total_amount);
                \App\Models\Payment::create([
                    'tenant_id'      => $tenantId,
                    'invoice_id'     => $invoice->id,
                    'payment_date'   => $request->invoice_date,
                    'amount'         => $payAmount,
                    'payment_method' => $request->initial_payment_method ?? 'cash',
                    'notes'          => 'دفعة عند إنشاء الفاتورة',
                ]);

                $invoice->increment('paid_amount', $payAmount);
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

        return redirect()->route('invoices.show', $invoiceRef)->with('success', 'تم إنشاء الفاتورة بنجاح.');
    }

    public function show(Invoice $invoice)
    {
        $this->authorizeTenant($invoice);

        // المستخدم غير Admin: يرى فقط فواتيره
        $user = auth()->user();
        if (!$user->isAdmin() && $invoice->created_by !== $user->id) {
            abort(403);
        }

        $invoice->load(['customer', 'items.product', 'payments', 'template', 'tenant', 'returns.items']);
        return view('invoices.show', compact('invoice'));
    }

    public function edit(Invoice $invoice)
    {
        $this->authorizeTenant($invoice);
        $tenantId = auth()->user()->tenant_id;
        $customers = Customer::where('tenant_id', $tenantId)->get();
        $products  = Product::where('tenant_id', $tenantId)->where('status', 'active')->get();
        $templates = InvoiceTemplate::where('tenant_id', $tenantId)->get();
        $invoice->load('items');
        $invoiceItems = $invoice->items->map(function ($i) {
            return [
                'product_id'  => $i->product_id,
                'description' => $i->description,
                'quantity'    => (float) $i->quantity,
                'unit_price'  => (float) $i->unit_price,
                'tax_rate'    => (float) $i->tax_rate,
                'total'       => (float) $i->total,
            ];
        })->values()->toArray();
        return view('invoices.edit', compact('invoice', 'customers', 'products', 'templates', 'invoiceItems'));
    }

    public function update(Request $request, Invoice $invoice)
    {
        $this->authorizeTenant($invoice);

        try {
            DB::transaction(function () use ($request, $invoice) {
            $oldStatus = $invoice->status;
            $sentStatuses = ['sent', 'paid', 'partially_paid'];
            $wasActive = in_array($oldStatus, $sentStatuses);

            // إعادة المخزون للبنود القديمة إذا كانت الفاتورة نشطة
            if ($wasActive) {
                $invoice->load('items.product');
                // استخدام warehouse_id المحفوظ على الفاتورة (BUG-02 fix)
                $warehouseId = $invoice->warehouse_id
                    ?? auth()->user()->getDefaultWarehouse()?->id;
                foreach ($invoice->items as $item) {
                    if ($item->product_id && $item->product) {
                        $this->stockService->move(
                            $item->product, 'in', (float) $item->quantity,
                            'invoice_edit_restore', $invoice->id,
                            'إعادة مخزون عند تعديل فاتورة: ' . $invoice->invoice_number,
                            $warehouseId
                        );
                    }
                }
            }

            $invoice->update($request->only([
                'customer_id', 'template_id', 'invoice_date', 'due_date',
                'status', 'discount_amount', 'discount_type', 'currency',
                'exchange_rate', 'language', 'notes', 'terms_conditions',
            ]));

            $invoice->items()->delete();
            foreach ($request->items as $item) {
                $invoice->items()->create([
                    'product_id'  => $item['product_id'] ?? null,
                    'description' => $item['description'],
                    'quantity'    => $item['quantity'],
                    'unit_price'  => $item['unit_price'],
                    'tax_rate'    => $item['tax_rate'] ?? 0,
                    'discount'    => $item['discount'] ?? 0,
                    'total'       => $item['quantity'] * $item['unit_price'],
                ]);
            }

            $invoice->load('items');
            $invoice->calculateTotals();

            // خصم المخزون للبنود الجديدة إذا كانت الفاتورة نشطة
            $newStatus = $invoice->fresh()->status;
            if (in_array($newStatus, $sentStatuses)) {
                // استخدام warehouse_id المحفوظ على الفاتورة (BUG-02 fix)
                $warehouseId = $invoice->warehouse_id
                    ?? auth()->user()->getDefaultWarehouse()?->id;
                $this->stockService->deductForInvoice($invoice->fresh(), $warehouseId);
            }
            });
        } catch (\RuntimeException $e) {
            return back()->withErrors(['items' => $e->getMessage()])->withInput();
        }

        return redirect()->route('invoices.show', $invoice)->with('success', 'تم تحديث الفاتورة.');
    }

    public function destroy(Invoice $invoice)
    {
        $this->authorizeTenant($invoice);
        $invoice->delete();
        return redirect()->route('invoices.index')->with('success', 'تم حذف الفاتورة.');
    }

    public function downloadPdf(Invoice $invoice)
    {
        $this->authorizeTenant($invoice);
        $invoice->load(['customer', 'items', 'tenant', 'template', 'returns']);
        return $this->pdfService->download($invoice);
    }

    public function whatsapp(Invoice $invoice)
    {
        $this->authorizeTenant($invoice);
        $link = $this->whatsAppService->getLink($invoice);
        return redirect($link);
    }

    public function updateStatus(Request $request, Invoice $invoice)
    {
        $this->authorizeTenant($invoice);
        $request->validate(['status' => 'required|in:draft,sent,paid,partially_paid,overdue,cancelled,returned']);

        $oldStatus    = $invoice->status;
        $newStatus    = $request->status;
        $sentStatuses = ['sent', 'paid', 'partially_paid'];

        $invoice->update(['status' => $newStatus]);

        // استخدام warehouse_id المحفوظ على الفاتورة (BUG-03 fix)
        $warehouseId = $invoice->warehouse_id
            ?? auth()->user()->getDefaultWarehouse()?->id;

        // الانتقال إلى حالة نشطة لأول مرة → خصم المخزون
        if (in_array($newStatus, $sentStatuses) && !in_array($oldStatus, $sentStatuses)) {
            $this->stockService->deductForInvoice($invoice, $warehouseId);
        }

        // الرجوع من حالة نشطة إلى draft/cancelled/returned → إعادة المخزون
        if (!in_array($newStatus, $sentStatuses) && in_array($oldStatus, $sentStatuses)) {
            $invoice->load('items.product');
            foreach ($invoice->items as $item) {
                if ($item->product_id && $item->product) {
                    $this->stockService->move(
                        $item->product, 'in', (float) $item->quantity,
                        'invoice_status_restore', $invoice->id,
                        'إعادة مخزون عند إلغاء/تراجع الفاتورة: ' . $invoice->invoice_number,
                        $warehouseId
                    );
                }
            }
        }

        return back()->with('success', 'تم تغيير حالة الفاتورة.');
    }

    public function sendEmail(Request $request, Invoice $invoice)
    {
        $this->authorizeTenant($invoice);
        $sent = $this->emailService->sendInvoice($invoice, $request->email);
        return back()->with($sent ? 'success' : 'error', $sent ? 'تم إرسال الفاتورة بنجاح.' : 'فشل الإرسال.');
    }

    public function publicView(string $token)
    {
        // token = public_token (UUID مشفر لمنع تخمين الفواتير)
        $invoice = Invoice::where('public_token', $token)->firstOrFail();

        // OPS-07 Fix: الفواتير الملغاة أو المسودة لا تُعرض للعامة
        abort_if(
            in_array($invoice->status, ['draft', 'cancelled', 'returned']),
            403,
            'هذه الفاتورة غير متاحة للعرض.'
        );

        $invoice->load(['customer', 'items.product', 'tenant', 'template', 'returns']);
        return $this->pdfService->stream($invoice);
    }

    private function authorizeTenant(Invoice $invoice): void
    {
        abort_if($invoice->tenant_id !== auth()->user()->tenant_id, 403);
    }
}
