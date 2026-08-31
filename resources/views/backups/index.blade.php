@extends('layouts.app')
@section('title', 'النسخ الاحتياطي')
@section('page-title')<span>النسخ الاحتياطي</span>@endsection

@section('content')
<div class="row g-3">
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center p-4">
                <div class="mb-3" style="font-size:3rem">🗄️</div>
                <h6 class="fw-bold mb-2">نسخة احتياطية جديدة</h6>
                <p class="text-muted small mb-3">تشمل جميع بياناتك: الفواتير، المنتجات، العملاء، الموردين، المشتريات، والإعدادات.</p>
                <form action="{{ route('backups.store') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-download me-2"></i>إنشاء نسخة احتياطية
                    </button>
                </form>
            </div>
        </div>
        <div class="card border-0 shadow-sm mt-3">
            <div class="card-body">
                <h6 class="fw-bold mb-2"><i class="fas fa-info-circle text-primary me-2"></i>ملاحظات</h6>
                <ul class="text-muted small mb-0 ps-3">
                    <li>النسخة بصيغة SQL مضغوطة (ZIP)</li>
                    <li>تحتوي على بياناتك فقط</li>
                    <li>احتفظ بها في مكان آمن</li>
                    <li>يتم حذف النسخ تلقائياً بعد 60 يوماً</li>
                </ul>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header">
                <h6 class="fw-bold mb-0"><i class="fas fa-history me-2 text-primary"></i>النسخ الاحتياطية السابقة</h6>
            </div>
            <div class="table-responsive">
                <table class="table mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>اسم الملف</th>
                            <th>الحجم</th>
                            <th>التاريخ</th>
                            <th>إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                        <tr>
                            <td>
                                <i class="fas fa-file-archive text-warning me-2"></i>
                                <span class="small">{{ $log->filename }}</span>
                            </td>
                            <td class="small text-muted">{{ number_format($log->size / 1024, 1) }} KB</td>
                            <td class="small text-muted">{{ \Carbon\Carbon::parse($log->created_at)->format('Y-m-d H:i') }}</td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="{{ route('backups.download', $log->id) }}" class="btn btn-xs btn-outline-primary">
                                        <i class="fas fa-download"></i>
                                    </a>
                                    <form action="{{ route('backups.destroy', $log->id) }}" method="POST" onsubmit="return confirm('حذف هذه النسخة؟')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-xs btn-outline-danger"><i class="fas fa-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">
                                <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                لا توجد نسخ احتياطية بعد
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($logs->hasPages())
            <div class="card-footer">{{ $logs->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
