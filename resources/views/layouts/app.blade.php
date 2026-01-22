<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Qiratae') }}</title>
    
    <!-- Anti-FOUC Blocking Script -->
    <script>
        (function() {
            try {
                const theme = localStorage.getItem('qiratae-theme') || 'light';
                document.documentElement.setAttribute('data-theme', theme);
                // Pre-set background color to avoid transition delay on initial load
                if (theme === 'dark') {
                    document.documentElement.style.backgroundColor = '#020617';
                } else {
                    document.documentElement.style.backgroundColor = '#FAFAF9';
                }
            } catch (e) {}
        })();
    </script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200..1000&family=Tajawal:wght@200;300;400;500;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>
<body class="bg-surface relative min-h-screen overflow-x-hidden" data-theme-controller>
    <!-- Global Premium Background -->
    <div class="fixed inset-0 z-0 overflow-hidden pointer-events-none opacity-20 dark:opacity-40">
        <div class="absolute -top-[10%] -right-[10%] w-[40%] h-[40%] bg-gradient-to-br from-[var(--gold-400)] to-transparent blur-[120px] rounded-full"></div>
        <div class="absolute -bottom-[10%] -left-[10%] w-[40%] h-[40%] bg-gradient-to-tr from-[var(--navy-600)] to-transparent blur-[120px] rounded-full"></div>
    </div>

    <div class="relative z-10">
        @unless(View::hasSection('hideNav'))
