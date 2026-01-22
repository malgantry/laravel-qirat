@extends('layouts.app')

@section('hideNav', true)

@section('content')
<div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 relative overflow-hidden" dir="rtl">

    <div class="card-premium max-w-md w-full p-8 relative z-10 animate-enter">
        <div class="text-center mb-8">
            <div class="landing-logo p-4 rounded-[2.5rem] bg-white dark:bg-slate-800/30 backdrop-blur-2xl border border-white/40 shadow-xl transition-all hover:scale-105 duration-500 ring-1 ring-gold-200/20 theme-icon-wrapper mx-auto mb-6">
                <img src="{{ asset('images/logo-qirat-premium.jpg') }}" alt="شعار قيراط" class="h-20 w-auto light-only rounded-2xl shadow-sm" loading="lazy">
                <img src="{{ asset('images/logo-dark.jpg') }}" alt="شعار قيراط" class="h-20 w-auto dark-only" loading="lazy">
            </div>
            <h2 class="text-3xl font-heading font-black text-text-main tracking-tight">استعادة الحساب</h2>
            <p class="mt-2 text-sm font-medium text-text-muted">
                سنرسل لك رابط إعادة تعيين إلى بريدك الإلكتروني
            </p>
        </div>

        @if(session('status'))
            <div class="p-4 mb-6 rounded-2xl bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400 text-sm border border-emerald-100 dark:border-emerald-900/30 font-bold">
                <i class="bi bi-check2-circle me-2"></i> {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}" class="space-y-6">
            @csrf
            <div>
                <label for="email" class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2 uppercase tracking-widest text-[10px] mr-1">البريد الإلكتروني</label>
                <div class="relative group">
                    <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none text-slate-400 group-focus-within:text-[var(--gold-500)] transition-colors">
                        <i class="bi bi-envelope"></i>
                    </div>
                    <input id="email" type="email" name="email" class="input-premium pr-11 @error('email') input-invalid @enderror" value="{{ old('email') }}" required autofocus placeholder="name@example.com">
                </div>
                @error('email')
                    <div class="invalid-feedback-premium">
                        <i class="bi bi-exclamation-circle-fill"></i>
                        <span>{{ $message }}</span>
                    </div>
                @enderror
            </div>

            <button type="submit" class="w-full btn-gold py-4 text-lg font-black shadow-xl">
                إرسال رابط التحقق
            </button>
        </form>

        <div class="text-center mt-8 pt-6 border-t border-slate-100 dark:border-slate-800">
            <a href="{{ route('login') }}" class="text-sm font-bold text-amber-600 hover:text-amber-500 flex items-center justify-center gap-2">
                 العودة لتسجيل الدخول <i class="bi bi-arrow-left"></i>
            </a>
        </div>
    </div>
</div>
@endsection
