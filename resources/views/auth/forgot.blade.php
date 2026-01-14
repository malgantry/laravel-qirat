@extends('layouts.app')

@section('content')
<style>
    .auth-hero { min-height: 80vh; display: grid; place-items: center; background: radial-gradient(circle at 10% 20%, rgba(201,162,39,0.10), transparent 35%), radial-gradient(circle at 80% 0%, rgba(11,11,11,0.08), transparent 40%), linear-gradient(135deg, #0b0b0b 0%, #1f1f1f 35%, #c9a227 100%); padding: 48px 16px; }
    .auth-card { max-width: 480px; width: 100%; border-radius: 18px; background: var(--card-bg); color: var(--text-primary); border: 1px solid rgba(255,255,255,0.08); box-shadow: 0 25px 60px rgba(0,0,0,0.18); position: relative; overflow: hidden; }
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
                    <i class="bi bi-shield-lock fs-4"></i>
                </div>
                <div>
                    <h5 class="mb-1">إعادة تعيين كلمة المرور</h5>
                    <div class="auth-sub">سنرسل لك رابط إعادة تعيين إلى بريدك.</div>
                </div>
            </div>

            @if(session('status'))
                <div class="alert alert-info">{{ session('status') }}</div>
            @endif

            <form method="POST" action="{{ route('password.email') }}" class="space-y-3">
                @csrf
                <div class="mb-3">
                    <label class="form-label">البريد الإلكتروني</label>
                    <div class="input-group input-group-lg">
                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                        <input type="email" name="email" class="form-control" value="{{ old('email') }}" required autofocus>
                    </div>
                    @error('email')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>
                <button type="submit" class="btn w-100 py-2 primary-gradient fw-bold">إرسال رابط إعادة التعيين</button>
            </form>

            <div class="text-center mt-4">
                <a href="{{ route('login') }}" class="fw-semibold">العودة لتسجيل الدخول</a>
            </div>
        </div>
    </div>
</div>
@endsection
