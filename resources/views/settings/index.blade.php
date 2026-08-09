@extends('layouts.app')
@section('title', 'الإعدادات')
@section('page-title')
<h6 class="mb-0 fw-bold">إعدادات الحساب</h6>
@endsection

@section('content')
<form action="{{ route('settings.update') }}" method="POST" enctype="multipart/form-data">
    @csrf @method('PUT')

    <div class="row g-3">
        <div class="col-md-8">
            {{-- بيانات الشركة --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white border-0 pt-3"><h6 class="fw-bold mb-0"><i class="fas fa-building me-2 text-primary"></i>بيانات الشركة</h6></div>
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
                <div class="card-header bg-white border-0 pt-3"><h6 class="fw-bold mb-0"><i class="fas fa-file-invoice me-2 text-primary"></i>إعدادات الفواتير</h6></div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">بادئة رقم الفاتورة</label>
                            <input type="text" name="invoice_prefix" class="form-control" value="{{ old('invoice_prefix', $tenant->getSetting('invoice_prefix', 'INV')) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">العملة الافتراضية</label>
                            <select name="default_currency" class="form-select">
                                <option value="SDG" {{ $tenant->getSetting('default_currency','SDG')=='SDG'?'selected':'' }}>جنيه سوداني (SDG)</option>
                                <option value="USD" {{ $tenant->getSetting('default_currency','SDG')=='USD'?'selected':'' }}>دولار (USD)</option>
                                <option value="SAR" {{ $tenant->getSetting('default_currency','SDG')=='SAR'?'selected':'' }}>ريال سعودي (SAR)</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">نسبة الضريبة الافتراضية %</label>
                            <input type="number" name="default_tax_rate" class="form-control" value="{{ old('default_tax_rate', $tenant->getSetting('default_tax_rate', 0)) }}" min="0" max="100" step="0.01">
                        </div>
                        <div class="col-12">
                            <label class="form-label">الشروط والأحكام الافتراضية</label>
                            <textarea name="terms_conditions" class="form-control" rows="3">{{ old('terms_conditions', $tenant->getSetting('terms_conditions')) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            {{-- التوقيع والختم --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white border-0 pt-3"><h6 class="fw-bold mb-0"><i class="fas fa-signature me-2 text-primary"></i>بيانات التوقيع والختم</h6></div>
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
                        <div class="col-md-6">
                            <label class="form-label">صورة الختم</label>
                            @if($tenant->stamp_image)
                                <div class="mb-2">
                                    <img src="{{ url('storage/'.$tenant->stamp_image) }}" style="max-height:80px" class="rounded border">
                                </div>
                            @endif
                            <input type="file" name="stamp_image" class="form-control" accept="image/*">
                            <div class="form-text">PNG بخلفية شفافة مفضل، بحد أقصى 2MB</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">التوقيع الإلكتروني</label>
                            <div class="border rounded bg-white mb-2" style="position:relative">
                                <canvas id="signatureCanvas" width="340" height="120" style="display:block;cursor:crosshair;touch-action:none"></canvas>
                                @if($tenant->signature_image)
                                <img id="savedSignature" src="{{ $tenant->signature_image }}" style="display:none">
                                @endif
                            </div>
                            <input type="hidden" name="signature_image" id="signatureData" value="{{ $tenant->signature_image }}">
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="clearSignature()"><i class="fas fa-eraser"></i> مسح</button>
                                <span class="text-muted small mt-1">ارسم توقيعك بالماوس أو اللمس</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            {{-- الشعار --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white border-0 pt-3"><h6 class="fw-bold mb-0"><i class="fas fa-image me-2 text-primary"></i>شعار الشركة</h6></div>
                <div class="card-body text-center">
                    @if($tenant->logo)
                        <img src="{{ url('storage/'.$tenant->logo) }}" class="img-fluid rounded mb-3" style="max-height:100px">
                    @else
                        <div class="bg-light rounded p-4 mb-3 text-muted"><i class="fas fa-image fa-2x"></i><div class="small mt-2">لا يوجد شعار</div></div>
                    @endif
                    <input type="file" name="logo" class="form-control form-control-sm" accept="image/*">
                    <div class="form-text">PNG أو JPG، بحد أقصى 2MB</div>
                </div>
            </div>

            {{-- معلومات الاشتراك --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white border-0 pt-3"><h6 class="fw-bold mb-0"><i class="fas fa-crown me-2 text-warning"></i>الاشتراك الحالي</h6></div>
                <div class="card-body">
                    @php $plans = ['free'=>'مجاني','basic'=>'أساسي','pro'=>'احترافي','enterprise'=>'مؤسسي'] @endphp
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">الخطة</span>
                        <span class="badge bg-primary">{{ $plans[$tenant->subscription_plan] ?? $tenant->subscription_plan }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">الحالة</span>
                        <span class="badge {{ $tenant->status === 'active' ? 'bg-success' : 'bg-warning text-dark' }}">
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
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> حفظ الإعدادات</button>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
const canvas = document.getElementById('signatureCanvas');
const ctx = canvas.getContext('2d');
let drawing = false;

ctx.strokeStyle = '#1e293b';
ctx.lineWidth = 2;
ctx.lineCap = 'round';
ctx.lineJoin = 'round';

const saved = document.getElementById('savedSignature');
if (saved) { const img = new Image(); img.onload = () => ctx.drawImage(img, 0, 0, canvas.width, canvas.height); img.src = saved.src; }

function getPos(e) {
    const r = canvas.getBoundingClientRect();
    const src = e.touches ? e.touches[0] : e;
    return { x: src.clientX - r.left, y: src.clientY - r.top };
}

canvas.addEventListener('mousedown',  e => { drawing=true; ctx.beginPath(); const p=getPos(e); ctx.moveTo(p.x,p.y); });
canvas.addEventListener('mousemove',  e => { if(!drawing) return; const p=getPos(e); ctx.lineTo(p.x,p.y); ctx.stroke(); });
canvas.addEventListener('mouseup',    () => { drawing=false; saveSignature(); });
canvas.addEventListener('mouseleave', () => { drawing=false; });
canvas.addEventListener('touchstart', e => { e.preventDefault(); drawing=true; ctx.beginPath(); const p=getPos(e); ctx.moveTo(p.x,p.y); }, {passive:false});
canvas.addEventListener('touchmove',  e => { e.preventDefault(); if(!drawing) return; const p=getPos(e); ctx.lineTo(p.x,p.y); ctx.stroke(); }, {passive:false});
canvas.addEventListener('touchend',   () => { drawing=false; saveSignature(); });

function saveSignature() {
    document.getElementById('signatureData').value = canvas.toDataURL('image/png');
}
function clearSignature() {
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    document.getElementById('signatureData').value = '';
}
</script>
@endpush
