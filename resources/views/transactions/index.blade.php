@extends('layouts.app')

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3 animate-enter">
        <h3 class="text-2xl font-bold font-heading text-text-main" data-i18n="transactionHistory">سجل المعاملات</h3>
        <a href="{{ route('transactions.create') }}" class="btn-gold flex items-center gap-2">
            <i class="bi bi-plus-lg"></i>
            <span data-i18n="newTransaction">معاملة جديدة</span>
        </a>
    </div>

    <form method="GET" action="{{ route('transactions.index') }}" class="space-y-3">
        <div class="flex flex-wrap items-center gap-2">
            <div class="relative flex-1 min-w-[260px] max-w-xl">
                <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"><i class="bi bi-search"></i></span>
                <input
                    type="text"
                    name="q"
                    value="{{ request('q') }}"
                    class="input-premium pl-10"
                    data-i18n-placeholder="searchPlaceholder"
                    placeholder="Search description or category"
                >
            </div>
            @if(request('q') || request('type'))
                <a href="{{ route('transactions.index') }}" class="btn-soft flex items-center gap-1 text-red-500 hover:bg-red-50">
                    <i class="bi bi-x-circle"></i>
                    <span data-i18n="clear">مسح</span>
                </a>
            @endif
        </div>
        <div class="flex flex-wrap gap-2">
            @php $q = request('q'); @endphp
            <a href="{{ route('transactions.index') }}"
               class="btn-soft text-sm {{ !request('type') ? 'active bg-[var(--gold-100)] dark:bg-[var(--gold-900)]/30 text-[var(--gold-700)] dark:text-[var(--gold-300)]' : '' }}">
                <i class="bi bi-funnel"></i> <span data-i18n="all">الكل</span>
            </a>
            <a href="{{ route('transactions.index', ['type' => 'income'] + ($q ? ['q' => $q] : [])) }}"
               class="btn-soft text-sm {{ request('type')==='income' ? 'active bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-300 border-emerald-100 dark:border-emerald-800' : '' }}">
                <i class="bi bi-graph-up-arrow"></i> <span data-i18n="income">الدخل</span>
            </a>
            <a href="{{ route('transactions.index', ['type' => 'expense'] + ($q ? ['q' => $q] : [])) }}"
               class="btn-soft text-sm {{ request('type')==='expense' ? 'active bg-rose-50 dark:bg-rose-900/20 text-rose-700 dark:text-rose-300 border-rose-100 dark:border-rose-800' : '' }}">
                <i class="bi bi-graph-down"></i> <span data-i18n="expense">المصروفات</span>
            </a>
        </div>
    </form>

    @if($transactions->isEmpty())
        <div class="card-premium flex flex-col items-center gap-2 px-6 py-8 text-center mt-6">
            <div class="w-16 h-16 rounded-full bg-[var(--gold-100)] flex items-center justify-center text-[var(--gold-600)] text-2xl mb-2">
                <i class="bi bi-stars"></i>
            </div>
            <h5 class="text-lg font-bold text-text-main" data-i18n="noTransactionsYet">لا توجد معاملات بعد</h5>
            <p class="mb-4 text-sm text-muted" data-i18n="startAddingTransactions">ابدأ بإضافة الفئات الأساسية ثم سجّل أول عملية دخل أو مصروف.</p>
            <div class="flex flex-wrap justify-center gap-2">
                <a href="{{ route('transactions.create') }}" class="btn-gold shadow-sm" data-i18n="addTransaction">إضافة معاملة</a>
                <a href="{{ route('budgets.index') }}" class="btn-soft" data-i18n="viewBudgets">عرض الميزانيات</a>
            </div>
        </div>
    @else
        @php
            $catColors = [
                'طعام' => '#fb923c', 'تسوق' => '#a855f7', 'فواتير' => '#ef4444', 'ترفيه' => '#f472b6',
                'هاتف' => '#38bdf8', 'رياضة' => '#4ade80', 'تجميل' => '#f472b6', 'تعليم' => '#6366f1',
                'اجتماعي' => '#f59e0b', 'راتب' => '#10b981', 'مكافأة' => '#34d399', 'استثمار' => '#059669',
                'تحويل' => '#6366f1', 'مواصلات' => '#38bdf8', 'صحة' => '#f43f5e', 'هدايا' => '#f59e0b',
                'غير مصنف' => '#94a3b8'
            ];

            $catMap = [
                'طعام' => 'food', 'تسوق' => 'shopping', 'فواتير' => 'bills', 'ترفيه' => 'entertainment',
                'هاتف' => 'phone', 'رياضة' => 'sports', 'تجميل' => 'beauty', 'تعليم' => 'education',
                'اجتماعي' => 'social', 'راتب' => 'salary', 'مكافأة' => 'bonus', 'استثمار' => 'investment',
                'تحويل' => 'transfer', 'صحة' => 'health', 'مواصلات' => 'transport', 'هدايا' => 'gifts',
                'غير مصنف' => 'uncategorized'
            ];
        @endphp
        <div class="space-y-4 animate-enter mt-6">
            @foreach($transactions as $transaction)
                <div class="card-premium p-4 flex flex-col sm:flex-row gap-4 items-center relative overflow-hidden" data-transaction-id="{{ $transaction->id }}">
                    @php
                        $cat = $transaction->categoryRef;
                        $isIncome = $transaction->type === 'income';
                        // Robust Color Fallback: DB Color -> Mapped Color -> Income/Expense Default
                        $catColor = $cat?->color ?? ($catColors[$cat?->name ?? $transaction->category] ?? ($isIncome ? '#10b981' : '#f43f5e'));
                        
                        // Detect Savings Transaction (Expense linked to Goal)
                        $isSavings = !$isIncome && $transaction->goal_id;
                        $displayIcon = $cat?->icon ?? ($isIncome ? 'bi-arrow-down-left' : 'bi-bag');
                        
                        if ($isSavings) {
                           $catColor = '#6366f1'; // Indigo for Savings
                           $displayIcon = 'bi-piggy-bank'; 
                        }
                    @endphp
                    
                    @if($isSavings)
                        <div class="absolute top-0 right-0 w-16 h-16 bg-indigo-500/10 rounded-bl-full pointer-events-none"></div>
                    @endif
                    
                    <div class="w-12 h-12 rounded-full flex items-center justify-center text-white text-lg shadow-sm shrink-0" style="background-color: {{ $catColor }};">
                        <i class="bi {{ $displayIcon }}"></i>
                    </div>

                    <div class="flex-1 text-center sm:text-right w-full sm:w-auto z-10">
                        <div class="flex items-center justify-center sm:justify-start gap-2 mb-1">
                            <span class="text-base font-bold text-text-main" data-i18n="{{ $cat ? ($catMap[$cat->name] ?? 'uncategorized') : 'uncategorized' }}">{{ $cat?->name ?? $transaction->category }}</span>
                            
                            @if($isSavings)
                                <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-indigo-50 dark:bg-indigo-900/20 text-indigo-600 dark:text-indigo-400">
                                    <i class="bi bi-piggy-bank"></i> <span data-i18n="savings">ادخار</span>
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-xs font-bold" style="background-color: {{ $catColor }}20; color: {{ $catColor }};">
                                    <span data-i18n="{{ $isIncome ? 'income' : 'expense' }}">{{ $isIncome ? 'دخل' : 'مصروف' }}</span>
                                </span>
                            @endif

                            @if($isIncome && $transaction->goal_id)
                                <span class="flex items-center gap-1 px-2 py-0.5 rounded-full border border-emerald-100 dark:border-emerald-800 bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400 text-[10px] font-bold">
                                    <i class="bi bi-arrow-right-circle"></i>
                                    <span data-i18n="allocatedTo">مخصص لـ:</span> {{ $transaction->goal->name ?? 'هدف' }}
                                </span>
                            @endif
                        </div>
                        <div class="text-sm text-text-muted mb-1">
                            {{ optional($transaction->occurred_at)->toDateString() }} 
                            @if($transaction->note) &bull; {{ $transaction->note }} @endif
                        </div>
                        
                        <!-- AI Feedback Placeholder -->
                        <div class="mt-2 text-right" id="tx-ai-{{ $transaction->id }}">
                             <div class="h-4 w-32 bg-slate-100 dark:bg-slate-800 animate-pulse rounded ml-0 mr-auto opacity-0" id="tx-ai-skeleton-{{ $transaction->id }}"></div>
                        </div>
                    </div>

                    <div class="text-center sm:text-left flex flex-col items-center sm:items-end gap-2 min-w-[120px] z-10">
                        <div class="text-lg font-heading font-bold {{ $isIncome ? 'text-green-600' : ($isSavings ? 'text-indigo-600' : 'text-text-main') }}">
                            {{ $isIncome ? '+' : '-' }}{{ number_format($transaction->amount, 2) }}
                        </div>
                        
                        <div class="flex gap-2">
                             <a href="{{ route('transactions.edit', $transaction) }}" class="p-2 rounded hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-400 hover:text-blue-500 transition" data-i18n-title="edit" title="تعديل">
                                <i class="bi bi-pencil-square"></i>
                             </a>
                             <form action="{{ route('transactions.destroy', $transaction) }}" method="POST" class="inline" onsubmit="return confirm(i18n[currentLang()].deleteConfirm);">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-2 rounded hover:bg-red-50 dark:hover:bg-red-900/20 text-slate-400 hover:text-red-500 transition" data-i18n-title="delete" title="حذف">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $transactions->links() }}
        </div>
    @endif
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const txIds = Array.from(document.querySelectorAll('[data-transaction-id]'))
            .map(el => el.dataset.transactionId);
        
        if (txIds.length > 0) {
            txIds.forEach(id => {
                const skel = document.getElementById(`tx-ai-skeleton-${id}`);
                if (skel) skel.classList.remove('opacity-0');
            });

            fetch(`{{ route('ai.insights.transactions') }}?ids=${txIds.join(',')}`)
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    Object.entries(data.feedback).forEach(([id, feedbackRaw]) => {
                         const container = document.getElementById(`tx-ai-${id}`);
                         // Handle Array vs Object for robustness
                         const feedback = Array.isArray(feedbackRaw) ? (feedbackRaw.length > 0 ? feedbackRaw[0] : null) : feedbackRaw;
                         
                        if (container && feedback) {
                            renderInsightTo(container, feedback, 'transaction', id);
                        }
                    });
                    if (window.applyLanguage) applyLanguage(localStorage.getItem('preferredLanguage') || 'ar');
                }
            });
        }

        function renderInsightTo(container, feedback, type, id) {
            const isWarning = feedback.type === 'warning';
            const isSuccess = feedback.type === 'success';
            const severity = (feedback.priority ?? 5) >= 9 ? 'مرتفع' : ((feedback.priority ?? 5) >= 6 ? 'متوسط' : 'منخفض');

            const catQuery = encodeURIComponent(feedback.vars?.category || '');
            const colorClass = isSuccess ? 'green' : (isWarning ? 'red' : 'indigo');
            const gradient = isSuccess 
                ? 'from-green-50 to-emerald-50 dark:from-green-900/40 dark:to-emerald-950/20 border-r-4 border-r-green-500 dark:border-r-green-600' 
                : (isWarning ? 'from-red-50 to-rose-50 dark:from-red-900/40 dark:to-rose-950/20 border-r-4 border-r-red-500 dark:border-r-red-600' : 'from-purple-50 to-indigo-50 dark:from-purple-900/40 dark:to-indigo-950/20 border-purple-400 dark:border-indigo-400');

            const iconBg = isSuccess 
                ? 'bg-white/50 border-2 border-white/20' 
                : (isWarning ? 'bg-amber-100 dark:bg-amber-900/30' : 'bg-white/50');
            
            const emoji = isSuccess ? '🏆' : (isWarning ? '⚡' : '💡');
            
            let html = `
                <div class="ai-insight-card mt-3 p-4 rounded-2xl bg-gradient-to-br ${gradient} border border-opacity-50 shadow-sm relative overflow-hidden animate-enter group" data-feedback-id="${feedback.id}">
                    <div class="flex items-start gap-3 relative z-10">
                        <div class="w-10 h-10 rounded-full ${iconBg} flex items-center justify-center shrink-0 shadow-sm border-2 border-white/20">
                            <span class="text-lg animate-bounce">${emoji}</span>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-1">
                                <p class="text-xs font-bold opacity-70 ${isSuccess ? 'text-green-700' : (isWarning ? 'text-rose-700' : 'text-indigo-700')}">
                                    ${isSuccess ? 'ممتاز!' : (isWarning ? 'تنبيه' : 'نصيحة')}
                                </p>
                                <span class="text-[10px] font-black px-2 py-0.5 rounded-full bg-white/60 dark:bg-slate-800/60 text-slate-600 dark:text-slate-200 border border-white/40">${severity}</span>
                            </div>
                            <p class="text-[13px] font-bold leading-relaxed mb-3 text-slate-700 dark:text-slate-200">
                                ${feedback.message}
                            </p>
                            <div class="flex items-center gap-2 opacity-80 group-hover:opacity-100 transition-opacity flex-wrap">
                                <button type="button" class="px-3 py-1 rounded-lg text-[10px] font-bold shadow-sm hover:scale-105 transition-transform bg-slate-900 text-white dark:bg-slate-700 dark:text-white border border-slate-800" onclick="this.closest('.ai-insight-card')?.remove()">
                                    <i class="bi bi-check2-circle text-white mr-1"></i> <span data-i18n="accept">قبول</span>
                                </button>
                                <button type="button" class="px-3 py-1 rounded-lg text-[10px] font-bold shadow-sm hover:scale-105 transition-transform bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-200 border border-slate-200 dark:border-slate-600" onclick="this.closest('.ai-insight-card')?.remove()">
                                    <i class="bi bi-x-circle text-slate-500 dark:text-slate-300 mr-1"></i> <span data-i18n="reject">رفض</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>`;
            container.innerHTML = html;
        }
    });
</script>
@endpush
