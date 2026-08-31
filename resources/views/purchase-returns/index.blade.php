@extends('layouts.app')
@section('title', 'مرتجعات المشتريات')
@section('page-title')<span>مرتجعات المشتريات</span>@endsection

@section('content')
<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>المرجع</th>
                    <th>أمر الشراء</th>
                    <th>المورد</th>
                    <th>التاريخ</th>
                    <th class="text-center">الإجمالي</th>
                    <th>السبب</th>
                    <th>إجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($returns as $return)
                <tr>
                    <td><code style="font-size:.8rem;background:#f1f5f9;padding:2px 6px;border-radius:5px">{{ $return->reference }}</code></td>
                    <td>
                        @if($return->purchase)
                        <a href="{{ route('purchases.show', $return->purchase) }}" class="fw-700 text-decoration-none" style="color:var(--primary)">
                            {{ $return->purchase->reference }}
                        </a>
                        @else <span class="text-muted small">—</span>
                        @endif
                    </td>
                    <td class="text-muted small">{{ $return->purchase->supplier_name ?? '—' }}</td>
                    <td class="text-muted small">{{ $return->return_date->format('Y-m-d') }}</td>
                    <td class="text-center fw-700 text-danger">{{ number_format($return->total, 2) }}</td>
                    <td class="text-muted small">{{ $return->reason ?? '—' }}</td>
                    <td>
                        @if($return->purchase)
                        <a href="{{ route('purchases.show', $return->purchase) }}" class="btn btn-xs btn-outline-secondary" title="عرض أمر الشراء">
                            <i class="fas fa-eye"></i>
                        </a>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7">
                        <div class="empty-state">
                            <div class="empty-icon"><i class="fas fa-rotate-left"></i></div>
                            <h5>لا توجد مرتجعات</h5>
                            <p>لم يتم تسجيل أي مرتجع مشتريات بعد</p>
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
