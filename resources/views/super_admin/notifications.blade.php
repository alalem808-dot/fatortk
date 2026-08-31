@extends('super_admin.layout')
@section('title', 'الإشعارات والتنبيهات')
@section('page-title')<h6 class="mb-0 fw-bold">الإشعارات والتنبيهات</h6>@endsection

@section('content')
<div class="row g-3 mb-4">
    {{-- بطاقات التنبيه --}}
    @foreach([
        ['label'=>'تنتهي خلال 7 أيام', 'count'=>$expiringIn7->count(), 'color'=>'danger', 'icon'=>'fa-fire'],
        ['label'=>'تنتهي خلال 30 يوم', 'count'=>$expiringIn30->count(), 'color'=>'warning', 'icon'=>'fa-clock'],
        ['label'=>'منتهية الاشتراك', 'count'=>$expired->count(), 'color'=>'secondary', 'icon'=>'fa-ban'],
        ['label'=>'تجريبي +14 يوم', 'count'=>$oldTrials->count(), 'color'=>'info', 'icon'=>'fa-hourglass'],
        ['label'=>'غير نشطة +60 يوم', 'count'=>$inactiveAccounts->count(), 'color'=>'dark', 'icon'=>'fa-moon'],
    ] as $alert)
    <div class="col">
        <div class="stat-card text-center">
            <div class="mb-1" style="font-size:1.5rem;color:var(--bs-{{ $alert['color'] }})">
                <i class="fas {{ $alert['icon'] }}"></i>
            </div>
            <div class="fw-bold fs-4">{{ $alert['count'] }}</div>
            <div class="text-muted small">{{ $alert['label'] }}</div>
        </div>
    </div>
    @endforeach
</div>

<div class="row g-3">
    {{-- قوائم التنبيهات --}}
    <div class="col-lg-8">
        @foreach([
            ['title'=>'🔴 تنتهي خلال 7 أيام', 'data'=>$expiringIn7, 'badge'=>'danger'],
            ['title'=>'🟡 تنتهي خلال 30 يوم', 'data'=>$expiringIn30, 'badge'=>'warning'],
            ['title'=>'⛔ منتهية الاشتراك', 'data'=>$expired, 'badge'=>'secondary'],
            ['title'=>'⏳ تجريبي أكثر من 14 يوم', 'data'=>$oldTrials, 'badge'=>'info'],
            ['title'=>'💤 غير نشطة أكثر من 60 يوم', 'data'=>$inactiveAccounts, 'badge'=>'dark'],
        ] as $group)
        @if($group['data']->count())
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white border-0 pt-3 d-flex justify-content-between">
                <h6 class="fw-bold mb-0">{{ $group['title'] }}</h6>
                <span class="badge bg-{{ $group['badge'] }}">{{ $group['data']->count() }}</span>
            </div>
            <div class="table-responsive">
                <table class="table mb-0 align-middle">
                    <thead class="table-light">
                        <tr><th>الشركة</th><th>البريد</th><th>انتهاء الاشتراك</th><th>تمديد سريع</th></tr>
                    </thead>
                    <tbody>
                        @foreach($group['data'] as $t)
                        <tr>
                            <td class="fw-semibold small">{{ $t->company_name }}</td>
                            <td class="text-muted small">{{ $t->email }}</td>
                            <td class="small">{{ $t->subscription_expires_at?->format('Y-m-d') ?? '—' }}</td>
                            <td>
                                <form action="{{ route('super_admin.notifications.extend', $t) }}" method="POST" class="d-flex gap-1">
                                    @csrf
                                    <select name="months" class="form-select form-select-sm" style="width:90px">
                                        <option value="1">1 شهر</option>
                                        <option value="3">3 أشهر</option>
                                        <option value="6">6 أشهر</option>
                                        <option value="12" selected>12 شهر</option>
                                    </select>
                                    <button class="btn btn-xs btn-success"><i class="fas fa-plus"></i></button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
        @endforeach
    </div>

    {{-- إرسال إشعار --}}
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 pt-3">
                <h6 class="fw-bold mb-0"><i class="fas fa-envelope text-primary me-2"></i>إرسال إشعار بريدي</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('super_admin.notifications.send') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">المستقبل</label>
                        <select name="recipient" class="form-select" id="recipientSelect">
                            <option value="all">جميع المشتركين</option>
                            <option value="active">النشطون فقط</option>
                            <option value="trial">التجريبيون فقط</option>
                            <option value="single">مشترك واحد</option>
                        </select>
                    </div>
                    <div class="mb-3" id="singleTenantDiv" style="display:none">
                        <label class="form-label">اختر المشترك</label>
                        <select name="tenant_id" class="form-select">
                            <option value="">—</option>
                            @foreach($tenants as $t)
                            <option value="{{ $t->id }}">{{ $t->company_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">عنوان الرسالة</label>
                        <input type="text" name="subject" class="form-control" required placeholder="مثال: تجديد الاشتراك">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">نص الرسالة</label>
                        <textarea name="message" class="form-control" rows="5" required placeholder="اكتب رسالتك هنا..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-paper-plane me-2"></i>إرسال الإشعار
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('recipientSelect').addEventListener('change', function() {
    document.getElementById('singleTenantDiv').style.display = this.value === 'single' ? 'block' : 'none';
});
</script>
@endpush
