@extends('layouts.app')
@section('title', 'الموردون')
@section('page-title')<span>الموردون</span>@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <form class="d-flex gap-2" method="GET">
        <div class="input-group input-group-sm" style="width:240px">
            <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
            <input type="text" name="search" class="form-control border-start-0"
                   placeholder="بحث بالاسم..." value="{{ request('search') }}">
        </div>
        <button class="btn btn-sm btn-outline-secondary">بحث</button>
    </form>
    @can('suppliers.create')
    <a href="{{ route('suppliers.create') }}" class="btn btn-primary btn-sm">
        <i class="fas fa-plus me-1"></i> مورد جديد
    </a>
    @endcan
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>الاسم</th>
                    <th>الهاتف</th>
                    <th>البريد الإلكتروني</th>
                    <th>العنوان</th>
                    <th class="text-center">المشتريات</th>
                    <th>إجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($suppliers as $s)
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div style="width:34px;height:34px;border-radius:9px;background:#f0fdf4;color:#16a34a;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.85rem;flex-shrink:0">
                                {{ Str::upper(Str::substr($s->name, 0, 1)) }}
                            </div>
                            <a href="{{ route('suppliers.show', $s) }}" class="fw-700 text-decoration-none" style="color:var(--primary)">
                                {{ $s->name }}
                            </a>
                        </div>
                    </td>
                    <td class="text-muted small">{{ $s->phone ?? '—' }}</td>
                    <td class="text-muted small">{{ $s->email ?? '—' }}</td>
                    <td class="text-muted small">{{ Str::limit($s->address ?? '—', 30) }}</td>
                    <td class="text-center">
                        <span class="badge" style="background:#f0fdf4;color:#16a34a">
                            {{ $s->purchases_count ?? 0 }}
                        </span>
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('suppliers.show', $s) }}" class="btn btn-xs btn-outline-secondary" title="عرض">
                                <i class="fas fa-eye"></i>
                            </a>
                            @can('suppliers.edit')
                            <a href="{{ route('suppliers.edit', $s) }}" class="btn btn-xs btn-outline-primary" title="تعديل">
                                <i class="fas fa-pen"></i>
                            </a>
                            @endcan
                            @can('suppliers.delete')
                            <form action="{{ route('suppliers.destroy', $s) }}" method="POST"
                                  onsubmit="return confirm('حذف المورد {{ $s->name }}؟')">
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
                            <div class="empty-icon"><i class="fas fa-truck"></i></div>
                            <h5>لا يوجد موردون</h5>
                            <p>ابدأ بإضافة موردينك</p>
                            @can('suppliers.create')
                            <a href="{{ route('suppliers.create') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus me-1"></i> مورد جديد
                            </a>
                            @endcan
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if(method_exists($suppliers, 'hasPages') && $suppliers->hasPages())
    <div class="card-footer bg-white d-flex justify-content-center py-2">
        {{ $suppliers->withQueryString()->links() }}
    </div>
    @endif
</div>
@endsection
