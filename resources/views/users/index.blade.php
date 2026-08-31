@extends('layouts.app')
@section('title', 'المستخدمون')
@section('page-title')<span>إدارة المستخدمين</span>@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div class="text-muted small">{{ $users->count() }} مستخدم</div>
    <div class="d-flex gap-2">
        <a href="{{ route('users.change-password') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-key me-1"></i> تغيير كلمة مروري
        </a>
        @can('users.create')
        <a href="{{ route('users.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i> مستخدم جديد
        </a>
        @endcan
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>الاسم</th>
                    <th>اسم المستخدم</th>
                    <th>البريد</th>
                    <th class="text-center">الصلاحيات</th>
                    <th>المخازن</th>
                    <th class="text-center">الحالة</th>
                    <th>آخر دخول</th>
                    <th>إجراءات</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div style="width:34px;height:34px;border-radius:9px;background:var(--primary-light);color:var(--primary);display:flex;align-items:center;justify-content:center;font-weight:700;flex-shrink:0">
                                {{ Str::upper(Str::substr($user->name, 0, 1)) }}
                            </div>
                            <div>
                                <div class="fw-700">{{ $user->name }}</div>
                                <div class="text-muted" style="font-size:.72rem">{{ $user->role }}</div>
                            </div>
                        </div>
                    </td>
                    <td><code style="font-size:.8rem;background:#f1f5f9;padding:2px 6px;border-radius:5px">{{ $user->username }}</code></td>
                    <td class="text-muted small">{{ $user->email }}</td>
                    <td class="text-center">
                        @php $count = $user->getDirectPermissions()->count(); @endphp
                        @if($count === 0)
                            <span class="badge bg-secondary">بدون صلاحيات</span>
                        @elseif($user->isAdmin())
                            <span class="badge bg-danger">مدير كامل</span>
                        @else
                            <span class="badge" style="background:var(--primary-light);color:var(--primary)">{{ $count }} صلاحية</span>
                        @endif
                    </td>
                    <td class="small text-muted">
                        @php $whs = $user->warehouses; @endphp
                        @if($whs->count() > 0)
                            {{ $whs->pluck('name')->join('، ') }}
                        @else
                            <span class="text-muted">كل المخازن</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <span class="badge {{ $user->is_active ? 'bg-success' : 'bg-danger' }}">
                            {{ $user->is_active ? 'نشط' : 'موقوف' }}
                        </span>
                    </td>
                    <td class="text-muted small">{{ $user->last_login?->diffForHumans() ?? 'لم يسجل بعد' }}</td>
                    <td>
                        @if($user->id !== auth()->id())
                        <div class="d-flex gap-1">
                            @can('users.edit')
                            <a href="{{ route('users.edit', $user) }}" class="btn btn-xs btn-outline-primary" title="تعديل الصلاحيات">
                                <i class="fas fa-pen"></i>
                            </a>
                            <button class="btn btn-xs btn-outline-warning"
                                    data-bs-toggle="modal"
                                    data-bs-target="#resetModal{{ $user->id }}"
                                    title="إعادة كلمة المرور">
                                <i class="fas fa-key"></i>
                            </button>
                            <form action="{{ route('users.toggle', $user) }}" method="POST">
                                @csrf
                                <button class="btn btn-xs {{ $user->is_active ? 'btn-outline-danger' : 'btn-outline-success' }}"
                                        title="{{ $user->is_active ? 'إيقاف' : 'تفعيل' }}">
                                    <i class="fas fa-{{ $user->is_active ? 'ban' : 'check' }}"></i>
                                </button>
                            </form>
                            @endcan
                            @can('users.delete')
                            <form action="{{ route('users.destroy', $user) }}" method="POST"
                                  onsubmit="return confirm('حذف المستخدم {{ $user->name }}؟')">
                                @csrf @method('DELETE')
                                <button class="btn btn-xs btn-outline-danger"><i class="fas fa-trash"></i></button>
                            </form>
                            @endcan
                        </div>

                        {{-- Modal إعادة تعيين كلمة المرور --}}
                        <div class="modal fade" id="resetModal{{ $user->id }}" tabindex="-1">
                            <div class="modal-dialog modal-sm">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h6 class="modal-title fw-bold">
                                            <i class="fas fa-key me-2 text-warning"></i>إعادة كلمة مرور
                                        </h6>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form action="{{ route('users.reset-password', $user) }}" method="POST">
                                        @csrf
                                        <div class="modal-body">
                                            <p class="text-muted small">المستخدم: <strong>{{ $user->name }}</strong></p>
                                            <div class="mb-3">
                                                <label class="form-label">كلمة المرور الجديدة</label>
                                                <input type="password" name="password" class="form-control" required minlength="8" placeholder="8 أحرف على الأقل">
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label">تأكيد كلمة المرور</label>
                                                <input type="password" name="password_confirmation" class="form-control" required>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">إلغاء</button>
                                            <button type="submit" class="btn btn-warning btn-sm">
                                                <i class="fas fa-save me-1"></i> حفظ
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @else
                        <span class="text-muted small">أنت</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
