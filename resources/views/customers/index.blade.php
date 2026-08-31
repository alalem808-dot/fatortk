@extends('layouts.app')
@section('title', 'العملاء')
@section('page-title')<span>العملاء</span>@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <form class="d-flex gap-2 flex-wrap" method="GET">
        <div class="input-group input-group-sm" style="width:250px">
            <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
            <input type="text" name="search" class="form-control border-start-0"
                   placeholder="بحث بالاسم أو البريد..." value="{{ request('search') }}">
        </div>
        <button class="btn btn-sm btn-outline-secondary">بحث</button>
        @if(request('search'))
        <a href="{{ route('customers.index') }}" class="btn btn-sm btn-outline-danger"><i class="fas fa-times"></i></a>
        @endif
    </form>
    @can('customers.create')
    <a href="{{ route('customers.create') }}" class="btn btn-primary btn-sm">
        <i class="fas fa-plus me-1"></i> عميل جديد
    </a>
    @endcan
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>الاسم</th>
                    <th>البريد الإلكتروني</th>
                    <th>الهاتف</th>
                    <th class="text-center">واتساب</th>
                    <th class="text-center">الفواتير</th>
                    <th>إجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($customers as $customer)
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div style="width:34px;height:34px;border-radius:9px;background:var(--primary-light);color:var(--primary);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.85rem;flex-shrink:0">
                                {{ Str::upper(Str::substr($customer->name, 0, 1)) }}
                            </div>
                            <div>
                                <a href="{{ route('customers.show', $customer) }}" class="fw-700 text-decoration-none" style="color:var(--primary)">
                                    {{ $customer->name }}
                                </a>
                                @if($customer->city)
                                <div class="text-muted" style="font-size:.72rem">
                                    <i class="fas fa-map-marker-alt me-1"></i>{{ $customer->city }}
                                </div>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="text-muted small">{{ $customer->email ?? '—' }}</td>
                    <td class="text-muted small">{{ $customer->phone ?? '—' }}</td>
                    <td class="text-center">
                        @if($customer->whatsapp_number)
                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $customer->whatsapp_number) }}"
                           target="_blank" class="btn btn-xs btn-whatsapp">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                        @else
                        <span class="text-muted small">—</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <span class="badge" style="background:var(--primary-light);color:var(--primary)">
                            {{ $customer->invoices_count ?? 0 }}
                        </span>
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('customers.show', $customer) }}" class="btn btn-xs btn-outline-secondary" title="عرض">
                                <i class="fas fa-eye"></i>
                            </a>
                            @can('customers.edit')
                            <a href="{{ route('customers.edit', $customer) }}" class="btn btn-xs btn-outline-primary" title="تعديل">
                                <i class="fas fa-pen"></i>
                            </a>
                            @endcan
                            @can('customers.delete')
                            <form action="{{ route('customers.destroy', $customer) }}" method="POST"
                                  onsubmit="return confirm('حذف العميل {{ $customer->name }}؟')">
                                @csrf @method('DELETE')
                                <button class="btn btn-xs btn-outline-danger"><i class="fas fa-trash"></i></button>
                            </form>
                            @endcan
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6">
                        <div class="empty-state">
                            <div class="empty-icon"><i class="fas fa-users"></i></div>
                            <h5>لا يوجد عملاء</h5>
                            <p>{{ request('search') ? 'لا توجد نتائج مطابقة' : 'ابدأ بإضافة عملائك' }}</p>
                            @if(!request('search'))
                            @can('customers.create')
                            <a href="{{ route('customers.create') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus me-1"></i> عميل جديد
                            </a>
                            @endcan
                            @endif
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($customers->hasPages())
    <div class="card-footer bg-white d-flex justify-content-center py-2">
        {{ $customers->withQueryString()->links() }}
    </div>
    @endif
</div>
@endsection
