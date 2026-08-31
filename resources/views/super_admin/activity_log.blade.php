@extends('super_admin.layout')
@section('title', 'سجل النشاط')
@section('page-title')<h6 class="mb-0 fw-bold">سجل النشاط</h6>@endsection

@section('content')
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-center">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control form-control-sm"
                    placeholder="بحث في الوصف..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <select name="action" class="form-select form-select-sm">
                    <option value="">كل الأحداث</option>
                    @foreach($actions as $action)
                    <option value="{{ $action }}" {{ request('action') == $action ? 'selected' : '' }}>{{ $action }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button class="btn btn-sm btn-primary">بحث</button>
                <a href="{{ route('super_admin.activity_log') }}" class="btn btn-sm btn-outline-secondary">إعادة</a>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table mb-0 align-middle">
            <thead class="table-light">
                <tr><th>الحدث</th><th>الوصف</th><th>الموضوع</th><th>المنفّذ</th><th>التاريخ</th></tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                <tr>
                    <td>
                        @php
                        $colors = [
                            'created' => 'success', 'deleted' => 'danger', 'plan_changed' => 'warning',
                            'status_changed' => 'info', 'payment_recorded' => 'success',
                            'payment_deleted' => 'danger', 'subscription_extended' => 'primary',
                            'notification_sent' => 'info', 'ticket_replied' => 'secondary',
                        ];
                        $color = $colors[$log->action] ?? 'secondary';
                        @endphp
                        <span class="badge bg-{{ $color }}">{{ $log->action }}</span>
                    </td>
                    <td class="small">{{ $log->description }}</td>
                    <td class="small text-muted">{{ $log->subject_type }} #{{ $log->subject_id }}</td>
                    <td class="small">{{ $log->performed_by ?? '—' }}</td>
                    <td class="small text-muted">{{ \Carbon\Carbon::parse($log->created_at)->format('Y-m-d H:i') }}</td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center text-muted py-4">لا توجد سجلات</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($logs->hasPages())
    <div class="card-footer bg-white">{{ $logs->withQueryString()->links() }}</div>
    @endif
</div>
@endsection
