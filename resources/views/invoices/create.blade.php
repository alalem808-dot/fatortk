@extends('layouts.app')
@section('title', 'فاتورة جديدة')
@section('page-title')
<h6 class="mb-0 fw-bold">إنشاء فاتورة جديدة</h6>
@endsection

@section('content')
<form action="{{ route('invoices.store') }}" method="POST" x-data="invoiceForm()">
    @csrf
    <div class="row g-3">
        <div class="col-md-8">
            {{-- بيانات الفاتورة --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">العميل <span class="text-danger">*</span></label>
                            <select name="customer_id" class="form-select" required>
                                <option value="">اختر العميل</option>
                                @foreach($customers as $c)
                                <option value="{{ $c->id }}" {{ old('customer_id')==$c->id?'selected':'' }}>{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">تاريخ الفاتورة</label>
                            <input type="date" name="invoice_date" class="form-control" value="{{ old('invoice_date', date('Y-m-d')) }}" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">تاريخ الاستحقاق</label>
                            <input type="date" name="due_date" class="form-control" value="{{ old('due_date') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">العملة</label>
                            <select name="currency" class="form-select">
                                <option value="SDG" selected>جنيه سوداني (SDG)</option>
                                <option value="USD">دولار (USD)</option>
                                <option value="EUR">يورو (EUR)</option>
                                <option value="SAR">ريال سعودي (SAR)</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">لغة الفاتورة</label>
                            <select name="language" class="form-select">
                                <option value="ar" selected>العربية</option>
                                <option value="en">English</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">الحالة</label>
                            <select name="status" class="form-select">
                                <option value="draft">مسودة</option>
                                <option value="sent">مرسلة</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">القالب</label>
                            <select name="template_id" class="form-select">
                                <option value="">افتراضي</option>
                                @foreach($templates as $t)
                                <option value="{{ $t->id }}">{{ $t->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            {{-- بنود الفاتورة --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white border-0 pt-3 d-flex justify-content-between">
                    <h6 class="fw-bold mb-0">بنود الفاتورة</h6>
                    <button type="button" class="btn btn-sm btn-outline-primary" @click="addItem()">
                        <i class="fas fa-plus"></i> إضافة بند
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:35%">الوصف</th>
                                    <th style="width:12%">الكمية</th>
                                    <th style="width:15%">السعر</th>
                                    <th style="width:10%">الضريبة%</th>
                                    <th style="width:15%">الإجمالي</th>
                                    <th style="width:5%"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(item, index) in items" :key="index">
                                    <tr>
                                        <td>
                                            <select class="form-select form-select-sm mb-1" @change="selectProduct($event, index)">
                                                <option value="">-- اختر منتج --</option>
                                                @foreach($products as $p)
                                                <option value="{{ $p->id }}" data-price="{{ $p->unit_price }}" data-tax="{{ $p->tax_rate }}" data-name="{{ $p->name }}">{{ $p->name }}</option>
                                                @endforeach
                                            </select>
                                            <input type="text" :name="`items[${index}][description]`" x-model="item.description" class="form-control form-control-sm" placeholder="الوصف" required>
                                            <input type="hidden" :name="`items[${index}][product_id]`" x-model="item.product_id">
                                        </td>
                                        <td><input type="number" :name="`items[${index}][quantity]`" x-model.number="item.quantity" @input="calcItem(index)" class="form-control form-control-sm" min="0.01" step="0.01" required></td>
                                        <td><input type="number" :name="`items[${index}][unit_price]`" x-model.number="item.unit_price" @input="calcItem(index)" class="form-control form-control-sm" min="0" step="0.01" required></td>
                                        <td><input type="number" :name="`items[${index}][tax_rate]`" x-model.number="item.tax_rate" @input="calcItem(index)" class="form-control form-control-sm" min="0" max="100" step="0.01"></td>
                                        <td><span class="fw-semibold" x-text="item.total.toFixed(2)"></span></td>
                                        <td><button type="button" class="btn btn-sm btn-outline-danger" @click="removeItem(index)" x-show="items.length > 1"><i class="fas fa-trash"></i></button></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- الملاحظات --}}
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">ملاحظات</label>
                            <textarea name="notes" class="form-control" rows="3">{{ old('notes') }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">الشروط والأحكام</label>
                            <textarea name="terms_conditions" class="form-control" rows="3">{{ old('terms_conditions') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ملخص الفاتورة --}}
        <div class="col-md-4">
            <div class="card border-0 shadow-sm sticky-top" style="top:80px">
                <div class="card-header bg-white border-0 pt-3">
                    <h6 class="fw-bold mb-0">ملخص الفاتورة</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">المجموع الفرعي</span>
                        <span x-text="subtotal.toFixed(2)"></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">الضريبة</span>
                        <span x-text="taxTotal.toFixed(2)"></span>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">الخصم</label>
                        <div class="input-group input-group-sm">
                            <input type="number" name="discount_amount" x-model.number="discount" @input="calcTotals()" class="form-control" min="0" step="0.01" value="0">
                            <select name="discount_type" x-model="discountType" @change="calcTotals()" class="form-select" style="max-width:80px">
                                <option value="fixed">ثابت</option>
                                <option value="percent">%</option>
                            </select>
                        </div>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between fw-bold fs-5">
                        <span>الإجمالي</span>
                        <span x-text="grandTotal.toFixed(2)"></span>
                    </div>
                    <div class="mt-3 d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> حفظ الفاتورة
                        </button>
                        <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary">إلغاء</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
function invoiceForm() {
    return {
        items: [{ product_id: '', description: '', quantity: 1, unit_price: 0, tax_rate: 0, total: 0 }],
        discount: 0,
        discountType: 'fixed',
        subtotal: 0,
        taxTotal: 0,
        grandTotal: 0,

        addItem() {
            this.items.push({ product_id: '', description: '', quantity: 1, unit_price: 0, tax_rate: 0, total: 0 });
        },
        removeItem(i) {
            this.items.splice(i, 1);
            this.calcTotals();
        },
        selectProduct(e, i) {
            const opt = e.target.selectedOptions[0];
            if (opt.value) {
                this.items[i].product_id = opt.value;
                this.items[i].unit_price = parseFloat(opt.dataset.price) || 0;
                this.items[i].tax_rate   = parseFloat(opt.dataset.tax)   || 0;
                this.items[i].description = opt.dataset.name;
                this.calcItem(i);
            }
        },
        calcItem(i) {
            const item = this.items[i];
            const base = item.quantity * item.unit_price;
            item.total = base + (base * item.tax_rate / 100);
            this.calcTotals();
        },
        calcTotals() {
            this.subtotal = this.items.reduce((s, i) => s + (i.quantity * i.unit_price), 0);
            this.taxTotal = this.items.reduce((s, i) => s + (i.quantity * i.unit_price * i.tax_rate / 100), 0);
            const disc = this.discountType === 'percent' ? (this.subtotal * this.discount / 100) : this.discount;
            this.grandTotal = this.subtotal + this.taxTotal - disc;
        }
    }
}
</script>
@endpush
