@extends('layouts.app')
@section('title', 'قوالب الفواتير')
@section('page-title')
<h6 class="mb-0 fw-bold">قوالب الفواتير</h6>
@endsection

@section('content')
<div class="d-flex justify-content-end mb-3">
    <a href="{{ route('templates.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> قالب جديد</a>
</div>

<div class="row g-3">
    @forelse($templates as $template)
    <div class="col-md-4">
        <div class="card border-0 shadow-sm {{ $template->is_default ? 'border-primary border-2' : '' }}">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h6 class="fw-bold mb-1">{{ $template->name }}</h6>
                        @if($template->is_default)
                            <span class="badge bg-primary">افتراضي</span>
                        @endif
                    </div>
                    <div class="d-flex gap-1">
                        <a href="{{ route('templates.edit', $template) }}" class="btn btn-xs btn-outline-primary"><i class="fas fa-edit"></i></a>
                        @if(!$template->is_default)
                        <form action="{{ route('templates.destroy', $template) }}" method="POST" onsubmit="return confirm('حذف القالب؟')">
                            @csrf @method('DELETE')
                            <button class="btn btn-xs btn-outline-danger"><i class="fas fa-trash"></i></button>
                        </form>
                        @endif
                    </div>
                </div>
                {{-- معاينة الألوان --}}
                <div class="d-flex gap-2 mb-3">
                    <div class="rounded" style="width:24px;height:24px;background:{{ $template->primary_color }}" title="اللون الرئيسي"></div>
                    <div class="rounded" style="width:24px;height:24px;background:{{ $template->secondary_color }}" title="اللون الثانوي"></div>
                    <span class="small text-muted">{{ $template->font_family }}</span>
                </div>
                <div class="d-flex flex-wrap gap-1">
                    @if($template->show_logo)<span class="badge bg-light text-dark">شعار</span>@endif
                    @if($template->show_tax)<span class="badge bg-light text-dark">ضريبة</span>@endif
                    @if($template->show_discount)<span class="badge bg-light text-dark">خصم</span>@endif
                    @if($template->show_notes)<span class="badge bg-light text-dark">ملاحظات</span>@endif
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12"><div class="text-center text-muted py-5">لا توجد قوالب</div></div>
    @endforelse
</div>
@endsection
