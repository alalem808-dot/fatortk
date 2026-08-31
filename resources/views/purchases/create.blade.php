@extends('layouts.app')
@section('title', 'أمر شراء جديد')
@section('page-title')<h6 class="mb-0 fw-bold">أمر شراء جديد</h6>@endsection

@section('content')

@if($errors->any())
<div class="alert alert-danger">
    <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

<form action="{{ route('purchases.store') }}" method="POST" id="purchaseForm">
    @csrf
    <div class="row g-3">
        <div class="col-md-8">
            {{-- بيانات المورد --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white border-0 pt-3"><h6 class="fw-bold mb-0">بيانات المورد</h6></div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">المورد</label>
                            @if($suppliers->count())
                            <select name="supplier_id" class="form-select" onchange="fillSupplier(this)">
                                <option value="">— اختر مورداً أو اكتب يدوياً —</option>
                                @foreach($suppliers as $s)
                                <option value="{{ $s->id }}" data-name="{{ $s->name }}" data-phone="{{ $s->phone }}"
                                    {{ old('supplier_id')==$s->id?'selected':'' }}>{{ $s->name }}</option>
                                @endforeach
                            </select>
                            @endif
                            <input type="text" name="supplier_name" id="supplierName" class="form-control {{ $suppliers->count() ? 'mt-2' : '' }}"
                                placeholder="اسم المورد" value="{{ old('supplier_name') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">هاتف المورد</label>
                            <input type="text" name="supplier_phone" id="supplierPhone" class="form-control" value="{{ old('supplier_phone') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">المرجع</label>
                            <input type="text" name="reference" class="form-control" value="{{ old('reference') }}" placeholder="PO-...">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">التاريخ <span class="text-danger">*</span></label>
                            <input type="date" name="purchase_date" class="form-control @error('purchase_date') is-invalid @enderror"
                                value="{{ old('purchase_date', date('Y-m-d')) }}" required>
                            @error('purchase_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <input type="hidden" name="currency" value="{{ $defaultCurrency ?? 'SDG' }}">
                        <input type="hidden" name="exchange_rate" value="1">
                        <div class="col-md-3">
                            <label class="form-label">العملة</label>
                            <input type="text" class="form-control bg-light" value="{{ $defaultCurrency ?? 'SDG' }}" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">الحالة</label>
                            <select name="status" class="form-select">
                                <option value="pending"  {{ old('status','pending')=='pending'  ?'selected':'' }}>معلق</option>
                                <option value="received" {{ old('status')=='received' ?'selected':'' }}>مستلم (يضاف للمخزون)</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">
                                المخزن <span class="text-muted fw-normal small">(سيُضاف المخزون إليه)</span>
                            </label>
                            <select name="warehouse_id" class="form-select">
                                <option value="">-- المخزن الافتراضي --</option>
                                @foreach($warehouses ?? [] as $wh)
                                <option value="{{ $wh->id }}"
                                    {{ old('warehouse_id', $defaultWarehouse?->id) == $wh->id ? 'selected' : '' }}>
                                    {{ $wh->name }}{{ $wh->is_default ? ' (افتراضي)' : '' }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            {{-- الأصناف --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white border-0 pt-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0">الأصناف <span class="text-danger">*</span></h6>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#newProductModal">
                            <i class="fas fa-plus me-1"></i> صنف جديد
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="addRow()">
                            <i class="fas fa-list me-1"></i> إضافة سطر
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    @error('items')<div class="alert alert-danger m-3 py-2">{{ $message }}</div>@enderror
                    <div class="table-responsive">
                        <table class="table mb-0" id="itemsTable">
                            <thead class="table-light">
                                <tr>
                                    <th>المنتج / الصنف</th>
                                    <th style="width:110px">الكمية <span class="text-danger">*</span></th>
                                    <th style="width:130px">سعر التكلفة <span class="text-danger">*</span></th>
                                    <th style="width:120px">الإجمالي</th>
                                    <th style="width:40px"></th>
                                </tr>
                            </thead>
                            <tbody id="itemsBody"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">ضريبة إضافية</label>
                        <input type="number" name="tax_amount" id="taxAmount" class="form-control" value="{{ old('tax_amount', 0) }}" min="0" step="0.01" onchange="calcTotal()">
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">المجموع الفرعي</span>
                        <span id="subtotalDisplay">0.00</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">الضريبة</span>
                        <span id="taxDisplay">0.00</span>
                    </div>
                    <div class="d-flex justify-content-between fw-bold fs-5 border-top pt-2">
                        <span>الإجمالي</span>
                        <span id="totalDisplay">0.00</span>
                    </div>
                    <hr>
                    <div class="mb-3">
                        <label class="form-label">ملاحظات</label>
                        <textarea name="notes" class="form-control" rows="3">{{ old('notes') }}</textarea>
                    </div>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <i class="fas fa-save me-1"></i> حفظ أمر الشراء
                        </button>
                        <a href="{{ route('purchases.index') }}" class="btn btn-outline-secondary">إلغاء</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

{{-- Modal إضافة صنف جديد --}}
<div class="modal fade" id="newProductModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-bold">إضافة صنف جديد للمخزون</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">اسم الصنف <span class="text-danger">*</span></label>
                    <input type="text" id="np_name" class="form-control" placeholder="اسم المنتج">
                </div>
                <div class="row g-3">
                    <div class="col-6">
                        <label class="form-label">سعر البيع</label>
                        <input type="number" id="np_price" class="form-control" value="0" min="0" step="0.01">
                    </div>
                    <div class="col-6">
                        <label class="form-label">سعر التكلفة</label>
                        <input type="number" id="np_cost" class="form-control" value="0" min="0" step="0.01">
                    </div>
                    <div class="col-6">
                        <label class="form-label">الكمية الافتتاحية</label>
                        <input type="number" id="np_stock" class="form-control" value="0" min="0">
                    </div>
                    <div class="col-6">
                        <label class="form-label">الحد الأدنى للتنبيه</label>
                        <input type="number" id="np_min" class="form-control" value="5" min="0">
                    </div>
                </div>
                <div id="np_error" class="text-danger small mt-2 d-none"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" onclick="saveNewProduct()">
                    <i class="fas fa-save me-1"></i> حفظ وإضافة للسطر
                </button>
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">إلغاء</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let rowCount = 0;
let products = @json($products);

// إضافة سطر أول تلقائياً
document.addEventListener('DOMContentLoaded', () => addRow());

function fillSupplier(sel) {
    const opt = sel.selectedOptions[0];
    if (opt.value) {
        document.getElementById('supplierName').value  = opt.dataset.name  || '';
        document.getElementById('supplierPhone').value = opt.dataset.phone || '';
    }
}

function buildOptions(selectedId = '') {
    let opts = '<option value="">-- اختر من المخزون --</option>';
    products.forEach(p => {
        opts += `<option value="${p.id}" data-name="${p.name}" data-cost="${p.cost_price || 0}" ${p.id == selectedId ? 'selected' : ''}>${p.name}</option>`;
    });
    return opts;
}

function addRow(productId = '', productName = '', cost = 0) {
    const idx = rowCount++;
    const tr = document.createElement('tr');
    tr.id = 'row_' + idx;
    tr.innerHTML = `
        <td>
            <select name="items[${idx}][product_id]" class="form-select form-select-sm mb-1" onchange="onProductSelect(this, ${idx})">
                ${buildOptions(productId)}
            </select>
            <input type="text" name="items[${idx}][product_name]"
                class="form-control form-control-sm"
                placeholder="اسم الصنف *"
                value="${productName}"
                required>
        </td>
        <td>
            <input type="number" name="items[${idx}][quantity]"
                class="form-control form-control-sm qty"
                value="1" min="0.001" step="0.001" required
                oninput="calcRow(${idx})">
            <div class="invalid-feedback">مطلوب</div>
        </td>
        <td>
            <input type="number" name="items[${idx}][unit_cost]"
                class="form-control form-control-sm cost"
                value="${cost}" min="0" step="0.01" required
                oninput="calcRow(${idx})">
            <div class="invalid-feedback">مطلوب</div>
        </td>
        <td>
            <input type="text" class="form-control form-control-sm bg-light total-cell"
                id="total_${idx}" readonly value="${(1 * cost).toFixed(2)}">
        </td>
        <td>
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeRow(${idx})">
                <i class="fas fa-times"></i>
            </button>
        </td>
    `;
    document.getElementById('itemsBody').appendChild(tr);
    calcTotal();
}

function onProductSelect(select, idx) {
    const opt = select.options[select.selectedIndex];
    const row = document.getElementById('row_' + idx);
    if (opt.value) {
        row.querySelector(`input[name="items[${idx}][product_name]"]`).value = opt.dataset.name;
        row.querySelector('.cost').value = opt.dataset.cost || 0;
        calcRow(idx);
    }
}

function calcRow(idx) {
    const row = document.getElementById('row_' + idx);
    if (!row) return;
    const qty  = parseFloat(row.querySelector('.qty').value) || 0;
    const cost = parseFloat(row.querySelector('.cost').value) || 0;
    document.getElementById('total_' + idx).value = (qty * cost).toFixed(2);
    calcTotal();
}

function calcTotal() {
    let subtotal = 0;
    document.querySelectorAll('.total-cell').forEach(el => subtotal += parseFloat(el.value) || 0);
    const tax = parseFloat(document.getElementById('taxAmount').value) || 0;
    document.getElementById('subtotalDisplay').textContent = subtotal.toFixed(2);
    document.getElementById('taxDisplay').textContent = tax.toFixed(2);
    document.getElementById('totalDisplay').textContent = (subtotal + tax).toFixed(2);
}

function removeRow(idx) {
    const rows = document.querySelectorAll('#itemsBody tr');
    if (rows.length <= 1) { alert('يجب أن يكون هناك صنف واحد على الأقل.'); return; }
    document.getElementById('row_' + idx)?.remove();
    calcTotal();
}

// التحقق قبل الإرسال
document.getElementById('purchaseForm').addEventListener('submit', function(e) {
    const rows = document.querySelectorAll('#itemsBody tr');
    if (rows.length === 0) {
        e.preventDefault();
        alert('يجب إضافة صنف واحد على الأقل.');
        return;
    }
    let valid = true;
    rows.forEach(row => {
        const name = row.querySelector('input[type="text"]:not(.total-cell)');
        const qty  = row.querySelector('.qty');
        const cost = row.querySelector('.cost');
        if (!name?.value.trim()) { name.classList.add('is-invalid'); valid = false; }
        else name.classList.remove('is-invalid');
        if (!qty?.value || parseFloat(qty.value) <= 0) { qty.classList.add('is-invalid'); valid = false; }
        else qty.classList.remove('is-invalid');
        if (cost?.value === '' || parseFloat(cost.value) < 0) { cost.classList.add('is-invalid'); valid = false; }
        else cost.classList.remove('is-invalid');
    });
    if (!valid) { e.preventDefault(); alert('يرجى تصحيح الأخطاء في الأصناف.'); }
});

// حفظ صنف جديد عبر AJAX
function saveNewProduct() {
    const name  = document.getElementById('np_name').value.trim();
    const price = document.getElementById('np_price').value;
    const cost  = document.getElementById('np_cost').value;
    const stock = document.getElementById('np_stock').value;
    const min   = document.getElementById('np_min').value;
    const errEl = document.getElementById('np_error');

    if (!name) { errEl.textContent = 'اسم الصنف مطلوب.'; errEl.classList.remove('d-none'); return; }
    errEl.classList.add('d-none');

    fetch('{{ route("products.store") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        },
        body: JSON.stringify({
            name, unit_price: price, cost_price: cost,
            stock_quantity: stock, min_stock_alert: min, status: 'active',
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data.id) {
            products.push(data);
            // تحديث كل الـ selects
            document.querySelectorAll('#itemsBody select').forEach(sel => {
                sel.innerHTML = buildOptions();
            });
            addRow(data.id, data.name, data.cost_price || 0);
            bootstrap.Modal.getInstance(document.getElementById('newProductModal')).hide();
            document.getElementById('np_name').value = '';
            document.getElementById('np_price').value = 0;
            document.getElementById('np_cost').value = 0;
            document.getElementById('np_stock').value = 0;
        } else {
            const msgs = data.errors ? Object.values(data.errors).flat().join(' | ') : 'حدث خطأ.';
            errEl.textContent = msgs;
            errEl.classList.remove('d-none');
        }
    })
    .catch(() => { errEl.textContent = 'فشل الاتصال بالخادم.'; errEl.classList.remove('d-none'); });
}
</script>
@endpush
