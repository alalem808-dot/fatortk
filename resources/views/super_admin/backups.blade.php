@extends('super_admin.layout')
@section('title', 'النسخ الاحتياطي')
@section('page-title')<h6 class="mb-0 fw-bold">النسخ الاحتياطي</h6>@endsection

@section('content')
<div class="row g-3 mb-4">
    {{-- نسخة كاملة --}}
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center p-4">
                <div class="mb-3" style="font-size:2.5rem">🗄️</div>
                <h6 class="fw-bold">نسخة كاملة للنظام</h6>
                <p class="text-muted small mb-3">تشمل قاعدة البيانات بالكامل لجميع المشتركين.</p>
                <form action="{{ route('super_admin.backups.full') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-danger w-100">
                        <i class="fas fa-database me-2"></i>نسخ النظام كاملاً
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- نسخة مشترك محدد --}}
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-4">
                <div class="text-center mb-3" style="font-size:2.5rem">👤</div>
                <h6 class="fw-bold text-center">نسخة مشترك محدد</h6>
                <form action="{{ route('super_admin.backups.tenant') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <select name="tenant_id" class="form-select" required>
                            <option value="">اختر المشترك...</option>
                            @foreach($tenants as $t)
                            <option value="{{ $t->id }}">{{ $t->company_name }} ({{ $t->subdomain }})</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-warning w-100">
                        <i class="fas fa-user-shield me-2"></i>نسخ بيانات المشترك
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- إحصائيات --}}
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-3"><i class="fas fa-chart-bar text-primary me-2"></i>إحصائيات</h6>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted small">إجمالي النسخ</span>
                    <strong>{{ $stats['total'] }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted small">نسخ كاملة</span>
                    <strong>{{ $stats['full'] }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted small">نسخ المشتركين</span>
                    <strong>{{ $stats['tenant'] }}</strong>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted small">الحجم الكلي</span>
                    <strong>{{ number_format($stats['total_size'] / 1024 / 1024, 2) }} MB</strong>
                </div>
                <hr>
                <p class="text-muted small mb-0">
                    <i class="fas fa-clock me-1"></i>
                    النسخ الروتيني: يومياً 2:00 ص (كامل) + أسبوعياً 3:00 ص (مشتركين)
                </p>
            </div>
        </div>
    </div>
</div>

{{-- سجل النسخ --}}
<div class="card border-0 shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="fw-bold mb-0"><i class="fas fa-history me-2"></i>سجل النسخ الاحتياطية</h6>
        <form action="{{ route('super_admin.backups.prune') }}" method="POST" onsubmit="return confirm('حذف النسخ الأقدم من 30 يوم؟')">
            @csrf
            <button class="btn btn-sm btn-outline-danger">
                <i class="fas fa-broom me-1"></i>حذف القديمة (+30 يوم)
            </button>
        </form>
    </div>
    <div class="table-responsive">
        <table class="table mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>النوع</th>
                    <th>المشترك</th>
                    <th>اسم الملف</th>
                    <th>الحجم</th>
                    <th>التاريخ</th>
                    <th>إجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                <tr>
                    <td>
                        @if($log->type === 'full')
                            <span class="badge bg-danger">كامل</span>
                        @else
                            <span class="badge bg-primary">مشترك</span>
                        @endif
                    </td>
                    <td class="small">{{ $log->company_name ?? '—' }}</td>
                    <td class="small text-muted">{{ $log->filename }}</td>
                    <td class="small text-muted">{{ number_format($log->size / 1024, 1) }} KB</td>
                    <td class="small text-muted">{{ \Carbon\Carbon::parse($log->created_at)->format('Y-m-d H:i') }}</td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('super_admin.backups.download', $log->id) }}" class="btn btn-xs btn-outline-primary">
                                <i class="fas fa-download"></i>
                            </a>
                            <form action="{{ route('super_admin.backups.destroy', $log->id) }}" method="POST" onsubmit="return confirm('حذف هذه النسخة؟')">
                                @csrf @method('DELETE')
                                <button class="btn btn-xs btn-outline-danger"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">لا توجد نسخ احتياطية بعد</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($logs->hasPages())
    <div class="card-footer">{{ $logs->links() }}</div>
    @endif
</div>
@endsection
