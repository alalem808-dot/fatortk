@extends('layouts.app')
@section('title', 'تقرير العملاء')
@section('page-title')
<h6 class="mb-0 fw-bold">تقرير العملاء</h6>
@endsection

@section('content')
<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr><th>العميل</th><th>عدد الفواتير</th><th>إجمالي الفواتير</th><th>المدفوع</th><th>المتبقي</th><th>نسبة السداد</th><th></th></tr>
            </thead>
            <tbody>
                @forelse($customers as $customer)
                @php
                    $total   = $customer->invoices_sum_total_amount ?? 0;
                    $paid    = $customer->invoices_sum_paid_amount  ?? 0;
                    $due     = $total - $paid;
                    $percent = $total > 0 ? round(($paid / $total) * 100) : 0;
                @endphp
                <tr>
                    <td>
                        <a href="{{ route('customers.show', $customer) }}" class="fw-semibold text-decoration-none">{{ $customer->name }}</a>
                        @if($customer->phone)<div class="text-muted" style="font-size:.75rem">{{ $customer->phone }}</div>@endif
                    </td>
                    <td><span class="badge bg-light text-dark">{{ $customer->invoices_count }}</span></td>
                    <td>{{ number_format($total, 2) }} SDG</td>
                    <td class="text-success">{{ number_format($paid, 2) }} SDG</td>
                    <td class="{{ $due > 0 ? 'text-danger fw-semibold' : 'text-success' }}">{{ number_format($due, 2) }} SDG</td>
                    <td style="min-width:120px">
                        <div class="d-flex align-items-center gap-2">
                            <div class="progress flex-grow-1" style="height:6px">
                                <div class="progress-bar {{ $percent >= 100 ? 'bg-success' : ($percent >= 50 ? 'bg-warning' : 'bg-danger') }}" style="width:{{ $percent }}%"></div>
                            </div>
                            <span class="small">{{ $percent }}%</span>
                        </div>
                    </td>
                    <td>
                        @if($customer->whatsapp_number)
                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $customer->whatsapp_number) }}" target="_blank" class="btn btn-xs btn-whatsapp"><i class="fab fa-whatsapp"></i></a>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted py-5">لا يوجد عملاء</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
