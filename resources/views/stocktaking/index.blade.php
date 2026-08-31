@extends('layouts.app')
@section('title', 'الجرد الدوري')
@section('page-title')<span>جلسات الجرد الدوري</span>@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div class="text-muted small">{{ $sessions->total() }} جلسة مسجّلة</div>
    @can('stocktaking.create')
    <a href="{{ route('stocktaking.create') }}" class="btn btn-primary btn-sm">
        <i class="fas fa-plus me-1"></i> جلسة جرد جديدة
    </a>
    @endcan
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>اسم الجلسة</th>
                    <th>المخزن</th>
                    <th>التاريخ</th>
                    <th class="text-center">عدد الأصناف</th>
                    <th class="text-center">الحالة</th>
                    <th>إجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sessions as $s)
                <tr>
                    <td>
                        <a href="{{ route('stocktaking.show', $s) }}" class="fw-700 text-decoration-none" style="color:var(--primary)">
                            {{ $s->name }}
                        </a>
                        @if($s->notes)
                        <div class="text-muted" style="font-size:.72rem">{{ Str::limit($s->notes, 40) }}</div>
                        @endif
                    </td>
                    <td>
                        @if($s->warehouse)
                        <span class="badge" style="background:var(--primary-light);color:var(--primary)">
                            <i class="fas fa-warehouse me-1"></i>{{ $s->warehouse->name }}
                        </span>
                        @else
                        <span class="text-muted small">—</span>
                        @endif
                    </td>
                    <td class="text-muted small">{{ $s->date->format('Y-m-d') }}</td>
                    <td class="text-center">
                        <span class="badge bg-light text-dark">{{ $s->items_count }}</span>
                    </td>
                    <td class="text-center">
                        <span class="badge {{ $s->status === 'confirmed' ? 'bg-success' : 'bg-warning text-dark' }}">
                            {{ $s->status === 'confirmed' ? 'مؤكد ✓' : 'مسودة' }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('stocktaking.show', $s) }}" class="btn btn-xs btn-outline-{{ $s->status === 'draft' ? 'primary' : 'secondary' }}" title="{{ $s->status === 'draft' ? 'متابعة الجرد' : 'عرض' }}">
                            <i class="fas fa-{{ $s->status === 'draft' ? 'clipboard-check' : 'eye' }}"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6">
                        <div class="empty-state">
                            <div class="empty-icon"><i class="fas fa-clipboard-list"></i></div>
                            <h5>لا توجد جلسات جرد</h5>
                            <p>ابدأ بجلسة جرد لمراجعة مخزونك</p>
                            @can('stocktaking.create')
                            <a href="{{ route('stocktaking.create') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus me-1"></i> جلسة جرد جديدة
                            </a>
                            @endcan
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($sessions->hasPages())
    <div class="card-footer bg-white d-flex justify-content-center py-2">
        {{ $sessions->links() }}
    </div>
    @endif
</div>
@endsection
