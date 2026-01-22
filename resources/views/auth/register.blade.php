@extends('layouts.app')

@section('hideNav', true)

@section('content')
<div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 relative overflow-hidden" dir="rtl">

    <div class="card-premium max-w-md w-full p-6 sm:p-8 relative z-10 animate-enter">
        <div class="text-center mb-8">
            <div class="landing-logo p-4 rounded-[2.5rem] bg-white dark:bg-slate-800/30 backdrop-blur-2xl border border-white/40 shadow-xl transition-all hover:scale-105 duration-500 ring-1 ring-gold-200/20 theme-icon-wrapper mx-auto mb-6">
                <img src="{{ asset('images/logo-qirat-premium.jpg') }}" alt="شعار قيراط" data-i18n-alt="appLogo" class="h-20 w-auto light-only rounded-2xl shadow-sm" loading="lazy">
                <img src="{{ asset('images/logo-dark.jpg') }}" alt="شعار قيراط" data-i18n-alt="appLogo" class="h-20 w-auto dark-only" loading="lazy">
            </div>
            <h2 class="text-3xl font-heading font-black text-text-main tracking-tight" data-i18n="register">إنشاء حساب</h2>
            <p class="mt-2 text-sm font-medium text-text-muted" data-i18n="registerJoin">
                <span data-i18n="registerJoin">سجّل بياناتك للانضمام وإدارة أموالك في قيراط</span>
            </p>
        </div>

        <form class="space-y-4" action="{{ route('register.perform') }}" method="POST">
            @csrf

            <div>
                <label for="name" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1" data-i18n="name">الاسم</label>
                <input id="name" name="name" type="text" required class="input-premium @error('name') input-invalid @enderror" data-i18n-placeholder="fullName" placeholder="Full Name" value="{{ old('name') }}" minlength="3" maxlength="80">
                @error('name')
                    <div class="invalid-feedback-premium">
                        <i class="bi bi-exclamation-circle-fill"></i>
                        <span>{{ $message }}</span>
                    </div>
                @enderror
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1" data-i18n="email">البريد الإلكتروني</label>
                <input id="email" name="email" type="email" required class="input-premium @error('email') input-invalid @enderror" data-i18n-placeholder="email" placeholder="name@example.com" value="{{ old('email') }}" maxlength="120">
                @error('email')
                    <div class="invalid-feedback-premium">
                        <i class="bi bi-exclamation-circle-fill"></i>
                        <span>{{ $message }}</span>
                    </div>
                @enderror
            </div>
            
            <div>
                <label for="password" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1" data-i18n="password">كلمة المرور</label>
                <input id="password" name="password" type="password" required class="input-premium @error('password') input-invalid @enderror" data-i18n-placeholder="password" placeholder="******" minlength="8">
                @error('password')
                    <div class="invalid-feedback-premium">
                        <i class="bi bi-exclamation-circle-fill"></i>
                        <span>{{ $message }}</span>
                    </div>
                @enderror
            </div>

            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1" data-i18n="confirmPassword">تأكيد كلمة المرور</label>
                <input id="password_confirmation" name="password_confirmation" type="password" required class="input-premium" data-i18n-placeholder="password" placeholder="******" minlength="8">
            </div>

            <div class="pt-2">
                <button type="submit" class="w-full btn-gold py-3 text-lg shadow-xl" data-i18n="signUp">
                    <span data-i18n="signUp">تسجيل جديد</span>
                </button>
            </div>
            
            <div class="text-center mt-6">
                <p class="text-sm text-slate-600 dark:text-slate-400">
                    <span data-i18n="haveAccount">لديك حساب بالفعل؟</span>
                    <a href="{{ route('login') }}" class="font-medium text-amber-600 hover:text-amber-500" data-i18n="login">
                        <span data-i18n="login">تسجيل الدخول</span>
                    </a>
                </p>
                <div class="mt-4 border-t border-slate-100 dark:border-slate-800 pt-4">
                     <a href="{{ url('/') }}" class="text-sm text-text-muted hover:text-slate-700 dark:hover:text-slate-300 flex items-center justify-center gap-2">
                        <i class="bi bi-arrow-right"></i> <span data-i18n="backToHome">عودة للرئيسية</span>
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
