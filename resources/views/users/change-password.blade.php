@extends('layouts.app')
@section('title', 'تغيير كلمة المرور')
@section('page-title')<h6 class="mb-0 fw-bold">تغيير كلمة المرور</h6>@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <form action="{{ route('users.change-password') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">كلمة المرور الحالية <span class="text-danger">*</span></label>
                        <input type="password" name="current_password" class="form-control @error('current_password') is-invalid @enderror" required>
                        @error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">كلمة المرور الجديدة <span class="text-danger">*</span></label>
                        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required minlength="8">
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-4">
                        <label class="form-label">تأكيد كلمة المرور الجديدة <span class="text-danger">*</span></label>
                        <input type="password" name="password_confirmation" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">حفظ كلمة المرور</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