<header class="top-shell relative z-50">
    <div class="max-w-6xl mx-auto px-4 flex items-center justify-between py-3">
        <a href="{{ url('/') }}" class="flex items-center gap-3 text-decoration-none group">
            <div class="theme-icon-wrapper">
                <img src="{{ asset('images/logo-qirat-premium.jpg') }}" alt="قيراط" class="h-10 w-auto light-only rounded-lg shadow-sm border border-white/20 transition-transform group-hover:scale-105" loading="lazy">
                <img src="{{ asset('images/logo-dark.jpg') }}" alt="قيراط" class="h-10 w-auto dark-only transition-transform group-hover:scale-105" loading="lazy">
            </div>
            <div class="hidden sm:block">
                <div class="font-heading font-black text-slate-900 dark:text-white leading-none" data-i18n="appBrand">قيراط</div>
                <div class="text-[10px] text-text-muted font-medium mt-0.5" data-i18n="appTagline">تحكم كامل بمدخراتك</div>
            </div>
        </a>
        <div class="flex items-center gap-3 relative">
            <!-- Theme Toggle -->
            <button id="themeToggle" class="w-12 h-12 rounded-xl border border-transparent hover:border-slate-200 dark:hover:border-slate-700 hover:bg-white/50 dark:hover:bg-slate-800/50 text-slate-600 dark:text-amber-400 flex items-center justify-center transition-all duration-300 theme-icon-wrapper group" data-i18n-title="toggleTheme">
                <i class="bi bi-brightness-high light-only text-lg group-hover:rotate-12 transition-transform"></i>
                <i class="bi bi-moon-stars dark-only text-lg group-hover:-rotate-12 transition-transform"></i>
            </button>
            

            @auth
                <!-- Notifications Dropdown -->
                <div class="relative" id="notificationsShell">
                    <button type="button" id="notificationsToggle" class="w-10 h-10 relative flex items-center justify-center rounded-xl bg-white/50 dark:bg-slate-800/50 backdrop-blur-sm border border-slate-200 dark:border-white/10 text-slate-600 dark:text-amber-400 transition-all duration-300 hover:bg-white dark:hover:bg-slate-700 hover:scale-105 shadow-sm hover:shadow-md theme-icon-wrapper group" data-i18n-title="notifications">
                        <i class="bi bi-bell text-lg group-hover:rotate-12 transition-transform"></i>
                        <span id="notificationBadge" class="absolute top-2 right-2 w-2.5 h-2.5 bg-red-500 border-2 border-white dark:border-slate-900 rounded-full hidden"></span>
                    </button>
                    
                    <div id="notificationsMenu" class="fixed inset-x-4 top-20 mx-auto max-w-sm lg:absolute lg:inset-auto lg:left-0 lg:mt-3 lg:w-80 bg-white/95 dark:bg-slate-900/95 backdrop-blur-xl rounded-2xl shadow-2xl border border-white/20 dark:border-white/10 overflow-hidden hidden z-[100] transform origin-top-left transition-all duration-200 opacity-0 scale-95">
                        <div class="p-4 border-b border-slate-100 dark:border-white/5 flex items-center justify-between">
                            <h3 class="font-bold text-sm text-slate-800 dark:text-white" data-i18n="notifications">التنبيهات</h3>
                            <button id="markAllRead" class="text-xs text-[var(--gold-600)] hover:text-[var(--gold-500)] font-medium transition-colors" data-i18n="markAllRead">تحديد الكل كمقروء</button>
                        </div>
                        <div id="notificationsList" class="max-h-[300px] overflow-y-auto">
                            <!-- JS will populate -->
                            <div class="p-8 text-center text-slate-400 dark:text-white/30">
                                <i class="bi bi-bell-slash text-3xl mb-2 block"></i>
                                <span class="text-xs" data-i18n="noNotifications">لا توجد تنبيهات جديدة</span>
                            </div>
                        </div>
                        <div class="p-2 border-t border-slate-100 dark:border-white/5 bg-slate-50/50 dark:bg-black/20 text-center">
                            <a href="{{ route('notifications.index') }}" class="text-xs font-bold text-slate-500 hover:text-[var(--gold-500)] transition-colors block py-1" data-i18n="viewAllNotifications">عرض كل التنبيهات</a>
                        </div>
                    </div>
                </div>

                <!-- Profile Menu -->
                <div class="relative" id="profileMenuShell">
                    @php
                        $user = auth()->user();
                        $initial = mb_substr($user->name ?? 'ق', 0, 1);
                        $avatar = $user->avatar_path ? asset('storage/' . $user->avatar_path) : null;
                    @endphp
                    
                    <button type="button" class="flex items-center gap-2 pl-2 pr-1.5 py-1.5 rounded-xl border border-transparent hover:border-slate-200 dark:hover:border-slate-700 hover:bg-white/50 dark:hover:bg-slate-800/50 transition-all duration-300 group" id="profileToggle" data-i18n-title="myAccount">
                        <div class="relative">
                            @if($avatar)
                                <img src="{{ $avatar }}" alt="{{ $initial }}" class="w-9 h-9 rounded-lg object-cover shadow-sm border border-white/20 dark:border-white/10 group-hover:shadow-md transition-all">
                            @else
                                <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-[var(--gold-400)] to-[var(--gold-600)] flex items-center justify-center text-white font-bold shadow-sm border border-white/20">{{ $initial }}</div>
                            @endif
                            <div class="absolute -bottom-1 -right-1 w-3 h-3 bg-emerald-500 border-2 border-white dark:border-slate-900 rounded-full"></div>
                        </div>
                        
                        <div class="hidden lg:flex flex-col items-start leading-none px-1">
                            <span class="text-xs font-bold text-slate-700 dark:text-slate-200 group-hover:text-[var(--gold-600)] transition-colors" data-i18n="myAccount">حسابي</span>
                            <i class="bi bi-caret-down-fill text-[8px] text-slate-400 mt-0.5"></i>
                        </div>
                    </button>
                    <div id="profileMenu" class="profile-menu hidden">
                        <a href="{{ route('profile.index') }}" class="menu-item"><i class="bi bi-person"></i> <span data-i18n="profile">الملف الشخصي</span></a>
                        <a href="{{ route('settings.index') }}" class="menu-item"><i class="bi bi-gear"></i> <span data-i18n="settings">الإعدادات</span></a>
                        <form method="POST" action="{{ route('logout') }}" class="menu-item p-0">
                            @csrf
                            <button type="submit" class="w-full text-right px-3 py-2 text-sm flex items-center gap-2">
                                <i class="bi bi-box-arrow-right"></i> <span data-i18n="logout">تسجيل الخروج</span>
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Mobile Menu Button -->
                <button id="mobileMenuToggle" class="lg:hidden w-10 h-10 flex items-center justify-center rounded-xl bg-white/50 dark:bg-slate-800/50 backdrop-blur-sm border border-slate-200 dark:border-white/10 text-slate-600 dark:text-white transition-all shadow-sm">
                    <i class="bi bi-list text-2xl"></i>
                </button>
            @endauth
        </div>
    </div>
    
    <!-- Desktop Navigation -->
    <div class="max-w-6xl mx-auto px-4 hidden lg:block">
        <nav class="primary-tabs">
            @auth
                @if(auth()->user()->is_admin)
                    <a href="{{ route('admin.dashboard') }}" class="tab-link {{ request()->is('admin') ? 'active' : '' }}" data-i18n="adminDashboard">لوحة المدير</a>
                    <a href="{{ route('admin.users') }}" class="tab-link {{ request()->is('admin/users*') ? 'active' : '' }}" data-i18n="users">المستخدمون</a>
                    <a href="{{ route('admin.loginAttempts') }}" class="tab-link {{ request()->is('admin/login-attempts*') ? 'active' : '' }}" data-i18n="loginLogs">سجلات الدخول</a>
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
                <a href="{{ route('login') }}" class="tab-link" data-i18n="login">تسجيل الدخول</a>
                <a href="{{ route('register') }}" class="tab-link" data-i18n="register">إنشاء حساب</a>
            @endguest
        </nav>
    </div>

    <!-- Mobile Navigation Drawer -->
    <div id="mobileMenu" class="fixed inset-0 z-[60] bg-white/95 dark:bg-slate-900/95 backdrop-blur-xl transform translate-x-full transition-transform duration-300 lg:hidden flex flex-col pt-24 px-6 pb-6">
        <button id="closeMobileMenu" class="absolute top-6 left-6 w-10 h-10 flex items-center justify-center rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 hover:text-red-500 transition-colors">
            <i class="bi bi-x-lg text-xl"></i>
        </button>
        
        <nav class="flex flex-col gap-4 text-lg">
             @auth
                @if(auth()->user()->is_admin)
                    <a href="{{ route('admin.dashboard') }}" class="mobile-link {{ request()->is('admin') ? 'text-[var(--gold-600)] font-bold' : 'text-slate-600 dark:text-slate-300' }}" data-i18n="adminDashboard">لوحة المدير</a>
                    <a href="{{ route('admin.users') }}" class="mobile-link {{ request()->is('admin/users*') ? 'text-[var(--gold-600)] font-bold' : 'text-slate-600 dark:text-slate-300' }}" data-i18n="users">المستخدمون</a>
                    <a href="{{ route('admin.loginAttempts') }}" class="mobile-link {{ request()->is('admin/login-attempts*') ? 'text-[var(--gold-600)] font-bold' : 'text-slate-600 dark:text-slate-300' }}" data-i18n="loginLogs">سجلات الدخول</a>
                    <a href="{{ route('settings.index') }}" class="mobile-link {{ request()->is('settings*') ? 'text-[var(--gold-600)] font-bold' : 'text-slate-600 dark:text-slate-300' }}" data-i18n="settings">الإعدادات</a>
                @else
                    <a href="{{ url('/') }}" class="mobile-link {{ request()->is('/') ? 'text-[var(--gold-600)] font-bold' : 'text-slate-600 dark:text-slate-300' }}">
                        <i class="bi bi-house-door ml-3 opacity-70"></i><span data-i18n="home">الرئيسية</span>
                    </a>
                    <a href="{{ route('transactions.index') }}" class="mobile-link {{ request()->is('transactions*') ? 'text-[var(--gold-600)] font-bold' : 'text-slate-600 dark:text-slate-300' }}">
                        <i class="bi bi-receipt-cutoff ml-3 opacity-70"></i><span data-i18n="transactions">المعاملات</span>
                    </a>
                    <a href="{{ route('goals.index') }}" class="mobile-link {{ request()->is('goals*') ? 'text-[var(--gold-600)] font-bold' : 'text-slate-600 dark:text-slate-300' }}">
                        <i class="bi bi-bullseye ml-3 opacity-70"></i><span data-i18n="goals">الأهداف</span>
                    </a>
                    <a href="{{ route('reports.index') }}" class="mobile-link {{ request()->is('reports*') ? 'text-[var(--gold-600)] font-bold' : 'text-slate-600 dark:text-slate-300' }}">
                        <i class="bi bi-pie-chart ml-3 opacity-70"></i><span data-i18n="reports">التقارير</span>
                    </a>
                    <a href="{{ route('settings.index') }}" class="mobile-link {{ request()->is('settings*') ? 'text-[var(--gold-600)] font-bold' : 'text-slate-600 dark:text-slate-300' }}">
                        <i class="bi bi-gear ml-3 opacity-70"></i><span data-i18n="settings">الإعدادات</span>
                    </a>
                @endif
                <div class="h-px bg-slate-200 dark:bg-white/10 my-2"></div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="flex items-center gap-3 text-red-500 font-medium">
                        <i class="bi bi-box-arrow-right text-xl"></i> <span data-i18n="logout">تسجيل الخروج</span>
                    </button>
                </form>
            @endauth
            @guest
                <a href="{{ url('/') }}" class="mobile-link font-bold text-slate-800 dark:text-white" data-i18n="home">الرئيسية</a>
                <a href="{{ route('login') }}" class="mobile-link text-slate-600 dark:text-slate-300" data-i18n="login">تسجيل الدخول</a>
                <a href="{{ route('register') }}" class="mobile-link text-slate-600 dark:text-slate-300" data-i18n="register">إنشاء حساب</a>
            @endguest
        </nav>
    </div>
