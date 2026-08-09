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
        $tenantId = auth()->user()->tenant_id;
        $invoices = Invoice::where('tenant_id', $tenantId)
            ->with('customer')
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->search, fn($q) => $q->where('invoice_number', 'like', "%{$request->search}%")
                ->orWhereHas('customer', fn($q2) => $q2->where('name', 'like', "%{$request->search}%")))
            ->latest()
            ->paginate(15);

        return view('invoices.index', compact('invoices'));
    }

    public function create()
    {
        $tenantId = auth()->user()->tenant_id;
        $customers  = Customer::where('tenant_id', $tenantId)->get();
        $products   = Product::where('tenant_id', $tenantId)->where('status', 'active')->get();
        $templates  = InvoiceTemplate::where('tenant_id', $tenantId)->get();
        return view('invoices.create', compact('customers', 'products', 'templates'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id'   => 'required|exists:customers,id',
            'invoice_date'  => 'required|date',
            'items'         => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.quantity'    => 'required|numeric|min:0.01',
            'items.*.unit_price'  => 'required|numeric|min:0',
        ]);

        $tenantId = auth()->user()->tenant_id;

        DB::transaction(function () use ($request, $tenantId) {
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
                'currency'         => $request->currency ?? 'SAR',
                'language'         => $request->language ?? 'ar',
                'notes'            => $request->notes,
                'terms_conditions' => $request->terms_conditions,
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

            $invoice->calculateTotals();

            if ($invoice->status === 'sent') {
                $this->stockService->deductForInvoice($invoice);
            }
        });

        return redirect()->route('invoices.index')->with('success', 'تم إنشاء الفاتورة بنجاح.');
    }

    public function show(Invoice $invoice)
    {
        $this->authorizeTenant($invoice);
        $invoice->load(['customer', 'items.product', 'payments', 'template', 'tenant']);
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

        DB::transaction(function () use ($request, $invoice) {
            $invoice->update($request->only([
                'customer_id', 'template_id', 'invoice_date', 'due_date',
                'status', 'discount_amount', 'discount_type', 'currency', 'language', 'notes', 'terms_conditions',
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

            $invoice->calculateTotals();
        });

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
        $invoice->load(['customer', 'items', 'tenant', 'template']);
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
        $request->validate(['status' => 'required|in:draft,sent,paid,overdue,cancelled']);
        $invoice->update(['status' => $request->status]);
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
        $invoice = Invoice::where('id', $token)->firstOrFail();
        $invoice->load(['customer', 'items.product', 'tenant', 'template']);
        return $this->pdfService->stream($invoice);
    }

    private function authorizeTenant(Invoice $invoice): void
    {
        abort_if($invoice->tenant_id !== auth()->user()->tenant_id, 403);
    }
}
