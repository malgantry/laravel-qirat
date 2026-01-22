@extends('layouts.app')

@section('content')
    <div class="flex flex-wrap justify-between items-center gap-4 mb-6">
        <a href="{{ url()->previous() }}" class="text-slate-500 hover:text-[var(--gold-600)] text-decoration-none flex items-center gap-2 transition order-2 sm:order-1">
            <i class="bi bi-arrow-right"></i>
            <span data-i18n="back">رجوع</span>
        </a>
        <div class="flex items-center gap-3 order-1 sm:order-2 w-full sm:w-auto justify-between sm:justify-end">
            <h3 class="mb-0 font-heading text-2xl text-text-main" data-i18n="activeGoals">الأهداف النشطة</h3>
            <a href="{{ route('goals.create') }}" class="btn-gold text-sm flex items-center gap-1"><i class="bi bi-plus-lg"></i> <span data-i18n="newGoal">هدف جديد</span></a>
        </div>
    </div>

    @if($goals->isEmpty())
        <div class="card-premium flex flex-col items-center gap-2 px-6 py-8 text-center mt-6" data-i18n="noGoalsYet">
            <div class="w-16 h-16 rounded-full bg-[var(--gold-100)] flex items-center justify-center text-[var(--gold-600)] text-2xl mb-2">
                <i class="bi bi-bullseye"></i>
            </div>
            <div class="fw-bold mb-1 text-lg text-text-main" data-i18n="noGoalsYet">لا توجد أهداف بعد</div>
            <div class="text-muted mb-4 text-sm" data-i18n="startAddingGoal">ابدأ بإضافة هدف ادخار أو شراء وسيظهر تقدمك هنا بشكل أنيق.</div>
            <a href="{{ route('goals.create') }}" class="btn-gold" data-i18n="createGoal">إنشاء هدف</a>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($goals as $goal)
                @php
                    $progress = max(0, min(100, (int)$goal->progress));
                    $remaining = max(0, ($goal->target_amount ?? 0) - ($goal->current_amount ?? 0));
                @endphp
                <div class="animate-enter">
                    <div class="goal-card-premium p-6 relative group h-full flex flex-col">
                        <!-- Action Buttons (Edit/Delete) -->
                        <div class="absolute top-4 right-4 flex gap-2 opacity-100 lg:opacity-0 lg:group-hover:opacity-100 transition-opacity z-10">
                            <a href="{{ route('goals.edit', $goal) }}" class="w-8 h-8 rounded-full bg-white/50 backdrop-blur dark:bg-slate-800/50 flex items-center justify-center text-slate-600 dark:text-slate-300 hover:text-blue-500 hover:bg-white shadow-sm transition-all" data-i18n-title="edit" title="تعديل">
                                <i class="bi bi-pencil-fill text-xs"></i>
                            </a>
                             <form action="{{ route('goals.destroy', $goal) }}" method="POST" onsubmit="return confirm(i18n[currentLang()].deleteConfirm);" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-8 h-8 rounded-full bg-white/50 backdrop-blur dark:bg-slate-800/50 flex items-center justify-center text-slate-600 dark:text-slate-300 hover:text-red-500 hover:bg-white shadow-sm transition-all" data-i18n-title="delete" title="حذف">
                                    <i class="bi bi-trash-fill text-xs"></i>
                                </button>
                            </form>
                        </div>

                        <div class="text-center mb-6 mt-2 flex-1">
                            <div class="text-lg font-bold text-text-main truncate mb-4">{{ $goal->name }}</div>
                            <div class="ring-premium mx-auto" style="--p: {{ $progress }}%">
                                <span class="ring-premium-val text-text-main">{{ $progress }}%</span>
                            </div>
                        </div>
                        
                        <div class="space-y-3 bg-white/50 dark:bg-slate-800/50 rounded-xl p-4 border border-white/20 dark:border-white/5">
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-text-muted" data-i18n="collected">تم جمع:</span>
                                <span class="font-bold text-emerald-600">{{ number_format($goal->current_amount) }}</span>
                            </div>
                            <div class="w-full h-px bg-slate-200 dark:bg-slate-700"></div>
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-text-muted" data-i18n="left">المتبقي:</span>
                                <span class="font-bold text-amber-600">{{ number_format($remaining) }}</span>
                            </div>
                        </div>

                        <!-- AI Feedback Spot for Goals (Async) -->
                        <div id="goal-ai-{{ $goal->id }}" class="mt-4" data-goal-id="{{ $goal->id }}">
                             @php
                                $progress = (int)$goal->progress;
                                $isComplete = $progress >= 100;
                             @endphp
                             @if($isComplete)
                                 <div class="ai-insight-card p-4 ai-card-success shadow-sm relative overflow-hidden">
                                    <div class="confetti-container absolute inset-0 pointer-events-none" style="z-index: 5; overflow: hidden;">
                                        @php $colors = ['#FFD700', '#C0C0C0', '#D4AF37', '#FF6B6B', '#4ECDC4']; @endphp
                                        @for($i = 0; $i < 30; $i++)
                                            @php
                                                $left = rand(0, 100);
                                                $delay = rand(0, 30) / 10; 
                                                $duration = 3 + (rand(0, 20) / 10);
                                                $bg = $colors[$i % count($colors)];
                                                $size = rand(6, 12);
                                            @endphp
                                            <div class="confetti" style="
                                                left: {{ $left }}%; 
                                                width: {{ $size }}px; 
                                                height: {{ $size * 0.4 }}px; 
                                                background-color: {{ $bg }}; 
                                                animation-delay: {{ $delay }}s;
                                                animation-duration: {{ $duration }}s;
                                                opacity: 0.8;
                                            "></div>
                                        @endfor
                                    </div>
                                    <div class="flex items-start gap-3 relative z-10">
                                        <div class="w-10 h-10 rounded-full bg-white/10 dark:bg-black/20 flex items-center justify-center flex-shrink-0 shadow-md border border-white/20 backdrop-blur-sm">
                                            <span class="text-lg animate-bounce">🏆</span>
                                        </div>
                                        <div class="flex-1">
                                            <div class="flex items-center gap-2 mb-2">
                                                <span class="text-xs font-black text-emerald-600 dark:text-emerald-400" data-i18n="congrats">مبروك!</span>
                                            </div>
                                             <p class="text-sm text-slate-700 dark:text-slate-200 leading-relaxed font-bold" data-i18n="reachedGoal" data-i18n-vars='{"amount":"{{ number_format($goal->target_amount) }}"}'>
                                                إنجاز استثنائي: مبروك بلوغك الهدف المالي المخطط له بقيمة {{ number_format($goal->target_amount) }} د.ل بنجاح.
                                             </p>
                                        </div>
                                    </div>
                                 </div>
                             @else
                                 <div class="h-24 bg-slate-100 dark:bg-slate-800 animate-pulse rounded-2xl opacity-50" id="goal-ai-skeleton-{{ $goal->id }}"></div>
                             @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-3">
            {{ $goals->links() }}
        </div>
    @endif
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Fetch AI insights for incomplete goals
        fetch('{{ route('ai.insights.goals') }}')
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                Object.entries(data.feedback).forEach(([id, feedbackRaw]) => {
                    const container = document.getElementById(`goal-ai-${id}`);
                    // If feedbackRaw is array, take first or null
                    const feedback = Array.isArray(feedbackRaw) ? (feedbackRaw.length > 0 ? feedbackRaw[0] : null) : feedbackRaw;

                    if (container && feedback) {
                        renderGoalAdvisor(container, feedback, id);
                    }
                });
                if (window.applyLanguage) applyLanguage(localStorage.getItem('preferredLanguage') || 'ar');
            }
        });

        function renderGoalAdvisor(container, feedback, id) {
            const isWarning = feedback.type === 'warning';
            const isSuccess = feedback.type === 'success';
            const statusClass = isSuccess ? 'ai-card-success' : (isWarning ? 'ai-card-warning' : 'ai-card-info');
            const colorClass = isSuccess ? 'emerald' : (isWarning ? 'amber' : 'indigo');
            const textClass = isSuccess ? 'text-emerald-600' : (isWarning ? 'text-amber-600' : 'text-indigo-600');
            const emoji = isSuccess ? '🎉' : (isWarning ? '⚡' : '💡');
            const titleKey = isWarning ? 'importantAlert' : (isSuccess ? 'greatAchievement' : 'financialAdvisor');

            // Professional Confetti Logic
            let confettiHtml = '';
            if (isSuccess) {
                confettiHtml = '<div class="confetti-container absolute inset-0 pointer-events-none" style="z-index: 5; overflow: hidden;">';
                const colors = ['#FFD700', '#C0C0C0', '#D4AF37', '#FF6B6B', '#4ECDC4'];
                for (let i = 0; i < 30; i++) { 
                    const left = Math.floor(Math.random() * 100);
                    const delay = Math.random() * 3;
                    const duration = 3 + Math.random() * 2;
                    const bg = colors[i % colors.length];
                    const size = 6 + Math.random() * 6;
                    
                    confettiHtml += `<div class="confetti" style="
                        left: ${left}%; 
                        width: ${size}px; 
                        height: ${size * 0.4}px; 
                        background-color: ${bg}; 
                        animation-delay: ${delay}s;
                        animation-duration: ${duration}s;
                        opacity: 0.8;
                    "></div>`;
                }
                confettiHtml += '</div>';
            }

            let html = `
                <div class="ai-insight-card mt-4 p-5 ${statusClass} group relative overflow-hidden animate-enter" id="ai-card-${id}">
                    ${confettiHtml}
                    <div class="flex items-start gap-3 relative z-10">
                        <div class="w-10 h-10 rounded-full bg-white/10 dark:bg-black/20 flex items-center justify-center flex-shrink-0 shadow-md border border-white/20 backdrop-blur-sm">
                            <span class="text-lg">${emoji}</span>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="text-xs font-black ${textClass}" data-i18n="${titleKey}"></span>
                                <span class="text-[9px] text-slate-400 ml-auto" data-i18n="justNow">• الآن</span>
                            </div>
                            <p class="text-sm text-slate-700 dark:text-slate-200 leading-relaxed font-bold mb-4">
                                ${feedback.message}
                            </p>
                            <div class="flex items-center gap-2">
                                <button type="button" class="px-4 py-1.5 rounded-xl bg-emerald-500 text-white text-[10px] font-black hover:scale-105 active:scale-95 transition-all shadow-md" onclick="document.getElementById('ai-card-${id}').remove()">
                                    <span data-i18n="accept">قبول</span>
                                </button>
                                <button type="button" class="px-4 py-1.5 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-500 text-[10px] font-bold hover:bg-rose-50 hover:text-rose-500 transition-all border border-slate-200/50" onclick="document.getElementById('ai-card-${id}').remove()">
                                    <span data-i18n="reject">تجاهل</span>
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
