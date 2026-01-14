<!doctype html>
<html lang="ar" dir="rtl" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Qiratae') }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>
<body class="bg-surface" data-theme-controller>
<header class="top-shell">
    <div class="max-w-6xl mx-auto px-4 flex items-center justify-between py-3">
        <a href="{{ url('/') }}" class="flex items-center gap-3 text-decoration-none">
            <img src="{{ asset('images/logo-gold.png') }}" alt="قيراط" class="logo-img logo-img-dark" loading="lazy">
            <img src="{{ asset('images/logo-gold-light.png') }}" alt="قيراط" class="logo-img logo-img-light" loading="lazy">
            <div>
                <div class="fw-bold" data-i18n="appName">قيراط المالي</div>
                <small class="text-muted" data-i18n="appTagline">تحكم كامل بمدخراتك</small>
            </div>
        </a>
        <div class="flex items-center gap-2 relative">
            <button class="icon-btn" id="themeToggle" type="button" aria-label="تبديل الوضع">
                <i class="bi bi-brightness-high"></i>
                <span class="btn-label">فاتح</span>
            </button>
            @auth
                @php
                    $avatarPath = auth()->user()->avatar_path ? asset('storage/' . auth()->user()->avatar_path) : null;
                    $initial = mb_substr(auth()->user()->name ?? 'م', 0, 1);
                @endphp
                <a class="profile-chip" href="{{ route('profile.index') }}" aria-label="الملف الشخصي">
                    @if($avatarPath)
                        <img src="{{ $avatarPath }}" alt="الصورة الشخصية" class="avatar-img" loading="lazy">
                    @else
                        <div class="avatar-fallback" aria-hidden="true">{{ $initial }}</div>
                    @endif
                    <div class="d-flex flex-column">
                        <span class="fw-bold">{{ auth()->user()->name }}</span>
                        <small class="text-muted">ملفي</small>
                    </div>
                </a>
                <form method="POST" action="{{ route('logout') }}" class="d-inline">
                    @csrf
                    <button type="submit" class="icon-btn primary-gradient" aria-label="تسجيل الخروج">
                        <i class="bi bi-box-arrow-right"></i>
                        <span class="btn-label">خروج</span>
                    </button>
                </form>
            @endauth
            @guest
                <a class="icon-btn" href="{{ route('login') }}" aria-label="تسجيل الدخول">
                    <i class="bi bi-box-arrow-in-right"></i>
                    <span class="btn-label">دخول</span>
                </a>
                <a class="icon-btn" href="{{ route('register') }}" aria-label="إنشاء حساب">
                    <i class="bi bi-person-plus"></i>
                    <span class="btn-label">تسجيل</span>
                </a>
            @endguest
        </div>
    </div>
    <div class="max-w-6xl mx-auto px-4">
        <nav class="primary-tabs">
            @auth
                @if(auth()->user()->is_admin)
                    <a href="{{ route('admin.dashboard') }}" class="tab-link {{ request()->is('admin') ? 'active' : '' }}">لوحة المدير</a>
                    <a href="{{ route('admin.users') }}" class="tab-link {{ request()->is('admin/users*') ? 'active' : '' }}">المستخدمون</a>
                    <a href="{{ route('admin.loginAttempts') }}" class="tab-link {{ request()->is('admin/login-attempts*') ? 'active' : '' }}">سجلات الدخول</a>
                    <a href="{{ route('settings.index') }}" class="tab-link {{ request()->is('settings*') ? 'active' : '' }}" data-i18n="settings">الإعدادات</a>
                    <a href="{{ route('profile.index') }}" class="tab-link {{ request()->is('profile*') ? 'active' : '' }}" data-i18n="profile">الملف</a>
                @else
                    <a href="{{ url('/') }}" class="tab-link {{ request()->is('/') ? 'active' : '' }}" data-i18n="home">الرئيسية</a>
                    <a href="{{ route('transactions.index') }}" class="tab-link {{ request()->is('transactions*') ? 'active' : '' }}" data-i18n="transactions">المعاملات</a>
                    <a href="{{ route('goals.index') }}" class="tab-link {{ request()->is('goals*') ? 'active' : '' }}" data-i18n="goals">الأهداف</a>
                    <a href="{{ route('reports.index') }}" class="tab-link {{ request()->is('reports*') ? 'active' : '' }}" data-i18n="reports">التقارير</a>
                    <a href="{{ route('settings.index') }}" class="tab-link {{ request()->is('settings*') ? 'active' : '' }}" data-i18n="settings">الإعدادات</a>
                @endif
            @endauth
            @guest
                <a href="{{ url('/') }}" class="tab-link {{ request()->is('/') ? 'active' : '' }}" data-i18n="home">الرئيسية</a>
                <a href="{{ route('transactions.index') }}" class="tab-link {{ request()->is('transactions*') ? 'active' : '' }}" data-i18n="transactions">المعاملات</a>
                <a href="{{ route('goals.index') }}" class="tab-link {{ request()->is('goals*') ? 'active' : '' }}" data-i18n="goals">الأهداف</a>
                <a href="{{ route('reports.index') }}" class="tab-link {{ request()->is('reports*') ? 'active' : '' }}" data-i18n="reports">التقارير</a>
                <a href="{{ route('settings.index') }}" class="tab-link {{ request()->is('settings*') ? 'active' : '' }}" data-i18n="settings">الإعدادات</a>
            @endguest
        </nav>
    </div>
