@extends('layouts.app')

@section('content')
    <div class="max-w-4xl mx-auto space-y-8 animate-enter">
        <!-- Main Settings Container -->
        <div class="card-premium p-8 border-none shadow-2xl">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-10 border-b border-[var(--border-light)] pb-8">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 rounded-2xl bg-amber-50 dark:bg-amber-900/20 text-[var(--gold-500)] flex items-center justify-center text-3xl shadow-inner border border-amber-100/50 dark:border-amber-900/30">
                        <i class="bi bi-gear-fill"></i>
                    </div>
                    <div>
                        <h2 class="text-3xl font-heading font-black text-text-main tracking-tight" data-i18n="settingsPanel">لوحة الإعدادات</h2>
                        <p class="text-text-muted mt-1 font-medium text-sm" data-i18n="fullSystemControl">تحكم كامل في مظهر النظام والخيارات المتقدمة.</p>
                    </div>
                </div>
                
                <div class="flex flex-wrap gap-2 p-1 bg-slate-100 dark:bg-slate-900/50 rounded-2xl border border-[var(--border-light)]">
                    <button class="px-4 py-2 text-xs font-black rounded-xl hover:bg-white dark:hover:bg-slate-800 transition-all text-slate-600 dark:text-slate-400 theme-icon-wrapper" type="button" onclick="applyTheme('light')" data-i18n="light">فاتح</button>
                    <button class="px-4 py-2 text-xs font-black rounded-xl hover:bg-white dark:hover:bg-slate-800 transition-all text-slate-600 dark:text-slate-400 theme-icon-wrapper" type="button" onclick="applyTheme('dark')" data-i18n="dark">داكن</button>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- General Configuration Side -->
                <div class="space-y-8">
                    <!-- General Settings Group -->
                    <div class="card-premium p-8 border-none shadow-xl relative overflow-hidden group">
                        <div class="absolute -top-12 -right-12 w-24 h-24 bg-[var(--gold-500)]/5 blur-3xl rounded-full"></div>
                        <div class="flex items-center gap-3 mb-6">
                            <i class="bi bi-sliders2 text-xl text-[var(--gold-500)]"></i>
                            <h3 class="font-heading font-black text-slate-800 dark:text-slate-100" data-i18n="settings">الإعدادات العامة</h3>
                        </div>
                        
                        <div class="space-y-6">
                            <div class="space-y-3">
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mr-1" data-i18n="preferredLanguage">اللغة المفضلة</label>
                                <div class="grid grid-cols-2 gap-3">
                                    <button class="py-3 px-4 border border-slate-200 dark:border-slate-700 rounded-2xl hover:border-[var(--gold-400)] dark:hover:border-[var(--gold-600)] hover:bg-white dark:hover:bg-slate-800 transition-all text-sm font-bold flex items-center justify-center gap-2" id="set-ar">
                                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span> <span data-i18n="langAr">العربية</span>
                                    </button>
                                    <button class="py-3 px-4 border border-slate-200 dark:border-slate-700 rounded-2xl hover:border-[var(--gold-400)] dark:hover:border-[var(--gold-600)] hover:bg-white dark:hover:bg-slate-800 transition-all text-sm font-bold flex items-center justify-center gap-2" id="set-en">
                                        <span class="w-2 h-2 rounded-full bg-slate-300 dark:bg-slate-600"></span> <span data-i18n="langEn">English</span>
                                    </button>
                                </div>
                            </div>

                            <div class="space-y-3">
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mr-1" data-i18n="uiInterface">واجهة المستخدم</label>
                                <div class="grid grid-cols-2 gap-3">
                                    <button class="py-3 px-4 border border-slate-200 dark:border-slate-700 rounded-2xl hover:border-[var(--gold-400)] dark:hover:border-[var(--gold-600)] hover:bg-white dark:hover:bg-slate-800 transition-all text-sm font-bold flex items-center justify-center gap-2" id="set-light">
                                        <i class="bi bi-brightness-high"></i> <span data-i18n="lightMode">وضع فاتح</span>
                                    </button>
                                    <button class="py-3 px-4 border border-slate-200 dark:border-slate-700 rounded-2xl hover:border-[var(--gold-400)] dark:hover:border-[var(--gold-600)] hover:bg-white dark:hover:bg-slate-800 transition-all text-sm font-bold flex items-center justify-center gap-2" id="set-dark">
                                        <i class="bi bi-moon-stars"></i> <span data-i18n="darkMode">وضع داكن</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Currency Selector Card -->
                    <div class="card-premium p-8 border-none shadow-xl relative overflow-hidden group">
                        <div class="flex items-center gap-3 mb-4">
                            <i class="bi bi-cash-coin text-xl text-emerald-500"></i>
                            <h3 class="font-heading font-black text-slate-800 dark:text-slate-100" data-i18n="defaultCurrency">العملة الافتراضية</h3>
                        </div>
                        <div class="relative">
                            <select class="input-premium py-3 pl-4 pr-10 appearance-none bg-white dark:bg-slate-900 border-slate-200/50 dark:border-slate-800/50" id="currency">
                                <option value="LYD" data-i18n="lyd">الدينار الليبي (د.ل)</option>
                                <option value="USD" data-i18n="usd">الدولار الأمريكي ($)</option>
                                <option value="EUR" data-i18n="eur">اليورو (€)</option>
                            </select>
                            <div class="absolute inset-y-0 left-4 flex items-center pointer-events-none text-slate-400">
                                <i class="bi bi-chevron-down text-xs"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Strategic Navigation & Security -->
                <div class="space-y-8">
                    <!-- Navigation Shortcuts (Quick Access) -->
                    <div class="card-premium p-8 border-none shadow-2xl flex flex-col relative overflow-hidden group">
                        <div class="absolute -top-12 -right-12 w-32 h-32 bg-[var(--gold-500)]/5 blur-3xl rounded-full group-hover:scale-150 transition-transform duration-700"></div>
                        <div class="flex justify-between items-start mb-2">
                            <div class="flex items-center gap-4">
                                <div class="w-14 h-14 rounded-2xl bg-blue-50 dark:bg-blue-900/20 text-blue-500 flex items-center justify-center text-2xl shadow-inner border border-blue-100/50 dark:border-blue-900/30">
                                    <i class="bi bi-compass-fill"></i>
                                </div>
                                <div>
                                    <h3 class="text-xl font-heading font-black text-slate-800 dark:text-slate-100 mb-0" data-i18n="strategicNavigation">التنقل الاستراتيجي</h3>
                                    <p class="text-[10px] text-text-muted font-bold uppercase tracking-widest mt-0.5" data-i18n="quickAccessTools">أدوات الوصول السريع</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 gap-3 relative z-10">
                            @php
                                $navs = auth()->user()?->is_admin ? [
                                    ['name' => 'adminDashboard', 'label' => 'adminDashboard', 'route' => 'admin.dashboard', 'icon' => 'bi-speedometer2'],
                                    ['name' => 'manageUsers', 'label' => 'manageUsers', 'route' => 'admin.users', 'icon' => 'bi-people'],
                                    ['name' => 'loginTraffic', 'label' => 'loginLogs', 'route' => 'admin.loginAttempts', 'icon' => 'bi-shield-check'],
                                ] : [
                                    ['name' => 'profile', 'label' => 'profile', 'route' => 'profile.index', 'icon' => 'bi-person-circle'],
                                    ['name' => 'reports', 'label' => 'reports', 'route' => 'reports.index', 'icon' => 'bi-graph-up-arrow'],
                                    ['name' => 'transactions', 'label' => 'transactions', 'route' => 'transactions.index', 'icon' => 'bi-list-check'],
                                ];
                            @endphp

                            @foreach($navs as $nav)
                                <a href="{{ route($nav['route']) }}" class="flex items-center justify-between p-5 rounded-2xl bg-slate-50/50 dark:bg-slate-900/40 border border-slate-100 dark:border-white/5 hover:border-[var(--gold-400)] dark:hover:border-[var(--gold-600)] transition-all group shadow-sm hover:shadow-md">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 rounded-xl bg-white dark:bg-slate-800 flex items-center justify-center shadow-sm group-hover:scale-110 transition-transform">
                                            <i class="bi {{ $nav['icon'] }} text-slate-400 group-hover:text-[var(--gold-500)] transition-colors text-lg"></i>
                                        </div>
                                        <span class="text-sm font-black text-slate-700 dark:text-slate-300 group-hover:text-[var(--gold-600)] transition-colors" data-i18n="{{ $nav['name'] }}">{{ $nav['name'] }}</span>
                                    </div>
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center bg-white/0 group-hover:bg-[var(--gold-50)] dark:group-hover:bg-[var(--gold-900)]/20 transition-all">
                                        <i class="bi bi-chevron-left text-xs text-slate-300 group-hover:text-[var(--gold-600)] group-hover:translate-x-[-2px] transition-all"></i>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>

                    <!-- Security Group -->
                    <div class="card-premium p-8 border-none shadow-xl relative overflow-hidden group">
                          <div class="flex items-center gap-3 mb-6">
                            <i class="bi bi-shield-lock-fill text-xl text-emerald-500"></i>
                            <h3 class="font-heading font-black text-slate-800 dark:text-slate-100" data-i18n="accountSecurity">الأمان والخصوصية</h3>
                        </div>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mr-1 mb-2" data-i18n="walletVisibility">رؤية المحفظة</label>
                                <div class="relative">
                                    <select class="input-premium py-3 pl-4 pr-10 appearance-none bg-white dark:bg-slate-900 border-slate-200/50 dark:border-slate-800/50" id="privacy">
                                        <option value="public" data-i18n="public">عام (مرئي للمدراء)</option>
                                        <option value="private" data-i18n="private">خاص (مخفي تماماً)</option>
                                    </select>
                                    <div class="absolute inset-y-0 left-4 flex items-center pointer-events-none text-slate-400">
                                        <i class="bi bi-eye-slash-fill text-xs"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Status Footer -->
        <div class="flex items-center justify-center gap-3 py-6 px-4 bg-slate-50 dark:bg-slate-900/50 rounded-3xl border border-slate-200/30 dark:border-slate-800/30">
            <span class="flex h-2 w-2 relative">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
            </span>
            <span class="text-xs font-black text-slate-400 tracking-widest uppercase" data-i18n="systemEfficiency">النظام يعمل بكفاءة قصوى • قيراط</span>
        </div>
    </div>

    @push('scripts')
    <script>
        const setStore = (k,v) => localStorage.setItem(k, v);
        
        // Helper to mimic toast if not available or hook into existing
        const showToast = (type, msg) => {
            if(window.qirataeToast) {
                window.qirataeToast(type, msg);
            } else {
                // If not defined, we can use a custom luxury toast or fallback
                console.log(`${type}: ${msg}`);
            }
        };

        document.getElementById('set-ar').onclick = () => { applyLanguage('ar'); showToast('success', i18n[currentLang()].langSetAr); };
        document.getElementById('set-en').onclick = () => { applyLanguage('en'); showToast('success', i18n[currentLang()].langSetEn); };
        document.getElementById('set-light').onclick = () => { applyTheme('light'); showToast('info', i18n[currentLang()].lightModeSet); };
        document.getElementById('set-dark').onclick = () => { applyTheme('dark'); showToast('info', i18n[currentLang()].darkModeSet); };
        document.getElementById('currency').onchange = (e) => { setStore('qiratae-currency', e.target.value); showToast('success', i18n[currentLang()].currencyChanged); };
        document.getElementById('privacy').onchange = (e) => { setStore('qiratae-privacy', e.target.value); showToast('info', i18n[currentLang()].privacyUpdated); };
    </script>
    @endpush
@endsection
