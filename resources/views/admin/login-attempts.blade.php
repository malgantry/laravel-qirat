@extends('layouts.app')

@php use Illuminate\Support\Str; @endphp

@section('content')
    <div class="max-w-7xl mx-auto space-y-8 animate-enter">
        <!-- Login Attempts Header -->
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
            <div>
                <div class="flex items-center gap-2 text-[var(--gold-500)] font-black text-[10px] uppercase tracking-[0.2em] mb-2" data-i18n="securityAuditEngine">
                    <span class="w-8 h-px bg-[var(--gold-400)]"></span>
                    <span data-i18n="securityAuditEngine">محرك تدقيق الأمان والوصول</span>
                </div>
                <h3 class="text-4xl font-heading font-black text-text-main tracking-tight" data-i18n="loginLogs">سجلات الدخول</h3>
            </div>
            
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('admin.dashboard') }}" class="btn-soft px-8 py-3 text-sm font-bold shadow-xl border border-slate-200/50 dark:border-slate-800/50">
                    <i class="bi bi-grid-fill me-2"></i> <span data-i18n="adminDashboard">لوحة الإدارة</span>
                </a>
            </div>
        </div>

        <!-- Login Attempts Table Container -->
        <div class="card-premium overflow-hidden border-none shadow-2xl relative">
            <div class="p-8 border-b border-[var(--border-light)] flex justify-between items-center bg-white/50 dark:bg-slate-900/50 backdrop-blur-xl">
                <div>
                    <h5 class="text-xl font-heading font-black text-text-main mb-1" data-i18n="trafficLogs">سجل حركة المرور</h5>
                    <p class="text-xs text-slate-500 font-medium" data-i18n="loginLogsDesc">مراقبة محاولات الدخول وحماية الحسابات من الاختراقات.</p>
                </div>
                <div class="w-10 h-10 rounded-2xl bg-[var(--navy-900)] text-[var(--gold-500)] flex items-center justify-center text-lg shadow-inner">
                    <i class="bi bi-shield-lock-fill"></i>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="table-premium">
                    <thead>
                        <tr class="bg-slate-50/50 dark:bg-slate-950/20">
                            <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-[var(--border-light)]" data-i18n="user">المستخدم</th>
                            <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-[var(--border-light)]" data-i18n="email">البريد</th>
                            <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-[var(--border-light)]" data-i18n="result">النتيجة</th>
                            <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-[var(--border-light)]" data-i18n="ip">IP</th>
                            <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-[var(--border-light)]" data-i18n="browser">المتصفح</th>
                            <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-[var(--border-light)]" data-i18n="time">الوقت</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($attempts as $a)
                            <tr class="group hover:bg-[var(--gold-50)]/30 dark:hover:bg-[var(--gold-900)]/5 transition-colors border-b border-[var(--border-light)] last:border-0">
                                <td class="px-8 py-6">
                                    <div class="font-heading font-black text-slate-800 dark:text-white text-sm tracking-tight">
                                        {{ optional($a->user)->name ?? 'غير معروف' }}
                                    </div>
                                </td>
                                <td class="px-8 py-6">
                                    <div class="text-xs text-slate-500 font-medium italic">{{ $a->email }}</div>
                                </td>
                                <td class="px-8 py-6">
                                    @if($a->success)
                                        <span class="inline-flex items-center px-4 py-1 rounded-full bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400 text-[10px] font-black uppercase tracking-widest border border-emerald-100 dark:border-emerald-900/30">
                                            <i class="bi bi-check-circle-fill me-2"></i> <span data-i18n="success">ناجحة</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-4 py-1 rounded-full bg-rose-50 dark:bg-rose-900/20 text-rose-600 dark:text-rose-400 text-[10px] font-black uppercase tracking-widest border border-rose-100 dark:border-rose-900/30">
                                            <i class="bi bi-x-circle-fill me-2"></i> <span data-i18n="failed">فاشلة</span>
                                        </span>
                                    @endif
                                </td>
                                <td class="px-8 py-6">
                                    <div class="text-[10px] font-black text-slate-400 font-mono">{{ $a->ip_address }}</div>
                                </td>
                                <td class="px-8 py-6">
                                    <div class="text-[10px] text-slate-500 font-medium leading-tight max-w-[200px] truncate" title="{{ $a->user_agent }}">
                                        {{ $a->user_agent }}
                                    </div>
                                </td>
                                <td class="px-8 py-6">
                                    <div class="text-[9px] font-black text-text-muted uppercase tracking-tighter">{{ optional($a->created_at)->format('Y/m/d H:i') }}</div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination Container -->
            <div class="p-8 border-t border-[var(--border-light)] bg-slate-50/30 dark:bg-slate-950/10">
                <div class="flex justify-center">
                    {{ $attempts->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
