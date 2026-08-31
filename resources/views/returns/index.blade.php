@extends('layouts.app')
@section('title', 'مرتجعات المبيعات')
@section('page-title')<span>مرتجعات المبيعات</span>@endsection

@section('content')
<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>رقم الفاتورة</th>
                    <th>العميل</th>
                    <th>تاريخ المرتجع</th>
                    <th class="text-center">الإجمالي</th>
                    <th>السبب</th>
                    <th>إجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($returns as $r)
                <tr>
                    <td>
                        <a href="{{ route('invoices.show', $r->invoice) }}" class="fw-700 text-decoration-none" style="color:var(--primary)">
                            {{ $r->invoice->invoice_number }}
                        </a>
                    </td>
                    <td class="fw-600">{{ $r->invoice->customer->name ?? '—' }}</td>
                    <td class="text-muted small">{{ \Carbon\Carbon::parse($r->return_date)->format('Y-m-d') }}</td>
                    <td class="text-center">
                        <span class="fw-700 text-danger">{{ number_format($r->total, 2) }}</span>
                    </td>
                    <td class="text-muted small">{{ $r->reason ?? '—' }}</td>
                    <td>
                        <a href="{{ route('invoices.show', $r->invoice) }}" class="btn btn-xs btn-outline-secondary" title="عرض الفاتورة">
                            <i class="fas fa-eye"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6">
                        <div class="empty-state">
                            <div class="empty-icon"><i class="fas fa-undo"></i></div>
                            <h5>لا توجد مرتجعات</h5>
                            <p>لم يتم تسجيل أي مرتجع بعد</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($returns->hasPages())
    <div class="card-footer bg-white d-flex justify-content-center py-2">
        {{ $returns->links() }}
    </div>
    @endif
</div>
@endsection
