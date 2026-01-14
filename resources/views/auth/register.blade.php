@extends('layouts.app')

@section('content')
<style>
    .auth-hero { min-height: 80vh; display: grid; place-items: center; background: radial-gradient(circle at 10% 20%, rgba(201,162,39,0.10), transparent 35%), radial-gradient(circle at 80% 0%, rgba(11,11,11,0.08), transparent 40%), linear-gradient(135deg, #0b0b0b 0%, #1f1f1f 35%, #c9a227 100%); padding: 48px 16px; }
    .auth-card { max-width: 520px; width: 100%; border-radius: 18px; background: var(--card-bg); color: var(--text-primary); border: 1px solid rgba(255,255,255,0.08); box-shadow: 0 25px 60px rgba(0,0,0,0.18); position: relative; overflow: hidden; }
    .auth-card .accent-bar { height: 4px; width: 100%; background: var(--primary-gradient); }
    .auth-card .card-body { padding: 28px; }
    .auth-card h5 { font-weight: 800; letter-spacing: -0.02em; }
    .auth-sub { color: #9ca3af; font-size: 0.95rem; }
</style>

<div class="auth-hero" dir="rtl">
    <div class="auth-card">
        <div class="accent-bar"></div>
        <div class="card-body">
            <div class="d-flex align-items-start gap-3 mb-3">
                <div class="p-2 rounded-circle" style="background: rgba(201,162,39,0.15); color: #c9a227;">
                    <i class="bi bi-person-plus fs-4"></i>
                </div>
                <div>
                    <h5 class="mb-1">إنشاء حساب</h5>
                    <div class="auth-sub">سجّل بياناتك للانضمام وإدارة أموالك.</div>
                </div>
            </div>

            <form method="POST" action="{{ route('register.perform') }}" class="space-y-3">
                @csrf
                <div class="mb-3">
                    <label class="form-label">الاسم</label>
                    <div class="input-group input-group-lg">
                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" required autofocus>
                    </div>
                    @error('name')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">البريد الإلكتروني</label>
                    <div class="input-group input-group-lg">
                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                        <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                    </div>
                    @error('email')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">كلمة المرور</label>
                    <div class="input-group input-group-lg">
                        <span class="input-group-text"><i class="bi bi-key"></i></span>
                        <input id="reg-password" type="password" name="password" class="form-control" required>
                        <button class="input-group-text" type="button" aria-label="إظهار أو إخفاء كلمة المرور" data-toggle-password="reg-password">
                            <i class="bi bi-eye"></i>
                            <span class="toggle-text d-none d-md-inline" style="margin-inline-start: 6px;">إظهار</span>
                        </button>
                    </div>
                    @error('password')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">تأكيد كلمة المرور</label>
                    <div class="input-group input-group-lg">
                        <span class="input-group-text"><i class="bi bi-key"></i></span>
                        <input id="reg-password-confirm" type="password" name="password_confirmation" class="form-control" required>
                        <button class="input-group-text" type="button" aria-label="إظهار أو إخفاء كلمة المرور" data-toggle-password="reg-password-confirm">
                            <i class="bi bi-eye"></i>
                            <span class="toggle-text d-none d-md-inline" style="margin-inline-start: 6px;">إظهار</span>
                        </button>
                    </div>
                </div>
                <button type="submit" class="btn w-100 py-2 primary-gradient fw-bold">تسجيل</button>
            </form>

            <div class="text-center mt-4">
                <span>لديك حساب بالفعل؟</span>
                <a href="{{ route('login') }}" class="fw-semibold">تسجيل الدخول</a>
            </div>
        </div>
    </div>
</div>

@endsection
