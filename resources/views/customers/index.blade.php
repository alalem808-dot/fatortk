@extends('layouts.app')
@section('title', 'العملاء')
@section('page-title')
<h6 class="mb-0 fw-bold">العملاء</h6>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <form class="d-flex gap-2" method="GET">
        <input type="text" name="search" class="form-control form-control-sm" placeholder="بحث بالاسم أو البريد..." value="{{ request('search') }}" style="width:250px">
        <button class="btn btn-sm btn-outline-secondary">بحث</button>
    </form>
    <a href="{{ route('customers.create') }}" class="btn btn-primary btn-sm">
        <i class="fas fa-plus"></i> عميل جديد
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr><th>الاسم</th><th>البريد</th><th>الهاتف</th><th>واتساب</th><th>عدد الفواتير</th><th>إجراءات</th></tr>
            </thead>
            <tbody>
                @forelse($customers as $customer)
                <tr>
                    <td>
                        <a href="{{ route('customers.show', $customer) }}" class="fw-semibold text-decoration-none">{{ $customer->name }}</a>
                        @if($customer->city)<div class="text-muted" style="font-size:.75rem">{{ $customer->city }}</div>@endif
                    </td>
                    <td class="text-muted small">{{ $customer->email ?? '-' }}</td>
                    <td class="text-muted small">{{ $customer->phone ?? '-' }}</td>
                    <td>
                        @if($customer->whatsapp_number)
                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $customer->whatsapp_number) }}" target="_blank" class="btn btn-xs btn-whatsapp">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                        @else
                        <span class="text-muted small">-</span>
                        @endif
                    </td>
                    <td><span class="badge bg-light text-dark">{{ $customer->invoices_count }}</span></td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('customers.show', $customer) }}" class="btn btn-xs btn-outline-secondary"><i class="fas fa-eye"></i></a>
                            <a href="{{ route('customers.edit', $customer) }}" class="btn btn-xs btn-outline-primary"><i class="fas fa-edit"></i></a>
                            <form action="{{ route('customers.destroy', $customer) }}" method="POST" onsubmit="return confirm('حذف العميل؟')">
                                @csrf @method('DELETE')
                                <button class="btn btn-xs btn-outline-danger"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-5">لا يوجد عملاء</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white">{{ $customers->withQueryString()->links() }}</div>
</div>
@endsection
