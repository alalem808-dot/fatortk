@extends('layouts.app')
@section('title', 'فاتورة جديدة')
@section('page-title')<span>إنشاء فاتورة جديدة</span>@endsection

@section('content')

@php
// تجهيز بيانات المنتجات لـ JavaScript
$productsJson = $products->map(fn($p) => [
    'id'           => $p->id,
    'name'         => $p->name,
    'price'        => (float) $p->unit_price,
    'tax'          => (float) $p->tax_rate,
    'unit'         => $p->unit,
    'total_stock'  => (float) $p->stock_quantity, // الكمية الكلية كـ fallback دائماً
])->values()->toJson();

// مخزون المخازن
$warehouseStocksJson = collect($warehouseStocks ?? [])->map(fn($stocks) =>
    collect($stocks)->map(fn($qty) => (float)$qty)
)->toJson();
@endphp

<form action="{{ route('invoices.store') }}" method="POST" id="invoiceForm">
    @csrf

    <div class="row g-3">
        {{-- ===== العمود الرئيسي ===== --}}
        <div class="col-xl-8 col-lg-7">

            {{-- بيانات الفاتورة --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header">
                    <h6 class="fw-bold mb-0"><i class="fas fa-file-invoice me-2 text-primary"></i>بيانات الفاتورة</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">العميل <span class="text-danger">*</span></label>
                            <select name="customer_id" class="form-select" required>
                                <option value="">— اختر العميل —</option>
                                @foreach($customers as $c)
                                <option value="{{ $c->id }}" {{ old('customer_id')==$c->id?'selected':'' }}>{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">تاريخ الفاتورة <span class="text-danger">*</span></label>
                            <input type="date" name="invoice_date" class="form-control" value="{{ old('invoice_date', date('Y-m-d')) }}" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">تاريخ الاستحقاق</label>
                            <input type="date" name="due_date" class="form-control" value="{{ old('due_date') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">الحالة</label>
                            <select name="status" class="form-select" id="invoiceStatus">
                                <option value="draft">مسودة</option>
                                <option value="sent">مرسلة (يخصم المخزون)</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">العملة</label>
                            <input type="text" name="currency" class="form-control bg-light" value="{{ $defaultCurrency }}" readonly>
                            <input type="hidden" name="exchange_rate" value="1">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">لغة الفاتورة</label>
                            <select name="language" class="form-select">
                                <option value="ar">العربية</option>
                                <option value="en">English</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">القالب</label>
                            <select name="template_id" class="form-select">
                                <option value="">الافتراضي</option>
                                @foreach($templates as $t)
                                <option value="{{ $t->id }}">{{ $t->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        {{-- اختيار المخزن --}}
                        @if(isset($userWarehouses) && $userWarehouses->count() > 0)
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                المخزن
                                <span class="text-muted fw-normal small">(سيُخصم منه عند الإرسال)</span>
                            </label>
                            <select name="warehouse_id" class="form-select" id="warehouseSelect" onchange="onWarehouseChange(this.value)">
                                @foreach($userWarehouses as $wh)
                                <option value="{{ $wh->id }}" {{ ($userWarehouse?->id == $wh->id) ? 'selected' : '' }}>
                                    {{ $wh->name }}{{ $wh->is_default ? ' (افتراضي)' : '' }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        @else
                        <input type="hidden" name="warehouse_id" value="{{ $userWarehouse?->id }}">
                        @endif
                    </div>
                </div>
            </div>

            {{-- ===== بنود الفاتورة ===== --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0"><i class="fas fa-list me-2 text-primary"></i>بنود الفاتورة <span class="text-danger">*</span></h6>
                    <button type="button" class="btn btn-sm btn-primary" onclick="addRow()">
                        <i class="fas fa-plus me-1"></i> إضافة بند
                    </button>
                </div>
                <div id="itemsContainer">
                    {{-- البنود تُضاف هنا بـ JavaScript --}}
                </div>
                <div class="p-3 border-top">
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="addRow()">
                        <i class="fas fa-plus me-1"></i> بند جديد
                    </button>
                </div>
            </div>

            {{-- الملاحظات --}}
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">ملاحظات</label>
                            <textarea name="notes" class="form-control" rows="3" placeholder="تظهر في الفاتورة">{{ old('notes') }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">الشروط والأحكام</label>
                            <textarea name="terms_conditions" class="form-control" rows="3" placeholder="تظهر في أسفل الفاتورة">{{ old('terms_conditions') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== الشريط الجانبي ===== --}}
        <div class="col-xl-4 col-lg-5">
            <div class="card border-0 shadow-sm" style="position:sticky;top:80px">
                <div class="card-header">
                    <h6 class="fw-bold mb-0"><i class="fas fa-calculator me-2 text-primary"></i>ملخص الفاتورة</h6>
                </div>
                <div class="card-body">

                    {{-- المجاميع --}}
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">المجموع الفرعي</span>
                        <span class="fw-600" id="subtotalDisplay">0.00 {{ $defaultCurrency }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">الضريبة</span>
                        <span class="fw-600" id="taxDisplay">0.00 {{ $defaultCurrency }}</span>
                    </div>

                    {{-- الخصم --}}
                    <div class="py-2 border-bottom">
                        <label class="form-label small mb-1">الخصم</label>
                        <div class="input-group">
                            <input type="number" name="discount_amount" id="discountAmount"
                                   class="form-control" min="0" step="0.01" value="0"
                                   oninput="calcTotals()">
                            <select name="discount_type" id="discountType"
                                    class="form-select" style="max-width:90px"
                                    onchange="calcTotals()">
                                <option value="fixed">ثابت</option>
                                <option value="percent">%</option>
                            </select>
                        </div>
                    </div>

                    {{-- الإجمالي --}}
                    <div class="d-flex justify-content-between align-items-center py-3 border-bottom">
                        <span class="fw-700 fs-6">الإجمالي الكلي</span>
                        <span class="fw-800 fs-4 text-primary" id="totalDisplay">0.00 {{ $defaultCurrency }}</span>
                    </div>

                    {{-- دفعة عند الإنشاء --}}
                    <div class="pt-3 pb-2">
                        <div class="form-check mb-2">
                            <input type="checkbox" class="form-check-input" id="addInitialPayment"
                                   onchange="togglePaymentSection(this.checked)">
                            <label class="form-check-label fw-600" for="addInitialPayment">
                                تسجيل دفعة عند الإنشاء
                            </label>
                        </div>
                        <div id="initialPaymentSection" class="d-none">
                            <div class="p-3 rounded" style="background:#f0fdf4;border:1px solid #bbf7d0">
                                <div class="mb-2">
                                    <label class="form-label small fw-600">المبلغ المدفوع <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="number" name="initial_payment" id="initialPaymentAmount"
                                               class="form-control" min="0.01" step="0.01"
                                               placeholder="0.00">
                                        <span class="input-group-text">{{ $defaultCurrency }}</span>
                                    </div>
                                    <div class="form-text text-success" id="paymentHint">
                                        سيتغير وضع الفاتورة تلقائياً
                                    </div>
                                </div>
                                <div>
                                    <label class="form-label small fw-600">طريقة الدفع</label>
                                    <select name="initial_payment_method" class="form-select form-select-sm">
                                        @foreach($paymentMethods as $pm)
                                        <option value="{{ $pm->code }}">{{ $pm->name }}</option>
                                        @endforeach
                                        @if($paymentMethods->isEmpty())
                                        <option value="cash">نقدي</option>
                                        <option value="bank">تحويل بنكي</option>
                                        @endif
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- تنبيه مخزون --}}
                    <div id="stockWarningBanner" class="alert alert-warning py-2 mb-2 d-none" style="font-size:.8rem">
                        <i class="fas fa-triangle-exclamation me-1"></i>
                        يوجد بنود تتجاوز الكمية المتاحة في المخزن
                    </div>

                    <div class="d-grid gap-2 mt-2">
                        <button type="submit" class="btn btn-primary" style="font-size:1rem;padding:.7rem">
                            <i class="fas fa-save me-1"></i> حفظ الفاتورة
                        </button>
                        <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary">إلغاء</a>
                    </div>
                </div>
            </div>
        </div>

    </div>
</form>

@push('scripts')
<script>
// ===== البيانات من PHP =====
const PRODUCTS     = {!! $productsJson !!};
const WH_STOCKS    = {!! $warehouseStocksJson !!};
const DEFAULT_WH   = {{ $userWarehouse?->id ?? 'null' }};
const CURRENCY     = '{{ $defaultCurrency }}';

let currentWarehouseId = DEFAULT_WH;
let rowIndex = 0;

// ===== تغيير المخزن =====
function onWarehouseChange(whId) {
    currentWarehouseId = parseInt(whId) || null;
    // إعادة حساب تنبيهات المخزون لكل البنود
    document.querySelectorAll('.item-row').forEach((row, i) => {
        const productId = parseInt(row.querySelector('.product-select').value);
        if (productId) checkStock(row, productId);
    });
}

// ===== الحصول على الكمية المتاحة =====
function getAvailableStock(productId) {
    if (!productId) return null;
    const p = PRODUCTS.find(p => p.id == productId);
    if (!p) return null;

    // إذا وُجد مخزن محدد، ابحث في warehouse_stocks
    if (currentWarehouseId) {
        const stocks = WH_STOCKS[currentWarehouseId];
        if (stocks && stocks[productId] !== undefined) {
            return parseFloat(stocks[productId]);
        }
        // لا يوجد سجل في warehouse_stocks → ارجع للكمية الكلية
    }

    // fallback: الكمية الكلية من جدول products
    return parseFloat(p.total_stock ?? 0);
}

// ===== إضافة بند =====
function addRow(productId, productName, price, tax, unit, stock) {
    const idx = rowIndex++;
    const container = document.getElementById('itemsContainer');

    const div = document.createElement('div');
    div.className = 'item-row border-bottom p-3';
    div.dataset.idx = idx;

    const availableStock = stock !== undefined ? stock : (productId ? getAvailableStock(productId) : null);
    const stockInfo = availableStock !== null
        ? `<div class="stock-info text-muted mt-1" style="font-size:.72rem"><i class="fas fa-warehouse me-1"></i>متاح: <span class="stock-qty">${availableStock}</span> ${unit||''}</div>`
        : '';
    const stockWarn = `<div class="stock-warning d-none alert alert-warning py-1 mt-1 mb-0" style="font-size:.72rem"></div>`;

    div.innerHTML = `
        <div class="row g-2 align-items-end">

            <div class="col-12 col-md-5">
                <label class="form-label small text-muted mb-1">المنتج / الوصف</label>
                <select class="form-select form-select-sm product-select mb-1" onchange="onProductSelect(this, ${idx})">
                    <option value="">— اختر من المخزون (اختياري) —</option>
                    ${PRODUCTS.map(p => {
                        const qty = getAvailableStock(p.id);
                        const qtyText = qty !== null ? ` (${parseFloat(qty).toFixed(2)} ${p.unit})` : '';
                        return `<option value="${p.id}" ${productId==p.id?'selected':''}>${p.name}${qtyText}</option>`;
                    }).join('')}
                </select>
                <input type="text"
                       name="items[${idx}][description]"
                       class="form-control"
                       value="${productName||''}"
                       placeholder="وصف البند *"
                       required
                       style="font-size:.9rem">
                <input type="hidden" name="items[${idx}][product_id]" class="product-id-field" value="${productId||''}">
                ${stockInfo}
                ${stockWarn}
            </div>

            <div class="col-6 col-md-2">
                <label class="form-label small text-muted mb-1">الكمية *</label>
                <input type="number"
                       name="items[${idx}][quantity]"
                       class="form-control qty-field"
                       value="1" min="0.001" step="0.001" required
                       style="font-size:1rem;font-weight:700;text-align:center"
                       oninput="calcRow(${idx})">
            </div>

            <div class="col-6 col-md-2">
                <label class="form-label small text-muted mb-1">السعر *</label>
                <input type="number"
                       name="items[${idx}][unit_price]"
                       class="form-control price-field"
                       value="${price||0}" min="0" step="0.01" required
                       style="font-size:1rem;font-weight:700;text-align:center"
                       oninput="calcRow(${idx})">
            </div>

            <div class="col-4 col-md-1">
                <label class="form-label small text-muted mb-1">ضريبة%</label>
                <input type="number"
                       name="items[${idx}][tax_rate]"
                       class="form-control tax-field"
                       value="${tax||0}" min="0" max="100" step="0.01"
                       style="text-align:center"
                       oninput="calcRow(${idx})">
            </div>

            <div class="col-6 col-md-2">
                <label class="form-label small text-muted mb-1">الإجمالي</label>
                <div class="form-control bg-light fw-700 text-center total-display"
                     style="font-size:1rem;color:var(--primary)">
                    ${price ? (1 * parseFloat(price)).toFixed(2) : '0.00'}
                </div>
            </div>

            <div class="col-2 col-md-1 d-flex align-items-end pb-1">
                <button type="button" class="btn btn-outline-danger btn-sm w-100" onclick="removeRow(${idx})" title="حذف">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    `;

    container.appendChild(div);
    calcTotals();
}

// ===== اختيار منتج =====
function onProductSelect(select, idx) {
    const row   = document.querySelector(`.item-row[data-idx="${idx}"]`);
    const pid   = parseInt(select.value);
    if (!pid) return;

    const p = PRODUCTS.find(p => p.id == pid);
    if (!p) return;

    row.querySelector('.product-id-field').value = pid;
    row.querySelector('[name$="[description]"]').value = p.name;
    row.querySelector('.price-field').value = p.price;
    row.querySelector('.tax-field').value   = p.tax;

    const avail = getAvailableStock(pid);
    let stockInfoEl = row.querySelector('.stock-info');
    if (stockInfoEl) {
        stockInfoEl.innerHTML = `<i class="fas fa-warehouse me-1"></i>متاح: <span class="stock-qty">${avail !== null ? parseFloat(avail).toFixed(2) : '—'}</span> ${p.unit}`;
    } else {
        const newInfo = document.createElement('div');
        newInfo.className = 'stock-info text-muted mt-1';
        newInfo.style.fontSize = '.72rem';
        newInfo.innerHTML = `<i class="fas fa-warehouse me-1"></i>متاح: <span class="stock-qty">${avail !== null ? parseFloat(avail).toFixed(2) : '—'}</span> ${p.unit}`;
        row.querySelector('.product-select').parentNode.appendChild(newInfo);
    }

    checkStock(row, pid);
    calcRow(idx);
}

// ===== تحقق المخزون =====
function checkStock(row, productId) {
    const qty    = parseFloat(row.querySelector('.qty-field')?.value) || 0;
    const avail  = getAvailableStock(productId);
    const warnEl = row.querySelector('.stock-warning');
    if (!warnEl) return;

    if (avail !== null && qty > avail) {
        warnEl.textContent = `⚠ الكمية المطلوبة (${qty}) تتجاوز المتاح (${avail.toFixed(2)})`;
        warnEl.classList.remove('d-none');
        row.style.background = '#fffbeb';
    } else {
        warnEl.classList.add('d-none');
        row.style.background = '';
    }

    const anyWarn = document.querySelectorAll('.stock-warning:not(.d-none)').length > 0;
    document.getElementById('stockWarningBanner').classList.toggle('d-none', !anyWarn);
}

// ===== حساب بند =====
function calcRow(idx) {
    const row   = document.querySelector(`.item-row[data-idx="${idx}"]`);
    if (!row) return;

    const qty   = parseFloat(row.querySelector('.qty-field').value)  || 0;
    const price = parseFloat(row.querySelector('.price-field').value) || 0;
    const tax   = parseFloat(row.querySelector('.tax-field').value)   || 0;
    const base  = qty * price;
    const total = base + (base * tax / 100);

    row.querySelector('.total-display').textContent = total.toFixed(2);

    const pid = parseInt(row.querySelector('.product-id-field')?.value);
    if (pid) checkStock(row, pid);

    calcTotals();
}

// ===== حساب الإجماليات =====
function calcTotals() {
    let subtotal = 0, taxTotal = 0;

    document.querySelectorAll('.item-row').forEach(row => {
        const qty   = parseFloat(row.querySelector('.qty-field')?.value)  || 0;
        const price = parseFloat(row.querySelector('.price-field')?.value) || 0;
        const tax   = parseFloat(row.querySelector('.tax-field')?.value)   || 0;
        const base  = qty * price;
        subtotal += base;
        taxTotal += base * tax / 100;
    });

    const discAmt  = parseFloat(document.getElementById('discountAmount')?.value) || 0;
    const discType = document.getElementById('discountType')?.value || 'fixed';
    const discVal  = discType === 'percent' ? (subtotal * discAmt / 100) : discAmt;
    const total    = subtotal + taxTotal - discVal;

    document.getElementById('subtotalDisplay').textContent = subtotal.toFixed(2) + ' ' + CURRENCY;
    document.getElementById('taxDisplay').textContent      = taxTotal.toFixed(2)  + ' ' + CURRENCY;
    document.getElementById('totalDisplay').textContent    = total.toFixed(2)     + ' ' + CURRENCY;

    // تحديث hint الدفعة
    const payInput = document.getElementById('initialPaymentAmount');
    if (payInput) payInput.max = total.toFixed(2);
    document.getElementById('paymentHint').textContent =
        `الإجمالي: ${total.toFixed(2)} ${CURRENCY}`;
}

// ===== حذف بند =====
function removeRow(idx) {
    const rows = document.querySelectorAll('.item-row');
    if (rows.length <= 1) { alert('يجب أن يكون هناك بند واحد على الأقل.'); return; }
    document.querySelector(`.item-row[data-idx="${idx}"]`)?.remove();
    calcTotals();
}

// ===== قسم الدفعة =====
function togglePaymentSection(show) {
    document.getElementById('initialPaymentSection').classList.toggle('d-none', !show);
    if (show) {
        document.getElementById('invoiceStatus').value = 'sent';
    }
}

// ===== التحقق قبل الإرسال =====
document.getElementById('invoiceForm').addEventListener('submit', function(e) {
    const rows = document.querySelectorAll('.item-row');
    if (rows.length === 0) {
        e.preventDefault();
        alert('يجب إضافة بند واحد على الأقل.');
        return;
    }

    let valid = true;
    rows.forEach(row => {
        const desc  = row.querySelector('[name$="[description]"]');
        const qty   = row.querySelector('.qty-field');
        const price = row.querySelector('.price-field');

        if (!desc?.value?.trim()) { desc.classList.add('is-invalid'); valid = false; }
        else desc.classList.remove('is-invalid');

        if (!qty?.value || parseFloat(qty.value) <= 0) { qty.classList.add('is-invalid'); valid = false; }
        else qty.classList.remove('is-invalid');

        if (price?.value === '' || parseFloat(price.value) < 0) { price.classList.add('is-invalid'); valid = false; }
        else price.classList.remove('is-invalid');
    });

    if (!valid) {
        e.preventDefault();
        alert('يرجى تصحيح البنود المُعلَّمة بالأحمر.');
    }
});

// إضافة بند أول عند التحميل
document.addEventListener('DOMContentLoaded', () => addRow());
</script>
@endpush

@endsection