</header>
@endunless

<main class="max-w-6xl mx-auto px-4 pb-5 relative pt-8">
    @if (session('status'))
        <div class="alert alert-success soft-alert" data-i18n-dynamic>{{ session('status') }}</div>
    @endif

    @yield('content')

</main>

<script>
    function sendAiFeedback(feedbackId, action, objectType = null, objectId = null, feedbackType = null) {
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || document.querySelector('input[name="_token"]')?.value || '';
        const payload = { 
            feedback_id: feedbackId, 
            action: action,
            feedback_type: feedbackType 
        };
        if (objectType) payload.object_type = objectType;
        if (objectId) payload.object_id = objectId;

        fetch('{{ route('ai.feedback') }}', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(payload)
        }).then(r => r.json()).then(data => {
            const toast = document.getElementById('toast-area');
            if (toast) {
                const chip = document.createElement('div');
                chip.className = 'toast-chip';
                chip.textContent = (data?.status === 'success') ? 'تم تسجيل التقييم 👍' : 'تعذر تسجيل التقييم';
                toast.appendChild(chip);
                setTimeout(() => chip.remove(), 2500);
            }
            try {
                const el = document.querySelector('[data-feedback-id="' + feedbackId + '"]');
                if (el) {
                    if (action === 'dismissed') {
                        el.style.opacity = '0.5';
                        setTimeout(() => el.remove(), 500);
                    } else if (action === 'accepted') {
                        el.classList.add('border-green-500', 'bg-green-50');
                    }
                }
                
                // Refresh if needed
                if (objectType && objectId && (action === 'accepted' || action === 'dismissed')) {
                    // Optional: Call refresh logic here if strict sync is needed
                    // For now, simpler UI feedback is enough
                }
            } catch (e) { console.error(e); }
        }).catch(err => console.error('AI feedback error', err));
    }