</header>

<main class="max-w-6xl mx-auto px-4 pb-5 relative">
    @if (session('status'))
        <div class="alert alert-success soft-alert">{{ session('status') }}</div>
    @endif

    @yield('content')

</main>

<nav class="bottom-nav d-lg-none">
    @auth
        @if(auth()->user()->is_admin)
            <a href="{{ route('admin.dashboard') }}" class="bottom-item {{ request()->is('admin') ? 'active' : '' }}">
                <i class="bi bi-speedometer2"></i>
                <span>لوحة المدير</span>
            </a>
            <a href="{{ route('admin.users') }}" class="bottom-item {{ request()->is('admin/users*') ? 'active' : '' }}">
                <i class="bi bi-people"></i>
                <span>المستخدمون</span>
            </a>
            <a href="{{ route('admin.loginAttempts') }}" class="bottom-item {{ request()->is('admin/login-attempts*') ? 'active' : '' }}">
                <i class="bi bi-shield-lock"></i>
                <span>سجلات الدخول</span>
            </a>
            <a href="{{ route('settings.index') }}" class="bottom-item {{ request()->is('settings*') ? 'active' : '' }}">
                <i class="bi bi-gear"></i>
                <span data-i18n="settings">الإعدادات</span>
            </a>
            <a href="{{ route('profile.index') }}" class="bottom-item {{ request()->is('profile*') ? 'active' : '' }}">
                <i class="bi bi-person"></i>
                <span data-i18n="profile">الملف</span>
            </a>
        @else
            <a href="{{ url('/') }}" class="bottom-item {{ request()->is('/') ? 'active' : '' }}">
                <i class="bi bi-house"></i>
                <span data-i18n="home">الرئيسية</span>
            </a>
            <a href="{{ route('transactions.index') }}" class="bottom-item {{ request()->is('transactions*') ? 'active' : '' }}">
                <i class="bi bi-list"></i>
                <span data-i18n="transactions">المعاملات</span>
            </a>
            <a href="{{ route('goals.index') }}" class="bottom-item {{ request()->is('goals*') ? 'active' : '' }}">
                <i class="bi bi-bullseye"></i>
                <span data-i18n="goals">الأهداف</span>
            </a>
            <a href="{{ route('reports.index') }}" class="bottom-item {{ request()->is('reports*') ? 'active' : '' }}">
                <i class="bi bi-pie-chart"></i>
                <span data-i18n="stats">الإحصائيات</span>
            </a>
            <a href="{{ route('settings.index') }}" class="bottom-item {{ request()->is('settings*') ? 'active' : '' }}">
                <i class="bi bi-gear"></i>
                <span data-i18n="settings">الإعدادات</span>
            </a>
        @endif
    @endauth
    @guest
        <a href="{{ route('transactions.index') }}" class="bottom-item {{ request()->is('transactions*') ? 'active' : '' }}">
            <i class="bi bi-list"></i>
            <span data-i18n="transactions">المعاملات</span>
        </a>
        <a href="{{ route('goals.index') }}" class="bottom-item {{ request()->is('goals*') ? 'active' : '' }}">
            <i class="bi bi-bullseye"></i>
            <span data-i18n="goals">الأهداف</span>
        </a>
        <a href="{{ route('reports.index') }}" class="bottom-item {{ request()->is('reports*') ? 'active' : '' }}">
            <i class="bi bi-pie-chart"></i>
            <span data-i18n="stats">الإحصائيات</span>
        </a>
        <a href="{{ route('settings.index') }}" class="bottom-item {{ request()->is('settings*') ? 'active' : '' }}">
            <i class="bi bi-gear"></i>
            <span data-i18n="settings">الإعدادات</span>
        </a>
        <a href="{{ route('profile.index') }}" class="bottom-item {{ request()->is('profile*') ? 'active' : '' }}">
            <i class="bi bi-person"></i>
            <span data-i18n="profile">الملف</span>
        </a>
    @endguest
</nav>

<footer class="text-center py-4 text-muted text-sm">
    قيراط - إدارة مالية عربية مبنية بـ Laravel + Bootstrap
</footer>

<div id="toast-area" class="toast-area"></div>

<script>
    // Theme/style preferences are initialized via app.js global helpers.
</script>

@stack('scripts')
</body>
</html>
