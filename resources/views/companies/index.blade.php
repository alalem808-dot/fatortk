@extends('layouts.app')
@section('title', 'الشركات والحسابات')
@section('page-title')
<h6 class="mb-0 fw-bold">الشركات والحسابات</h6>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <p class="text-muted mb-0">إدارة الشركات والحسابات المرتبطة بك</p>
    </div>
    <a href="{{ route('companies.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>إضافة شركة جديدة
    </a>
</div>

<div class="row g-3">
    @forelse($companies as $company)
    <div class="col-md-6 col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                @if($company->logo)
                    <img src="{{ asset('storage/'.$company->logo) }}" class="img-fluid rounded mb-3" style="max-height:80px;object-fit:contain">
                @else
                    <div class="bg-light rounded p-3 mb-3 text-center text-muted">
                        <i class="fas fa-building fa-2x"></i>
                    </div>
                @endif
                
                <h5 class="fw-bold mb-1">{{ $company->company_name }}</h5>
                <p class="text-muted small mb-2">{{ $company->email }}</p>
                
                <div class="mb-3">
                    <span class="badge bg-{{ $company->status === 'active' ? 'success' : ($company->status === 'trial' ? 'info' : 'danger') }}">
                        {{ $company->status === 'active' ? 'نشط' : ($company->status === 'trial' ? 'تجريبي' : 'موقوف') }}
                    </span>
                </div>
                
                <div class="d-grid gap-2">
                    <a href="{{ route('companies.edit', $company) }}" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-edit me-1"></i>تعديل
                    </a>
                    <form action="{{ route('companies.destroy', $company) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد؟')">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger w-100">
                            <i class="fas fa-trash me-1"></i>حذف
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                <h5 class="fw-bold mb-2">لا توجد شركات</h5>
                <p class="text-muted mb-3">ابدأ بإضافة شركة جديدة</p>
                <a href="{{ route('companies.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>إضافة شركة
                </a>
            </div>
        </div>
    </div>
    @endforelse
</div>
@endsection