</script>



<footer class="text-center py-4 text-text-muted text-sm" data-i18n="footerText">
    قيراط - إدارة مالية عربية مبنية بـ Laravel + Bootstrap &bull; <span class="text-xs opacity-50">v2.4 Platinum</span>
</footer>

<div id="toast-area" class="toast-area"></div>

<script>
    // Theme/style preferences are initialized via app.js global helpers.
    (function(){
        // Mobile Menu Logic
        const mobileMenuToggle = document.getElementById('mobileMenuToggle');
        const closeMobileMenu = document.getElementById('closeMobileMenu');
        const mobileMenu = document.getElementById('mobileMenu');

        if (mobileMenuToggle && mobileMenu) {
            mobileMenuToggle.addEventListener('click', () => mobileMenu.classList.remove('translate-x-full'));
        }
        if (closeMobileMenu && mobileMenu) {
            closeMobileMenu.addEventListener('click', () => mobileMenu.classList.add('translate-x-full'));
        }

        // Profile Menu Logic
        const toggle = document.getElementById('profileToggle');
        const menu = document.getElementById('profileMenu');
        const shell = document.getElementById('profileMenuShell');
        
        if (toggle && menu && shell) {
            const closeMenu = () => menu.classList.add('hidden');
            toggle.addEventListener('click', (e) => {
                e.stopPropagation();
                menu.classList.toggle('hidden');
                // Close notifications if open
                document.getElementById('notificationsMenu')?.classList.add('hidden');
            });
            document.addEventListener('click', (e) => {
                if (!shell.contains(e.target)) closeMenu();
            });
        }

        // Notifications Logic
        const notifToggle = document.getElementById('notificationsToggle');
        const notifMenu = document.getElementById('notificationsMenu');
        const notifShell = document.getElementById('notificationsShell');
        const notifList = document.getElementById('notificationsList');
        const notifBadge = document.getElementById('notificationBadge');
        const markAllBtn = document.getElementById('markAllRead');

        if (notifToggle && notifMenu && notifShell) {
            const closeNotif = () => {
                notifMenu.classList.add('hidden');
                notifMenu.classList.remove('opacity-100', 'scale-100');
                notifMenu.classList.add('opacity-0', 'scale-95');
            };

            const toggleNotif = () => {
                if (notifMenu.classList.contains('hidden')) {
                    notifMenu.classList.remove('hidden');
                    notifMenu.style.display = 'block';
                    requestAnimationFrame(() => {
                        notifMenu.classList.remove('opacity-0', 'scale-95');
                        notifMenu.classList.add('opacity-100', 'scale-100');
                    });
                    fetchNotifications();
                    document.getElementById('profileMenu')?.classList.add('hidden');
                } else {
                    closeNotif();
                }
            };

            notifToggle.addEventListener('click', (e) => {
                e.stopPropagation();
                toggleNotif();
            });

            document.addEventListener('click', (e) => {
                if (!notifShell.contains(e.target)) closeNotif();
            });

            // Fetch Notifications
            function fetchNotifications() {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                fetch('{{ route('notifications.index') }}', {
                    headers: { 
                        'X-Requested-With': 'XMLHttpRequest', 
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json'
                    }
                })
                .then(r => {
                    if (!r.ok) throw new Error('Network error');
                    return r.json();
                })
                .then(data => {
                    renderNotifications(data);
                    updateBadge(data.length);
                })
                .catch(e => console.error('Notification Error:', e));
            }

            // Render List
            function renderNotifications(items) {
                if (!items || items.length === 0) {
                    notifList.innerHTML = `
                        <div class="p-8 text-center text-slate-400 dark:text-white/30">
                            <i class="bi bi-bell-slash text-3xl mb-2 block"></i>
                            <span class="text-xs" data-i18n="noNotifications">لا توجد تنبيهات جديدة</span>
                        </div>`;
                    return;
                }

                notifList.innerHTML = items.map(n => `
                    <div class="p-4 border-b border-slate-50 dark:border-white/5 hover:bg-slate-50 dark:hover:bg-white/5 transition-colors cursor-pointer group relative" onclick="markRead('${n.id}', this)">
                        <div class="flex gap-3">
                            <div class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center shrink-0 text-slate-500">
                                <i class="bi ${n.data.icon || 'bi-bell'} ${n.data.color || ''}"></i>
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-slate-800 dark:text-slate-200 mb-0.5">${n.data.title}</h4>
                                <p class="text-xs text-text-muted leading-relaxed line-clamp-2">${n.data.body}</p>
                                <span class="text-[10px] text-slate-300 mt-1 block">${new Date(n.created_at).toLocaleDateString('ar-EG')}</span>
                            </div>
                        </div>
                    </div>
                `).join('');
            }

            // Update Badge
            function updateBadge(count) {
                if (count > 0) {
                    notifBadge.textContent = count > 9 ? '9+' : count;
                    notifBadge.classList.remove('hidden');
                } else {
                    notifBadge.classList.add('hidden');
                }
            }

            // Mark Read Logic
            window.markRead = function(id, el) {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                fetch(`{{ url('notifications') }}/${id}/read`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token }
                }).then(() => {
                    el.style.opacity = '0.5';
                    setTimeout(() => el.remove(), 300);
                    let count = parseInt(notifBadge.textContent) || 0;
                    if (count > 0) updateBadge(count - 1);
                });
            };

            // Mark All Read
            markAllBtn?.addEventListener('click', () => {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                fetch('{{ route('notifications.readAll') }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': token }
                }).then(() => {
                    notifList.innerHTML = `
                        <div class="p-8 text-center text-slate-400 dark:text-white/30">
                            <i class="bi bi-bell-slash text-3xl mb-2 block"></i>
                            <span class="text-xs" data-i18n="noNotifications">تمت قراءة جميع التنبيهات</span>
                        </div>`;
                    updateBadge(0);
                });
            });

            // Initial fetch
            fetchNotifications();
        }
    })();
</script>

    </div>

    @stack('scripts')
</body>
</html>
