@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-12">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-10 animate-enter">
        <div>
            <h1 class="text-4xl font-heading font-black text-text-main tracking-tight flex items-center gap-4 mb-2">
                <div class="w-12 h-12 rounded-2xl bg-[var(--gold-500)] text-slate-900 flex items-center justify-center shadow-lg">
                    <i class="bi bi- megaphone-fill"></i>
                </div>
                <span data-i18n="operationCenter">مركز العمليات</span>
            </h1>
            <p class="text-text-muted font-medium" data-i18n="notificationHubDesc">تنبيهات النظام، توجيهات الذكاء الاصطناعي، وتقارير الأداء.</p>
        </div>
        <div class="flex items-center gap-3">
            <button id="markAllReadPage" class="btn-soft bg-white/50 dark:bg-slate-800/50 backdrop-blur px-6 py-3 rounded-2xl border border-slate-200 dark:border-white/5 text-sm font-bold flex items-center gap-2 hover:bg-white dark:hover:bg-slate-800 transition-all">
                <i class="bi bi-check-all text-xl"></i> <span data-i18n="markAllRead">تحديد الكل كمقروء</span>
            </button>
        </div>
    </div>

    <!-- Hub Categories -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8 animate-enter" style="animation-delay: 100ms">
        <div class="glass-panel p-4 rounded-3xl border-emerald-500/10 flex flex-col items-center justify-center text-center hover:scale-105 transition-transform cursor-pointer">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 text-emerald-500 flex items-center justify-center mb-2 shadow-inner">
                <i class="bi bi-shield-check"></i>
            </div>
            <span class="text-xs font-black text-slate-700 dark:text-slate-300" data-i18n="securityAlerts">تنبيهات أمنية</span>
            <span class="text-[10px] text-slate-500 mt-1">12 {{ __('notification') }}</span>
        </div>
        <div class="glass-panel p-4 rounded-3xl border-amber-500/10 flex flex-col items-center justify-center text-center hover:scale-105 transition-transform cursor-pointer">
            <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-900/20 text-amber-500 flex items-center justify-center mb-2 shadow-inner">
                <i class="bi bi-stars"></i>
            </div>
            <span class="text-xs font-black text-slate-700 dark:text-slate-300" data-i18n="aiDirections">توجيهات الذكاء</span>
            <span class="text-[10px] text-slate-500 mt-1">5 {{ __('notification') }}</span>
        </div>
        <div class="glass-panel p-4 rounded-3xl border-indigo-500/10 flex flex-col items-center justify-center text-center hover:scale-105 transition-transform cursor-pointer">
            <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-900/20 text-indigo-500 flex items-center justify-center mb-2 shadow-inner">
                <i class="bi bi-graph-up-arrow"></i>
            </div>
            <span class="text-xs font-black text-slate-700 dark:text-slate-300" data-i18n="performanceReports">تقارير الأداء</span>
            <span class="text-[10px] text-slate-500 mt-1">3 {{ __('notification') }}</span>
        </div>
        <div class="glass-panel p-4 rounded-3xl border-slate-500/10 flex flex-col items-center justify-center text-center border-dashed opacity-60">
            <div class="w-10 h-10 rounded-xl bg-slate-50 dark:bg-slate-800/50 text-slate-400 flex items-center justify-center mb-2 shadow-inner">
                <i class="bi bi-plus-lg"></i>
            </div>
            <span class="text-xs font-black text-slate-500" data-i18n="addCustom">إضافة مخصص</span>
        </div>
    </div>

    <!-- Notifications List -->
    <div class="glass-panel p-0 rounded-[32px] border-white/20 dark:border-white/10 shadow-2xl overflow-hidden animate-enter" style="animation-delay: 200ms">
        @if($notifications->count() > 0)
            <div class="divide-y divide-slate-100 dark:divide-white/5">
                @foreach($notifications as $notification)
                    <div class="p-8 hover:bg-slate-50/80 dark:hover:bg-white/5 transition-all group {{ $notification->read_at ? 'opacity-60 grayscale-[0.3]' : 'bg-gold-50/30' }}">
                        <div class="flex gap-6">
                            <div class="w-14 h-14 rounded-2xl bg-white dark:bg-slate-800 flex items-center justify-center shrink-0 text-2xl shadow-premium border border-slate-100 dark:border-white/5 group-hover:scale-110 transition-transform {{ $notification->data['color'] ?? 'text-slate-500' }}">
                                <i class="bi {{ $notification->data['icon'] ?? 'bi-bell-fill' }}"></i>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center gap-3">
                                        <h3 class="text-lg font-black text-slate-900 dark:text-white" data-i18n="{{ isset($notification->data['title']) ? '' : 'notification' }}">
                                            {{ $notification->data['title'] ?? 'تنبيه' }}
                                        </h3>
                                        @if(!$notification->read_at)
                                            <span class="w-2 h-2 rounded-full bg-[var(--gold-500)] animate-pulse shadow-[0_0_8px_var(--gold-400)]"></span>
                                        @endif
                                    </div>
                                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{{ $notification->created_at->diffForHumans() }}</span>
                                </div>
                                <p class="text-slate-600 dark:text-slate-300 leading-relaxed font-medium mb-4">
                                    {{ $notification->data['body'] ?? '' }}
                                </p>
                                @if(isset($notification->data['link']) && $notification->data['link'] !== '#')
                                    <a href="{{ $notification->data['link'] }}" class="btn-gold px-6 py-2.5 rounded-xl text-xs flex items-center gap-2 group/btn">
                                        <span data-i18n="details">عرض التفاصيل</span> 
                                        <i class="bi bi-arrow-right group-hover/btn:translate-x-1 transition-transform"></i>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            @if($notifications->hasPages())
                <div class="p-6 border-t border-slate-100 dark:border-white/5">
                    {{ $notifications->links() }}
                </div>
            @endif
        @else
            <div class="p-20 text-center animate-pulse">
                <div class="w-24 h-24 bg-slate-100 dark:bg-white/5 rounded-full flex items-center justify-center mx-auto mb-6 text-4xl text-slate-300 dark:text-slate-600">
                    <i class="bi bi-broadcast"></i>
                </div>
                <h3 class="font-black text-2xl text-slate-800 dark:text-slate-200 mb-2" data-i18n="noNotificationsPage">لا توجد تنبيهات</h3>
                <p class="text-slate-500 font-medium" data-i18n="latestActivityInfo">سنقوم بإعلامك بأحدث النشاطات هنا.</p>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
    document.getElementById('markAllReadPage')?.addEventListener('click', () => {
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        fetch('{{ route('notifications.readAll') }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': token }
        }).then(() => {
            window.location.reload();
        });
    });
</script>
@endpush
@endsection
