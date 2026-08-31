@extends('super_admin.layout')
@section('title', 'تذاكر الدعم')
@section('page-title')<h6 class="mb-0 fw-bold">نظام الدعم الفني</h6>@endsection

@section('content')
{{-- إحصائيات --}}
<div class="row g-3 mb-4">
    @foreach([
        ['label'=>'مفتوحة', 'value'=>$stats['open'], 'color'=>'danger'],
        ['label'=>'تم الرد', 'value'=>$stats['replied'], 'color'=>'warning'],
        ['label'=>'مغلقة', 'value'=>$stats['closed'], 'color'=>'success'],
        ['label'=>'عاجلة', 'value'=>$stats['urgent'], 'color'=>'dark'],
    ] as $s)
    <div class="col-md-3">
        <div class="stat-card text-center">
            <div class="fw-bold fs-3 text-{{ $s['color'] }}">{{ $s['value'] }}</div>
            <div class="text-muted small">{{ $s['label'] }}</div>
        </div>
    </div>
    @endforeach
</div>

<div class="row g-3">
    <div class="col-lg-8">
        {{-- فلاتر --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body py-2">
                <form method="GET" class="row g-2">
                    <div class="col-auto">
                        <select name="status" class="form-select form-select-sm">
                            <option value="">كل الحالات</option>
                            <option value="open" {{ request('status')=='open'?'selected':'' }}>مفتوح</option>
                            <option value="replied" {{ request('status')=='replied'?'selected':'' }}>تم الرد</option>
                            <option value="closed" {{ request('status')=='closed'?'selected':'' }}>مغلق</option>
                        </select>
                    </div>
                    <div class="col-auto">
                        <select name="priority" class="form-select form-select-sm">
                            <option value="">كل الأولويات</option>
                            <option value="urgent" {{ request('priority')=='urgent'?'selected':'' }}>عاجل</option>
                            <option value="high" {{ request('priority')=='high'?'selected':'' }}>عالي</option>
                            <option value="normal" {{ request('priority')=='normal'?'selected':'' }}>عادي</option>
                            <option value="low" {{ request('priority')=='low'?'selected':'' }}>منخفض</option>
                        </select>
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-sm btn-primary">بحث</button>
                        <a href="{{ route('super_admin.support.index') }}" class="btn btn-sm btn-outline-secondary">إعادة</a>
                    </div>
                </form>
            </div>
        </div>

        {{-- قائمة التذاكر --}}
        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table mb-0 align-middle">
                    <thead class="table-light">
                        <tr><th>الموضوع</th><th>الشركة</th><th>الأولوية</th><th>الحالة</th><th>التاريخ</th><th></th></tr>
                    </thead>
                    <tbody>
                        @forelse($tickets as $ticket)
                        <tr>
                            <td>
                                <a href="{{ route('super_admin.support.show', $ticket) }}" class="fw-semibold text-decoration-none small">
                                    {{ Str::limit($ticket->subject, 50) }}
                                </a>
                            </td>
                            <td class="small text-muted">{{ $ticket->tenant->company_name }}</td>
                            <td>
                                @php $pColors = ['urgent'=>'danger','high'=>'warning','normal'=>'secondary','low'=>'light text-muted']; @endphp
                                <span class="badge bg-{{ $pColors[$ticket->priority] ?? 'secondary' }}">
                                    {{ ['urgent'=>'عاجل','high'=>'عالي','normal'=>'عادي','low'=>'منخفض'][$ticket->priority] }}
                                </span>
                            </td>
                            <td>
                                @php $sColors = ['open'=>'danger','replied'=>'warning','closed'=>'success']; @endphp
                                <span class="badge bg-{{ $sColors[$ticket->status] ?? 'secondary' }}">
                                    {{ ['open'=>'مفتوح','replied'=>'تم الرد','closed'=>'مغلق'][$ticket->status] }}
                                </span>
                            </td>
                            <td class="small text-muted">{{ $ticket->created_at->format('Y-m-d') }}</td>
                            <td>
                                <a href="{{ route('super_admin.support.show', $ticket) }}" class="btn btn-xs btn-outline-primary">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">لا توجد تذاكر</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($tickets->hasPages())
            <div class="card-footer bg-white">{{ $tickets->withQueryString()->links() }}</div>
            @endif
        </div>
    </div>

    {{-- إنشاء تذكرة جديدة --}}
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 pt-3">
                <h6 class="fw-bold mb-0"><i class="fas fa-plus-circle text-primary me-2"></i>تذكرة جديدة</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('super_admin.support.store') }}" method="POST">
                    @csrf
                    <div class="mb-2">
                        <label class="form-label small">الشركة</label>
                        <select name="tenant_id" class="form-select form-select-sm" required>
                            <option value="">اختر المشترك...</option>
                            @foreach(\App\Models\Tenant::orderBy('company_name')->get() as $t)
                            <option value="{{ $t->id }}">{{ $t->company_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">الموضوع</label>
                        <input type="text" name="subject" class="form-control form-control-sm" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">الأولوية</label>
                        <select name="priority" class="form-select form-select-sm">
                            <option value="normal">عادي</option>
                            <option value="high">عالي</option>
                            <option value="urgent">عاجل</option>
                            <option value="low">منخفض</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">الرسالة</label>
                        <textarea name="message" class="form-control form-control-sm" rows="4" required></textarea>
                    </div>
                    <button class="btn btn-primary btn-sm w-100">إنشاء التذكرة</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
