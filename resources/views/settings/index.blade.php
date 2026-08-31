@extends('layouts.app')
@section('title', 'الإعدادات')
@section('page-title')<span>إعدادات الحساب</span>@endsection

@section('content')
<form action="{{ route('settings.update') }}" method="POST" enctype="multipart/form-data" id="settingsForm">
    @csrf @method('PUT')

    <div class="row g-3">
        {{-- ===== العمود الرئيسي ===== --}}
        <div class="col-lg-8">

            {{-- بيانات الشركة --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header">
                    <h6 class="fw-bold mb-0"><i class="fas fa-building me-2 text-primary"></i>بيانات الشركة</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">اسم الشركة <span class="text-danger">*</span></label>
                            <input type="text" name="company_name" class="form-control" value="{{ old('company_name', $tenant->company_name) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">البريد الإلكتروني <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" value="{{ old('email', $tenant->email) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">رقم الهاتف</label>
                            <input type="text" name="phone" class="form-control" value="{{ old('phone', $tenant->phone) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">الرقم الضريبي</label>
                            <input type="text" name="tax_number" class="form-control" value="{{ old('tax_number', $tenant->tax_number) }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label">العنوان</label>
                            <textarea name="address" class="form-control" rows="2">{{ old('address', $tenant->address) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            {{-- إعدادات الفواتير --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header">
                    <h6 class="fw-bold mb-0"><i class="fas fa-file-invoice me-2 text-primary"></i>إعدادات الفواتير والمشتريات</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">بادئة رقم الفاتورة</label>
                            <input type="text" name="invoice_prefix" class="form-control" value="{{ old('invoice_prefix', $tenant->getSetting('invoice_prefix', 'INV')) }}" placeholder="INV">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">العملة الافتراضية</label>
                            @if($currencies->isNotEmpty())
                            <select name="default_currency" class="form-select">
                                @foreach($currencies as $cur)
                                <option value="{{ $cur->code }}"
                                    {{ $tenant->getSetting('default_currency', 'SDG') === $cur->code ? 'selected' : '' }}>
                                    {{ $cur->code }} — {{ $cur->name }}
                                    @if($cur->symbol) ({{ $cur->symbol }}) @endif
                                </option>
                                @endforeach
                            </select>
                            @else
                            <input type="text" name="default_currency" class="form-control"
                                   value="{{ old('default_currency', $tenant->getSetting('default_currency', 'SDG')) }}"
                                   placeholder="SDG" maxlength="5">
                            <div class="form-text text-warning">
                                <i class="fas fa-exclamation-triangle me-1"></i>
                                <a href="{{ route('currencies.index') }}">أضف عملات</a> لتظهر هنا كقائمة
                            </div>
                            @endif
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">نسبة الضريبة الافتراضية %</label>
                            <input type="number" name="default_tax_rate" class="form-control"
                                   value="{{ old('default_tax_rate', $tenant->getSetting('default_tax_rate', 0)) }}"
                                   min="0" max="100" step="0.01">
                        </div>
                        <div class="col-12">
                            <label class="form-label">الشروط والأحكام الافتراضية</label>
                            <textarea name="terms_conditions" class="form-control" rows="3"
                                      placeholder="تُضاف تلقائياً لكل فاتورة جديدة">{{ old('terms_conditions', $tenant->getSetting('terms_conditions')) }}</textarea>
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="allow_negative_stock" value="1" id="allowNegativeStock"
                                    {{ $tenant->getSetting('allow_negative_stock', '0') == '1' ? 'checked' : '' }}>
                                <label class="form-check-label" for="allowNegativeStock">
                                    السماح بالمخزون السالب
                                    <span class="text-muted small d-block">عند التفعيل، يمكن إصدار فواتير حتى لو كانت الكمية غير كافية في المخزن</span>
                                </label>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">طريقة احتساب سعر الشراء (التكلفة)</label>
                            <div class="d-flex flex-column gap-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="cost_price_method" value="wac" id="methodWac"
                                        {{ $tenant->getSetting('cost_price_method', 'wac') === 'wac' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="methodWac">
                                        المتوسط المرجح (WAC)
                                        <span class="text-muted small d-block">يُحسب متوسط التكلفة بين المخزون القديم والجديد </span>
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="cost_price_method" value="latest" id="methodLatest"
                                        {{ $tenant->getSetting('cost_price_method', 'wac') === 'latest' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="methodLatest">
                                        آخر سعر شراء
                                        <span class="text-muted small d-block">سعر الشراء الجديد يُطبَّق مباشرة على جميع الكميات (القديمة والجديدة)</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- التوقيع والختم --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header">
                    <h6 class="fw-bold mb-0"><i class="fas fa-signature me-2 text-primary"></i>التوقيع والختم</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">اسم الموقّع</label>
                            <input type="text" name="signer_name" class="form-control" value="{{ old('signer_name', $tenant->signer_name) }}" placeholder="مثال: أحمد محمد">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">الصفة / المنصب</label>
                            <input type="text" name="signer_title" class="form-control" value="{{ old('signer_title', $tenant->signer_title) }}" placeholder="مثال: المدير العام">
                        </div>

                        {{-- الختم --}}
                        <div class="col-md-6">
                            <label class="form-label">صورة الختم</label>
                            @if($tenant->stamp_image)
                            <div class="d-flex align-items-center gap-2 mb-2 p-2 border rounded" style="background:#f8fafc">
                                <img src="{{ url('storage/'.$tenant->stamp_image) }}" style="max-height:60px;max-width:120px" class="rounded">
                                <div>
                                    <div class="small text-muted mb-1">الختم الحالي</div>
                                    <label class="d-flex align-items-center gap-1 text-danger small" style="cursor:pointer">
                                        <input type="checkbox" name="remove_stamp" value="1" class="form-check-input">
                                        حذف الختم
                                    </label>
                                </div>
                            </div>
                            @endif
                            <input type="file" name="stamp_image" class="form-control" accept="image/*">
                            <div class="form-text">PNG بخلفية شفافة مفضل، بحد أقصى 2MB</div>
                        </div>

                        {{-- التوقيع --}}
                        <div class="col-md-6">
                            <label class="form-label">التوقيع الإلكتروني</label>
                            @if($tenant->signature_image)
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <div class="small text-success"><i class="fas fa-check-circle me-1"></i>يوجد توقيع محفوظ</div>
                                <label class="d-flex align-items-center gap-1 text-danger small ms-2" style="cursor:pointer">
                                    <input type="checkbox" name="remove_signature" value="1" class="form-check-input" id="removeSignature" onchange="toggleCanvas(this)">
                                    حذف التوقيع
                                </label>
                            </div>
                            @endif
                            <div class="border rounded bg-white" id="signatureWrap">
                                <canvas id="signatureCanvas" width="320" height="110" style="display:block;cursor:crosshair;touch-action:none;width:100%"></canvas>
                            </div>
                            <input type="hidden" name="signature_image" id="signatureData" value="{{ $tenant->signature_image }}">
                            <div class="d-flex gap-2 mt-1">
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="clearSignature()">
                                    <i class="fas fa-eraser me-1"></i>مسح ورسم جديد
                                </button>
                                <span class="text-muted small mt-1">ارسم توقيعك بالماوس</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== العمود الجانبي ===== --}}
        <div class="col-lg-4">

            {{-- الشعار --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header">
                    <h6 class="fw-bold mb-0"><i class="fas fa-image me-2 text-primary"></i>شعار الشركة</h6>
                </div>
                <div class="card-body text-center">
                    @if($tenant->logo)
                    <div class="position-relative d-inline-block mb-3">
                        <img src="{{ url('storage/'.$tenant->logo) }}" class="img-fluid rounded" style="max-height:90px;border:1px solid #e2e8f0;padding:8px;">
                    </div>
                    <div class="mb-3">
                        <label class="d-flex align-items-center justify-content-center gap-2 text-danger small" style="cursor:pointer">
                            <input type="checkbox" name="remove_logo" value="1" class="form-check-input">
                            حذف الشعار الحالي
                        </label>
                    </div>
                    @else
                    <div class="bg-light rounded p-4 mb-3 text-muted">
                        <i class="fas fa-image fa-2x"></i>
                        <div class="small mt-2">لا يوجد شعار</div>
                    </div>
                    @endif
                    <input type="file" name="logo" class="form-control form-control-sm" accept="image/*">
                    <div class="form-text">PNG أو JPG — بحد أقصى 2MB</div>
                </div>
            </div>

            {{-- معلومات الاشتراك --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header">
                    <h6 class="fw-bold mb-0"><i class="fas fa-crown me-2 text-warning"></i>الاشتراك</h6>
                </div>
                <div class="card-body">
                    @php $plans = ['free'=>'مجاني','basic'=>'أساسي','pro'=>'احترافي','enterprise'=>'مؤسسي'] @endphp
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">الخطة</span>
                        <span class="badge bg-primary">{{ $plans[$tenant->subscription_plan] ?? $tenant->subscription_plan }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">الحالة</span>
                        <span class="badge {{ $tenant->status === 'active' ? 'bg-success' : ($tenant->status === 'trial' ? 'bg-warning text-dark' : 'bg-danger') }}">
                            {{ $tenant->status === 'active' ? 'نشط' : ($tenant->status === 'trial' ? 'تجريبي' : 'موقوف') }}
                        </span>
                    </div>
                    @if($tenant->subscription_expires_at)
                    <div class="d-flex justify-content-between">
                        <span class="text-muted small">ينتهي في</span>
                        <span class="small">{{ $tenant->subscription_expires_at->format('Y-m-d') }}</span>
                    </div>
                    @endif
                </div>
            </div>

            <div class="d-grid">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i> حفظ الإعدادات
                </button>
            </div>
        </div>
    </div>
</form>

{{-- ===== طرق الدفع ===== --}}
@php $paymentMethods = \App\Models\PaymentMethod::where('tenant_id', $currentTenant->id)->orderBy('sort_order')->get(); @endphp
<div class="card border-0 shadow-sm mt-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="fw-bold mb-0"><i class="fas fa-credit-card me-2 text-primary"></i>طرق الدفع</h6>
        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addPaymentMethodModal">
            <i class="fas fa-plus me-1"></i> إضافة طريقة
        </button>
    </div>
    <div class="table-responsive">
        <table class="table mb-0 align-middle">
            <thead>
                <tr>
                    <th>الاسم</th>
                    <th>الرمز</th>
                    <th>الحالة</th>
                    <th>إجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($paymentMethods as $pm)
                <tr>
                    <td>
                        <form action="{{ route('payment-methods.update', $pm) }}" method="POST" class="d-flex gap-2 align-items-center">
                            @csrf @method('PUT')
                            <input type="text" name="name" value="{{ $pm->name }}" class="form-control form-control-sm" style="width:160px" required>
                            <button class="btn btn-xs btn-outline-primary" title="حفظ الاسم"><i class="fas fa-check"></i></button>
                        </form>
                    </td>
                    <td><code class="px-2 py-1 rounded" style="background:#f1f5f9;font-size:.8rem">{{ $pm->code }}</code></td>
                    <td>
                        <form action="{{ route('payment-methods.toggle', $pm) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-xs {{ $pm->is_active ? 'btn-success' : 'btn-outline-secondary' }}">
                                <i class="fas fa-{{ $pm->is_active ? 'check' : 'times' }} me-1"></i>
                                {{ $pm->is_active ? 'مفعّل' : 'معطّل' }}
                            </button>
                        </form>
                    </td>
                    <td>
                        <form action="{{ route('payment-methods.destroy', $pm) }}" method="POST" onsubmit="return confirm('حذف طريقة الدفع؟')">
                            @csrf @method('DELETE')
                            <button class="btn btn-xs btn-outline-danger"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4">
                        <div class="empty-state py-4">
                            <div class="empty-icon"><i class="fas fa-credit-card"></i></div>
                            <h5>لا توجد طرق دفع</h5>
                            <p>أضف طريقة دفع لتتمكن من تسجيل المدفوعات</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Modal إضافة طريقة دفع --}}
<div class="modal fade" id="addPaymentMethodModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-bold"><i class="fas fa-plus-circle me-2 text-primary"></i>إضافة طريقة دفع جديدة</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('payment-methods.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">الاسم <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="مثال: تحويل فوري" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">الرمز (Code) <span class="text-danger">*</span></label>
                        <input type="text" name="code" class="form-control" placeholder="مثال: instant_transfer" required>
                        <div class="form-text">حروف إنجليزية وأرقام وشرطة سفلية فقط — يُستخدم داخلياً</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> إضافة</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// ===== لوحة التوقيع =====
const canvas  = document.getElementById('signatureCanvas');
const ctx     = canvas ? canvas.getContext('2d') : null;
let drawing   = false;

if (ctx) {
    ctx.strokeStyle = '#1e293b';
    ctx.lineWidth   = 2;
    ctx.lineCap     = 'round';
    ctx.lineJoin    = 'round';

    const savedImg = '{{ $tenant->signature_image ?? "" }}';
    if (savedImg && savedImg.startsWith('data:')) {
        const img = new Image();
        img.onload = () => ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
        img.src = savedImg;
    }

    function getPos(e) {
        const r   = canvas.getBoundingClientRect();
        const src = e.touches ? e.touches[0] : e;
        const scaleX = canvas.width  / r.width;
        const scaleY = canvas.height / r.height;
        return { x: (src.clientX - r.left) * scaleX, y: (src.clientY - r.top) * scaleY };
    }

    canvas.addEventListener('mousedown',  e => { drawing=true; ctx.beginPath(); const p=getPos(e); ctx.moveTo(p.x,p.y); });
    canvas.addEventListener('mousemove',  e => { if(!drawing) return; const p=getPos(e); ctx.lineTo(p.x,p.y); ctx.stroke(); });
    canvas.addEventListener('mouseup',    () => { drawing=false; saveSignature(); });
    canvas.addEventListener('mouseleave', () => { drawing=false; });
    canvas.addEventListener('touchstart', e => { e.preventDefault(); drawing=true; ctx.beginPath(); const p=getPos(e); ctx.moveTo(p.x,p.y); }, {passive:false});
    canvas.addEventListener('touchmove',  e => { e.preventDefault(); if(!drawing) return; const p=getPos(e); ctx.lineTo(p.x,p.y); ctx.stroke(); }, {passive:false});
    canvas.addEventListener('touchend',   () => { drawing=false; saveSignature(); });
}

function saveSignature() {
    const el = document.getElementById('signatureData');
    if (el && canvas) el.value = canvas.toDataURL('image/png');
}

function clearSignature() {
    if (ctx) { ctx.clearRect(0, 0, canvas.width, canvas.height); }
    const el = document.getElementById('signatureData');
    if (el) el.value = '';
}

function toggleCanvas(cb) {
    const wrap = document.getElementById('signatureWrap');
    if (wrap) wrap.style.opacity = cb.checked ? '.4' : '1';
}
</script>
@endpush
