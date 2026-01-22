@extends('layouts.app')

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-4 mb-6 animate-enter">
        <h3 class="text-2xl font-bold font-heading text-text-main" data-i18n="budgets">الميزانيات</h3>
        <a href="{{ route('budgets.create') }}" class="btn-gold flex items-center gap-2 text-sm">
            <i class="bi bi-plus-lg"></i>
            <span data-i18n="newBudget">ميزانية جديدة</span>
        </a>
    </div>

    @if($budgets->isEmpty())
        <div class="card-premium flex flex-col items-center gap-2 px-6 py-8 text-center mt-6">
            <div class="w-16 h-16 rounded-full bg-[var(--gold-100)] flex items-center justify-center text-[var(--gold-600)] text-2xl mb-2">
                <i class="bi bi-wallet2"></i>
            </div>
            <h5 class="text-lg font-bold text-text-main" data-i18n="noBudgetsYet">لا توجد ميزانيات بعد</h5>
            <p class="mb-4 text-sm text-muted" data-i18n="addCategoryLimit">أضف فئة ثم حدد حد شهري لمراقبة المصروف والالتزام بالخطة.</p>
            <a href="{{ route('budgets.create') }}" class="btn-gold shadow-sm" data-i18n="createBudget">إنشاء ميزانية</a>
        </div>
    @else
        @php
            $catMap = [
                'طعام' => 'food', 'تسوق' => 'shopping', 'فواتير' => 'bills', 'ترفيه' => 'entertainment',
                'هاتف' => 'phone', 'رياضة' => 'sports', 'تجميل' => 'beauty', 'تعليم' => 'education',
                'اجتماعي' => 'social', 'راتب' => 'salary', 'مكافأة' => 'bonus', 'استثمار' => 'investment',
                'تحويل' => 'transfer', 'صحة' => 'health', 'مواصلات' => 'transport', 'هدايا' => 'gifts',
                'غير مصنف' => 'uncategorized'
            ];
            // Define colors for fallback
            $catColors = [
                'طعام' => '#fb923c', 'تسوق' => '#a855f7', 'فواتير' => '#ef4444', 'ترفيه' => '#f472b6',
                'هاتف' => '#38bdf8', 'رياضة' => '#4ade80', 'تجميل' => '#f472b6', 'تعليم' => '#6366f1',
                'اجتماعي' => '#f59e0b', 'راتب' => '#10b981', 'مكافأة' => '#34d399', 'استثمار' => '#059669',
                'تحويل' => '#6366f1', 'مواصلات' => '#38bdf8', 'صحة' => '#f43f5e', 'هدايا' => '#f59e0b',
                'غير مصنف' => '#94a3b8'
            ];
        @endphp
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 animate-enter">
            @foreach($budgets as $budget)
                @php
                    $cat = $budget->category;
                    $icon = $cat?->icon;
                    $catName = $cat?->name ?? 'غير مصنف';
                    $color = $cat?->color ?: ($catColors[$catName] ?? '#f59e0b');
                    
                    $limit = (float) ($budget->limit_amount ?? 0);
                    $spentVal = (float) ($budget->spent_amount ?? 0);
                    $pct = $limit > 0 ? min(100, round(($spentVal / $limit) * 100)) : 0;
                    $isOver = $spentVal > $limit;
                @endphp
                <div class="card-premium p-5 flex flex-col justify-between h-full">
                    <div>
                        <div class="flex items-center gap-4 mb-4">
                            <div class="w-12 h-12 rounded-xl flex items-center justify-center text-white text-lg shadow-sm" style="background: linear-gradient(135deg, {{ $color }}, {{ $color }}dd);">
                                <i class="bi {{ $icon ?? 'bi-wallet2' }}"></i>
                            </div>
                            <div class="flex-1">
                                <h5 class="font-bold text-lg text-text-main mb-1">
                                    <span data-i18n="{{ $cat ? ($catMap[$cat->name] ?? 'uncategorized') : 'uncategorized' }}">{{ $cat?->name ?? '—' }}</span>
                                </h5>
                                <div class="flex items-center gap-2 text-xs text-muted">
                                    <i class="bi bi-calendar3"></i>
                                    {{ optional($budget->period_start)->toDateString() }} &rarr; {{ optional($budget->period_end)->toDateString() }}
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <div class="flex justify-between items-end mb-2">
                                <span class="text-sm font-semibold text-text-muted" data-i18n="spent">المصروف</span>
                                <span class="font-bold {{ $isOver ? 'text-red-500' : 'text-text-main' }}">
                                    {{ number_format($spentVal, 2) }} <span class="text-xs font-normal text-text-muted">/ {{ number_format($limit, 2) }}</span>
                                </span>
                            </div>
                            <div class="h-2.5 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden border border-[var(--border-light)]">
                                <div class="h-full rounded-full transition-all duration-500 {{ $isOver ? 'bg-red-500' : 'bg-[var(--gold-500)]' }}" style="width: {{ $pct }}%"></div>
                            </div>
                            <div class="text-right mt-1 text-xs font-bold {{ $isOver ? 'text-red-500' : 'text-text-muted' }}">
                                {{ $pct }}%
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-[var(--border-light)] pt-4 flex items-center justify-between">
                        <span class="chip {{ ($budget->status === 'active') ? 'bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-300' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400' }}" data-i18n="{{ $budget->status === 'active' ? 'active' : 'completed' }}">
                            <span data-i18n="{{ $budget->status === 'active' ? 'activeStatus' : 'completedStatus' }}">{{ $budget->status === 'active' ? 'نشط' : 'مكتمل' }}</span>
                        </span>
                        
                        <div class="flex items-center gap-2">
                            <a href="{{ route('budgets.edit', $budget) }}" class="icon-btn text-slate-400 hover:text-blue-500" data-i18n-title="edit" title="تعديل">
                                <i class="bi bi-pencil-square"></i>
                            </a>
                            <form action="{{ route('budgets.destroy', $budget) }}" method="POST" onsubmit="return confirm(i18n[currentLang()].deleteConfirm);">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="icon-btn text-slate-400 hover:text-red-500" data-i18n-title="delete" title="حذف">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- AI Budget Insight (Async) -->
                    <div id="budget-ai-{{ $budget->id }}" class="mt-4 hidden empty:hidden"></div>
                </div>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $budgets->links() }}
        </div>
    @endif
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Fetch AI insights for budgets
        fetch('{{ route('ai.insights.budgets') }}')
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                Object.entries(data.feedback).forEach(([id, feedbackRaw]) => {
                    const container = document.getElementById(`budget-ai-${id}`);
                    const feedback = Array.isArray(feedbackRaw) ? (feedbackRaw.length > 0 ? feedbackRaw[0] : null) : feedbackRaw;

                    if (container && feedback) {
                        container.classList.remove('hidden');
                        renderBudgetInsight(container, feedback, id);
                    }
                });
                if (window.applyLanguage) applyLanguage(localStorage.getItem('preferredLanguage') || 'ar');
            }
        });

        function renderBudgetInsight(container, feedback, id) {
            const isWarning = feedback.type === 'warning';
            const isSuccess = feedback.type === 'success';

            const bgClass = isWarning 
                ? 'bg-gradient-to-br from-red-50 to-rose-50 dark:from-red-900/40 dark:to-rose-950/20 border-red-500 dark:border-red-400' 
                : (isSuccess ? 'bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/40 dark:to-emerald-950/20 border-green-500 dark:border-green-400' : 'bg-gradient-to-br from-purple-50 to-indigo-50 dark:from-purple-900/40 dark:to-indigo-950/20 border-purple-400 dark:border-indigo-400');
                
            const textClass = isWarning ? 'text-red-700 dark:text-red-300' : (isSuccess ? 'text-green-700 dark:text-green-300' : 'text-indigo-700 dark:text-indigo-300');
            const icon = isWarning ? 'bi-exclamation-triangle-fill' : (isSuccess ? 'bi-check-circle-fill' : 'bi-lightbulb-fill');

            let html = `
                <div class="ai-insight-card p-4 rounded-2xl ${bgClass} border-r-4 shadow-sm animate-enter flex items-start gap-3">
                    <div class="shrink-0 mt-0.5 ${textClass}">
                        <i class="bi ${icon}"></i>
                    </div>
                    <div class="flex-1">
                         <p class="text-xs font-bold ${textClass} mb-1">
                            ${isWarning ? 'تنبيه الميزانية' : (isSuccess ? 'أداء ممتاز' : 'ملاحظة')}
                         </p>
                         <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed mb-2">
                            ${feedback.message}
                         </p>
                         <button class="text-[10px] font-bold underline ${textClass} hover:opacity-75 transition-opacity" onclick="this.closest('.p-3').remove()">
                            <span data-i18n="dismiss">تجاهل</span>
                         </button>
                    </div>
                </div>
            `;
            container.innerHTML = html;
        }
    });
</script>
@endpush
