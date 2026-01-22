@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto space-y-8 animate-enter">
        <!-- Admin Command Center Header -->
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
            <div>
                <div class="flex items-center gap-2 text-[var(--gold-500)] font-black text-[10px] uppercase tracking-[0.2em] mb-2" data-i18n="centralManagement">
                    <span class="w-8 h-px bg-[var(--gold-400)]"></span>
                    <span data-i18n="centralManagement">إدارة المنظومة المركزية</span>
                </div>
                <h3 class="text-4xl font-heading font-black text-text-main tracking-tight" data-i18n="commandCenter">مركز القيادة</h3>
            </div>
            
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('admin.users') }}" class="btn-gold px-6 py-3 text-sm font-black shadow-xl">
                    <i class="bi bi-people-fill me-2"></i> <span data-i18n="manageUsersTitle">إدارة المستخدمين</span>
                </a>
                <a href="{{ route('admin.categories') }}" class="btn-soft px-6 py-3 text-sm font-bold">
                    <i class="bi bi-tags-fill me-2"></i> <span data-i18n="categoryStructure">هيكلة الفئات</span>
                </a>
            </div>
        </div>

        <!-- High-Level Metrics Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @php
                $adminStats = [
                    'users' => ['label' => 'المستخدمين النشطين', 'i18n' => 'activeUsers', 'icon' => 'bi-people', 'color' => 'amber'],
                    'categories' => ['label' => 'إجمالي الفئات', 'i18n' => 'totalCategories', 'icon' => 'bi-tags', 'color' => 'blue'],
                    'transactions' => ['label' => 'حجم العمليات', 'i18n' => 'transactionVolume', 'icon' => 'bi-cash-stack', 'color' => 'emerald'],
                    'goals' => ['label' => 'أهداف الادخار', 'i18n' => 'savingsGoals', 'icon' => 'bi-bullseye', 'color' => 'rose'],
                ];
            @endphp

            @foreach($stats as $key => $value)
                @php $s = $adminStats[$key] ?? ['label' => $key, 'i18n' => '', 'icon' => 'bi-activity', 'color' => 'slate']; @endphp
                <div class="card-premium p-6 border-none shadow-xl relative overflow-hidden group">
                    <div class="absolute -top-6 -left-6 w-20 h-20 bg-{{ $s['color'] }}-500/5 blur-3xl rounded-full group-hover:bg-{{ $s['color'] }}-500/10 transition-colors"></div>
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 rounded-2xl bg-{{ $s['color'] }}-50 dark:bg-{{ $s['color'] }}-900/20 text-{{ $s['color'] }}-600 dark:text-{{ $s['color'] }}-400 flex items-center justify-center text-xl shadow-inner border border-[var(--border-light)]">
                            <i class="bi {{ $s['icon'] }}"></i>
                        </div>
                        <span class="text-[10px] font-black text-text-muted uppercase tracking-widest" data-i18n="{{ $s['i18n'] }}">{{ $s['label'] }}</span>
                    </div>
                    <div>
                        <div class="text-4xl font-heading font-black text-text-main mb-3 tracking-tighter">{{ number_format($value) }}</div>
                        <div class="w-full bg-slate-100 dark:bg-slate-900 h-1.5 rounded-full overflow-hidden shadow-inner">
                            <div class="h-full bg-gradient-to-r from-{{ $s['color'] }}-400 to-{{ $s['color'] }}-600 rounded-full" style="width: 100%"></div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- System Health Snapshot -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @php
                $healthCards = [
                    ['label' => 'نشطون آخر 7 أيام', 'value' => $health['activeUsers7d'] ?? 0, 'icon' => 'bi-lightning-charge', 'color' => 'emerald'],
                    ['label' => 'فشل دخول 24س', 'value' => $health['failedLogins24h'] ?? 0, 'icon' => 'bi-shield-exclamation', 'color' => 'rose'],
                    ['label' => 'مستخدمون جدد 24س', 'value' => $health['newUsers24h'] ?? 0, 'icon' => 'bi-person-plus', 'color' => 'amber'],
                    ['label' => 'عمليات 24س', 'value' => $health['tx24h'] ?? 0, 'icon' => 'bi-activity', 'color' => 'indigo'],
                ];
            @endphp
            @foreach($healthCards as $c)
                <div class="card-premium p-5 border-none shadow-lg flex items-center gap-4 bg-white/60 dark:bg-slate-900/60">
                    <div class="w-12 h-12 rounded-xl bg-{{ $c['color'] }}-50 dark:bg-{{ $c['color'] }}-900/20 text-{{ $c['color'] }}-600 dark:text-{{ $c['color'] }}-400 flex items-center justify-center text-xl shadow-inner border border-[var(--border-light)]">
                        <i class="bi {{ $c['icon'] }}"></i>
                    </div>
                    <div>
                        <div class="text-sm font-black text-slate-500">{{ $c['label'] }}</div>
                        <div class="text-2xl font-heading font-black text-text-main">{{ number_format($c['value']) }}</div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- System Updates & Recent Activity -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Latest Users List -->
            <div class="lg:col-span-2 card-premium p-8 border-none shadow-2xl overflow-hidden relative">
                <div class="flex items-center justify-between mb-10">
                    <div>
                        <h5 class="text-xl font-heading font-black text-text-main mb-1" data-i18n="recentlyRegistered">المسجلين حديثاً</h5>
                        <p class="text-xs text-slate-500 font-medium" data-i18n="latestMembersInfo">قائمة بأحدث الأعضاء المنضمين للمنصة.</p>
                    </div>
                    <a href="{{ route('admin.users') }}" class="text-xs font-black text-[var(--gold-600)] hover:text-[var(--gold-700)] uppercase tracking-widest flex items-center gap-2 group">
                        <span data-i18n="userRegistry">سجل المستخدمين</span> 
                        <i class="bi bi-arrow-left transition-transform group-hover:translate-x-[-4px]"></i>
                    </a>
                </div>
                
                <div class="space-y-4">
                    @forelse($latestUsers as $u)
                        <div class="flex items-center justify-between p-4 rounded-3xl bg-slate-50/50 dark:bg-slate-800/30 border border-[var(--border-light)] hover:bg-white dark:hover:bg-slate-800 transition-all group">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-[var(--gold-400)] to-[var(--gold-600)] text-white flex items-center justify-center font-heading font-black text-lg shadow-lg group-hover:scale-110 transition-transform">
                                    {{ mb_substr($u->name, 0, 1) }}
                                </div>
                                <div>
                                    <div class="font-heading font-black text-slate-800 dark:text-white text-base">{{ $u->name }}</div>
                                    <div class="text-xs text-slate-500 font-medium italic">{{ $u->email }}</div>
                                </div>
                            </div>
                            <div class="text-left">
                                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest bg-slate-100 dark:bg-slate-900 px-3 py-1 rounded-full mb-1 inline-block">{{ $u->created_at->diffForHumans() }}</span>
                                <div class="flex items-center justify-end gap-1 text-emerald-500">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    <span class="text-[10px] font-bold" data-i18n="activeMember">عضو نشط</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-16">
                            <div class="w-20 h-20 bg-slate-50 dark:bg-slate-900 rounded-3xl flex items-center justify-center mx-auto mb-4 text-slate-200">
                                <i class="bi bi-person-x text-4xl"></i>
                            </div>
                            <p class="text-slate-400 font-bold italic" data-i18n="noNewUsers">لا مستخدمين جدد لهذا اليوم.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Quick Access / Info Panel -->
            <div class="card-premium p-8 shadow-2xl bg-white text-slate-900 dark:bg-black dark:text-white relative overflow-hidden border border-[var(--border-light)] dark:border-white/10">
                <div class="absolute inset-0 opacity-5 dark:opacity-10 pointer-events-none bg-[url('https://www.transparenttextures.com/patterns/carbon-fibre.png')]"></div>
                <div class="relative z-10">
                    <div class="flex items-center gap-3 mb-8">
                        <div class="w-10 h-10 rounded-2xl bg-[var(--gold-500)]/15 dark:bg-[var(--gold-500)]/20 text-[var(--gold-600)] dark:text-[var(--gold-500)] flex items-center justify-center text-lg border border-[var(--gold-500)]/25 shadow-inner">
                            <i class="bi bi-shield-lock-fill"></i>
                        </div>
                        <h5 class="text-xl font-heading font-black tracking-tight mb-0 text-slate-900 dark:text-white" data-i18n="securityTools">أدوات الأمان</h5>
                    </div>

                    <div class="space-y-4">
                        <a href="{{ route('admin.loginAttempts') }}" class="block p-5 rounded-3xl bg-slate-50 border border-slate-200 hover:bg-white transition-all group dark:bg-white/5 dark:border-white/10 dark:hover:bg-white/10">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-black text-[var(--gold-600)] group-hover:text-[var(--gold-700)] dark:text-[var(--gold-400)] dark:group-hover:text-[var(--gold-500)]" data-i18n="loginLogs">سجلات الدخول</span>
                                <i class="bi bi-arrow-left-short text-xl text-slate-400 group-hover:text-slate-600 dark:text-white/50 dark:group-hover:text-white opacity-70 group-hover:opacity-100 group-hover:translate-x-[-4px] transition-all"></i>
                            </div>
                            <p class="text-xs text-slate-500 dark:text-white/60 font-medium leading-relaxed" data-i18n="loginLogsDesc">مراقبة محاولات الدخول وحماية الحسابات من الاختراقات.</p>
                        </a>

                        <div class="p-5 rounded-3xl bg-[var(--gold-500)]/10 border border-[var(--gold-500)]/25 dark:bg-[var(--gold-600)]/10 dark:border-[var(--gold-500)]/20">
                            <div class="flex items-start gap-3">
                                <i class="bi bi-info-circle text-[var(--gold-500)] dark:text-[var(--gold-400)] text-lg"></i>
                                <div>
                                    <h6 class="text-sm font-black text-[var(--gold-700)] dark:text-[var(--gold-300)] mb-1" data-i18n="highSystemStatus">حالة النظام العالية</h6>
                                    <p class="text-[10px] text-slate-600 dark:text-white/60 font-medium" data-i18n="tlsEncryptionInfo">يتم الآن تشفير كافة التفاعلات المالية عبر بروتوكول TLS 1.3 المتقدم.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
