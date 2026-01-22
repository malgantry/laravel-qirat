@extends('layouts.app')

@section('hideThemeToggle')
@endsection

@section('content')
    
    <script>
        window.dashboardData = @json($dashboardData ?? []);
    </script>
    @php
        $income = $dashboardData['totalIncome'] ?? 0;
        $expense = $dashboardData['totalExpense'] ?? 0;
        $balance = $income - $expense;
        $transactions = $latestTransactions ?? collect();
        $goalsList = $goals ?? collect();
        $activeGoals = $goalsList->filter(fn ($g) => $g->progress < 100);

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

    <!-- Custom Styles (Animations only) -->
    <style>
        @keyframes confetti-fall {
            0% { transform: translateY(-10px) rotate3d(0,0,1,0deg); opacity: 1; }
            25% { transform: translateY(40px) rotate3d(1,1,1,60deg) translateX(10px); }
            50% { transform: translateY(100px) rotate3d(1,1,1,120deg) translateX(-10px); }
            75% { transform: translateY(180px) rotate3d(1,1,1,240deg) translateX(5px); }
            100% { transform: translateY(280px) rotate3d(1,1,1,360deg) translateX(0); opacity: 0; }
        }
    </style>

    <section class="hero mb-8 space-y-6 stagger-item delay-1">
        <div class="flex flex-col md:flex-row justify-between items-center gap-6">
            <div class="space-y-4">
               
            
                <div>
                    
                    <h1 class="text-4xl font-heading font-black text-text-main tracking-tight" data-i18n="welcome">
                        مرحباً بك في قيراط
                    </h1>
                    <p class="text-lg text-text-muted max-w-2xl font-medium" data-i18n="dashboardTagline">
                        لوحة التحكم المالية الخاصة بك، بتصميم عصري وأدوات متقدمة.
                 </p>
                </div>
            </div>
            <div class="flex gap-4">
                <a class="btn-gold shadow-xl shadow-amber-500/20 flex items-center gap-3 px-8 py-4 rounded-[22px] transform transition-all" href="{{ route('transactions.create') }}">
                    <i class="bi bi-plus-lg text-lg"></i> <span class="font-black" data-i18n="transaction">معاملة الجديدة</span>
                </a>
                <a class="btn-soft bg-white/40 dark:bg-slate-800/40 backdrop-blur-md border border-slate-100 dark:border-white/5 shadow-sm flex items-center gap-3 px-8 py-4 rounded-[22px] hover:bg-white dark:hover:bg-slate-800 hover:shadow-md transition-all group" href="{{ route('goals.create') }}">
                    <i class="bi bi-bullseye text-[var(--gold-500)] text-xl group-hover:scale-110 transition-transform"></i> <span class="font-black" data-i18n="goal">إضافة هدف</span>
                </a>
            </div>
        </div>

        <!-- AI Insights Banner (Async Loaded) -->
        <div id="ai-insights-container" class="mt-6 mb-2 hidden animate-fade-in-up">
            <div class="relative overflow-hidden rounded-[24px] p-[1px] bg-gradient-to-r from-[var(--gold-400)]/30 to-purple-500/30">
                <div class="absolute inset-0 bg-white/60 dark:bg-slate-900/80 backdrop-blur-xl rounded-[23px]"></div>
                <div class="relative p-5 flex flex-col md:flex-row gap-5 items-center">
                    <div class="flex-shrink-0">
                        <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-[var(--gold-500)] to-amber-600 flex items-center justify-center text-white shadow-lg shadow-amber-500/30 animate-pulse-slow">
                            <i class="bi bi-stars text-2xl"></i>
                        </div>
                    </div>
                    <div class="flex-grow text-center md:text-right">
                        <h3 class="font-bold text-lg text-slate-800 dark:text-white flex items-center justify-center md:justify-start gap-2 mb-2">
                             تحليلات قيراط المتقدمة
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-purple-500/10 text-purple-600 dark:text-purple-400 border border-purple-500/20">AI POWERED</span>
                        </h3>
                        <div id="ai-insights-content" class="text-sm font-medium text-slate-600 dark:text-slate-300 space-y-1">
                            <!-- Dynamic Content -->
                             <span class="inline-block w-4 h-4 border-2 border-[var(--gold-500)] border-t-transparent rounded-full animate-spin"></span> جاري تحليل بياناتك المالية...
                        </div>
                    </div>
                     <div class="flex-shrink-0">
                        <button onclick="document.getElementById('ai-insights-container').remove()" class="w-8 h-8 rounded-full hover:bg-black/5 dark:hover:bg-white/5 flex items-center justify-center text-slate-400 transition-colors">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6 items-stretch stagger-item delay-2">
            <!-- Health Card (Enhanced) -->
            <div class="card-premium relative overflow-hidden border-0 shadow-2xl p-0">
                                <!-- Background is now handled by .card-premium classes in SCSS -->
                <!-- Decorative Glows -->
                <div class="absolute top-0 right-0 w-64 h-64 bg-[var(--gold-500)] opacity-10 blur-[80px] rounded-full translate-x-1/3 -translate-y-1/3 pointer-events-none"></div>
                <div class="absolute bottom-0 left-0 w-48 h-48 bg-blue-500 opacity-10 blur-[60px] rounded-full -translate-x-1/3 translate-y-1/3 pointer-events-none"></div>
                
                <div class="relative z-10 p-6 sm:p-8 h-full flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-6">
                            <div>
                                <div class="text-text-muted text-sm font-medium mb-1" data-i18n="currentBalance">الرصيد الحالي</div>
                                <div class="text-3xl sm:text-4xl font-heading font-bold tracking-tight text-text-main">
                                    {{ number_format($balance, 2) }} <span class="text-lg sm:text-xl font-normal text-[var(--gold-600)] dark:text-[var(--gold-400)]">د.ل</span>
                                </div>
                            </div>
                            <div class="flex gap-4">
                                <!-- Place for potential future actions or leave empty for cleaner look -->
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3 sm:gap-4">
                            <div class="p-3 sm:p-4 rounded-xl bg-slate-100/80 dark:bg-white/5 border border-slate-200/50 dark:border-white/5 backdrop-blur-sm hover:bg-slate-200/80 dark:hover:bg-white/10 transition-colors group">
                                <div class="flex items-center gap-2 text-emerald-600 dark:text-emerald-400 mb-2 font-bold text-[10px] sm:text-xs uppercase tracking-wider">
                                    <i class="bi bi-arrow-down-left group-hover:scale-110 transition-transform"></i> <span data-i18n="income">دخل</span>
                                </div>
                                <div class="text-lg sm:text-xl font-bold font-heading text-text-main truncate">{{ number_format($income, 2) }}</div>
                            </div>
                            <div class="p-3 sm:p-4 rounded-xl bg-slate-100/80 dark:bg-white/5 border border-slate-200/50 dark:border-white/5 backdrop-blur-sm hover:bg-slate-200/80 dark:hover:bg-white/10 transition-colors group">
                                <div class="flex items-center gap-2 text-rose-600 dark:text-rose-400 mb-2 font-bold text-[10px] sm:text-xs uppercase tracking-wider">
                                    <i class="bi bi-arrow-up-right group-hover:scale-110 transition-transform"></i> <span data-i18n="expense">مصروف</span>
                                </div>
                                <div class="text-lg sm:text-xl font-bold font-heading text-text-main truncate">{{ number_format($expense, 2) }}</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Savings Growth Widget (New) -->
                    <div class="mt-6 pt-6 border-t border-[var(--border-light)]">
                        @php
                            $savingsRate = $dashboardData['savingsRate'] ?? 0;
                            $totalSavings = $dashboardData['totalSavings'] ?? 0;
                        @endphp
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                                    <i class="bi bi-piggy-bank"></i>
                                </div>
                                <span class="font-bold text-sm text-text-main" data-i18n="savingsGrowth">نمو المدخرات</span>
                            </div>
                            <div class="text-right">
                                <div class="text-sm font-black text-indigo-600 dark:text-indigo-400">{{ number_format($totalSavings, 0) }} <span class="text-[10px]">د.ل</span></div>
                            </div>
                        </div>
                        <div class="w-full h-2 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden shadow-inner">
                            <div class="h-full bg-gradient-to-r from-indigo-500 to-purple-600 rounded-full shadow-[0_0_10px_rgba(99,102,241,0.3)] transition-all duration-1000" style="width: {{ min(100, $savingsRate) }}%"></div>
                        </div>
                        <div class="flex justify-between mt-2">
                            <span class="text-[10px] font-medium text-slate-500" data-i18n="totalWealthProgress">تقدم الثراء الكلي</span>
                            <span class="text-[10px] font-bold text-indigo-500">{{ round($savingsRate) }}%</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Expense Chart Card (Enhanced) -->
            <div class="card-premium border-none shadow-xl p-6 sm:p-8 flex flex-col">
                <div class="flex items-center justify-between mb-6">
                    <div>
                         <h3 class="text-lg sm:text-xl font-heading font-bold text-text-main" data-i18n="expenseDistribution">توزيع المصروفات</h3>
                         <p class="text-xs sm:text-sm text-slate-500" data-i18n="topExpenseAnalysis">تحليل الفئات الأكثر إنفاقاً</p>
                    </div>
                    <div class="w-10 h-10 rounded-full bg-slate-50 dark:bg-slate-800 flex items-center justify-center text-slate-500">
                        <i class="bi bi-pie-chart text-lg"></i>
                    </div>
                </div>
                <div class="flex-1 relative flex items-center justify-center min-h-[200px]">
                    <canvas id="homeExpensePie"></canvas>
                    <div id="homeExpenseEmpty" class="absolute inset-0 flex items-center justify-center text-slate-400 dark:text-slate-500 text-sm hidden">
                        <div class="text-center">
                            <i class="bi bi-inbox text-3xl mb-2 block opacity-50"></i>
                            <span data-i18n="noData">لا توجد بيانات</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Goals Section (Premium Grid) -->
    <section id="goals" class="mb-12 stagger-item delay-3">
        <div class="flex items-center justify-between mb-6">
             <h2 class="text-2xl font-heading font-bold text-text-main flex items-center gap-2">
                <i class="bi bi-bullseye text-[var(--gold-500)]"></i> <span data-i18n="financialGoals">الأهداف المالية</span>
             </h2>
             <a href="{{ route('goals.index') }}" class="text-sm font-bold text-slate-500 hover:text-[var(--gold-600)] transition-colors" data-i18n="viewAll">عرض الكل</a>
        </div>
        
        @if($goalsList->isEmpty())
             <div class="card-premium p-10 text-center border-dashed border-2 border-slate-200 dark:border-slate-800 bg-transparent shadow-none">
                <div class="w-16 h-16 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center mx-auto mb-4 text-slate-400 text-2xl">
                    <i class="bi bi-bullseye"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-700 dark:text-slate-300 mb-2" data-i18n="noGoalsYet">لا توجد أهداف حالياً</h3>
                <p class="text-slate-500 mb-6 max-w-md mx-auto" data-i18n="startAddingGoal">ابدأ بتحديد أهدافك المالية لتتمكن من متابعة تقدمك وتحقيق أحلامك.</p>
                <a href="{{ route('goals.create') }}" class="btn-gold inline-flex items-center gap-2">
                    <i class="bi bi-plus-lg"></i> <span data-i18n="addNewGoal">إضافة هدف جديد</span>
                </a>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($goalsList as $goal)
                    @php
                        $progress = max(0, min(100, (int)$goal->progress));
                        $remaining = max(0, ($goal->target_amount ?? 0) - ($goal->current_amount ?? 0));
                    @endphp
                    <div class="goal-card-premium p-6 relative group">
                        <!-- Action Buttons (Edit/Delete) -->
                        <div class="absolute top-4 right-4 flex gap-2 opacity-0 group-hover:opacity-100 transition-opacity z-10">
                            <a href="{{ route('goals.edit', $goal) }}" class="w-8 h-8 rounded-full bg-white/50 backdrop-blur dark:bg-slate-800/50 flex items-center justify-center text-slate-600 dark:text-slate-300 hover:text-blue-500 hover:bg-white shadow-sm transition-all" data-i18n-title="edit" title="تعديل">
                                <i class="bi bi-pencil-fill text-xs"></i>
                            </a>
                        </div>

                        <div class="text-center mb-6 mt-2">
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

                        @php 
                            $gFeed = $goalFeedback[$goal->id] ?? null;
                            $progress = max(0, min(100, (int)$goal->progress));
                            $remaining = max(0, ($goal->target_amount ?? 0) - ($goal->current_amount ?? 0));
                            $remainingPercent = 100 - $progress;
                            $isComplete = $progress >= 100;
                            
                            $deadline = $goal->deadline ? \Carbon\Carbon::parse($goal->deadline) : null;
                            $daysLeft = $deadline ? (int)now()->diffInDays($deadline, false) : null;
                            $dailyNeeded = ($daysLeft && $daysLeft > 0) ? (int)ceil($remaining / $daysLeft) : 0;
                            $isUrgent = $daysLeft !== null && $daysLeft <= 30 && $daysLeft > 0;
                            $isPastDeadline = $daysLeft !== null && $daysLeft < 0;
                        @endphp
                         @if($isComplete || $progress > 0)
                             <div class="mt-4" id="goal-ai-{{ $goal->id }}">
                                 <!-- Skeleton Screen -->
                                 <div class="p-3 bg-slate-100/30 dark:bg-white/5 rounded-xl border border-dashed border-slate-200 dark:border-white/10">
                                     <div class="flex items-center gap-3">
                                         <div class="skeleton skeleton-circle h-8 w-8 flex-shrink-0"></div>
                                         <div class="flex-1 space-y-2">
                                             <div class="skeleton skeleton-text h-3 w-1/3"></div>
                                             <div class="skeleton skeleton-text h-3 w-3/4"></div>
                                         </div>
                                     </div>
                                 </div>
                             </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </section>

    <!-- Transactions Section (Premium List) -->
    <section id="transactions" class="mb-8 stagger-item delay-4">
        <div class="flex items-center justify-between mb-6">
             <h2 class="text-2xl font-heading font-bold text-text-main flex items-center gap-2">
                <i class="bi bi-receipt-cutoff text-[var(--gold-500)]"></i> <span data-i18n="recentTransactions">آخر المعاملات</span>
             </h2>
             <a href="{{ route('transactions.index') }}" class="text-sm font-bold text-slate-500 hover:text-[var(--gold-600)] transition-colors" data-i18n="viewAll">عرض الكل</a>
        </div>
        
        @if($transactions->isEmpty())
             <div class="card-premium p-8 text-center bg-slate-50 dark:bg-slate-900/50 border-dashed border-2 border-slate-200 dark:border-slate-800 shadow-none">
                <p class="text-slate-500" data-i18n="noRecentTransactions">لم تقم بأي معاملات حديثاً.</p>
            </div>
        @else
            <div class="flex flex-col gap-3 timeline-container">
                <div class="timeline-rail"></div>
                @foreach($transactions as $transaction)
                    @php
                        $isIncome = $transaction->type === 'income';
                        $cat = \App\Models\Category::find($transaction['category_id']); // Ensure we get model for color
                        $catName = $cat?->name ?? $transaction['category'];
                        
                        // Robust Color Fallback
                        $color = $cat?->color ?? ($catColors[$catName] ?? ($isIncome ? '#10b981' : '#f43f5e'));

                        $isSavings = !$isIncome && isset($transaction['goal_id']);
                        if($isSavings) {
                             $color = '#6366f1'; // Indigo for Savings
                        }
                    @endphp
                    <div class="card-premium p-4 flex items-center justify-between hover:bg-slate-50 dark:hover:bg-slate-800/80 transition-all group border border-transparent hover:border-[var(--gold-200)] dark:hover:border-slate-700 relative overflow-hidden">
                        
                         @if($isSavings)
                            <div class="absolute top-0 right-0 w-8 h-8 bg-indigo-500/10 rounded-bl-xl pointer-events-none"></div>
                        @endif

                        <div class="flex items-center gap-4 relative z-10">
                            <!-- Icon -->
                            <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-white text-xl shadow-md transform group-hover:scale-110 transition-transform duration-300" style="background: linear-gradient(135deg, {{ $color }}, {{ $color }}dd);">
                                <i class="bi {{ $cat?->icon ?? ($isSavings ? 'bi-piggy-bank' : ($isIncome ? 'bi-arrow-down-left' : 'bi-bag')) }}"></i>
                            </div>
                            
                            <!-- Info -->
                            <div class="flex-1">
                                <div class="font-bold text-text-main text-base mb-0.5">{{ $transaction['note'] ?? $catName }}</div>
                                <div class="text-xs font-medium text-text-muted flex items-center gap-2">
                                    <span>{{ $transaction['occurred_at'] }}</span>
                                    <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                                    <span data-i18n="{{ $catMap[$catName] ?? 'uncategorized' }}">{{ $catName }}</span>
                                    @if($isSavings)
                                        <span class="px-1.5 py-0.5 rounded-full bg-indigo-100/50 text-indigo-700 text-[10px]" data-i18n="savings">ادخار</span>
                                    @elseif($isIncome)
                                        <span class="px-1.5 py-0.5 rounded-full bg-emerald-100/50 text-emerald-700 text-[10px]" data-i18n="income">دخل</span>
                                    @else
                                        <span class="px-1.5 py-0.5 rounded-full bg-red-100/50 text-red-700 text-[10px]" data-i18n="expense">مصروف</span>
                                    @endif
                                </div>
                                <!-- AI Feedback Placeholder -->
                                <div class="mt-2" id="tx-ai-{{ $transaction->id }}">
                                    <!-- Simple pulse skeleton if needed, but keeping it light for transactions -->
                                    <div class="skeleton skeleton-text h-2 w-20"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Actions & Amount -->
                        <div class="flex items-center gap-6 relative z-10">
                            <div class="text-right">
                                <div class="font-bold font-heading text-lg {{ $isIncome ? 'text-emerald-600' : ($isSavings ? 'text-indigo-600' : 'text-text-main') }}">
                                    {{ $isIncome ? '+' : '-' }}{{ number_format($transaction['amount'], 2) }}
                                </div>
                            </div>
                            
                             <div class="flex items-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity translate-x-2 group-hover:translate-x-0">
                                <a href="{{ route('transactions.edit', $transaction['id']) }}" class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-700 text-slate-500 hover:text-blue-500 flex items-center justify-center transition-colors" data-i18n-title="edit" title="تعديل">
                                    <i class="bi bi-pencil-fill text-xs"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </section>
    

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // 1. Initial Dashboard Data from Window
        const features = window.dashboardData?.aiFeatures || {};
        
        // AI Health Score and X-Ray logic removed as requested.
        // Dashboard now focuses on Savings Growth.

        // 3. Load Async AI Feedback (Internal API)
        const container = document.getElementById('ai-insights-container');
        const content = document.getElementById('ai-insights-content');

        if (container && content) {
            // Reveal container with animation delay
            setTimeout(() => { container.classList.remove('hidden'); }, 1500);

            fetch('{{ route('ai.insights.dashboard') }}')
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    let msgs = [];
                    // Collect all messages
                    if(data.goalFeedback) Object.values(data.goalFeedback).forEach(item => { if(item && item.message) msgs.push(item); });
                    if(data.txFeedback) Object.values(data.txFeedback).forEach(item => { if(item && item.message) msgs.push(item); });
                    if(data.globalInsights) data.globalInsights.forEach(item => { if(item && item.message) msgs.push(item); });

                    if (msgs.length > 0) {
                        // Prioritize
                        msgs.sort((a, b) => (b.priority || 0) - (a.priority || 0));
                        const topMsg = msgs[0];
                        
                        // Render Top Insight
                        content.innerHTML = `
                            <div class="animate-fade-in">
                                <p class="leading-relaxed mb-1 font-bold text-slate-800 dark:text-white text-base">
                                    <i class="bi bi-chat-quote-fill text-[var(--gold-500)] ml-2"></i>
                                    ${topMsg.message}
                                </p>
                                ${msgs.length > 1 ? `<p class="text-xs opacity-75 mt-2 flex items-center gap-1"><i class="bi bi-plus-circle"></i> ${msgs.length - 1} توصيات أخرى متاحة في صفحة التقارير</p>` : ''}
                            </div>
                        `;
                    } else {
                        // Empty State
                        content.innerHTML = `<span class="opacity-70 text-sm">جاري تحليل بياناتك المالية... لا توجد توصيات عاجلة الآن.</span>`;
                    }

                    // --- Render Individual Card Insights ---
                    if(data.txFeedback) {
                        console.log('Rendering Tx Feedback:', data.txFeedback);
                        Object.entries(data.txFeedback).forEach(([id, feedback]) => {
                            const el = document.getElementById(`tx-ai-${id}`);
                            if(el && feedback && feedback.message) {
                                let color = feedback.type === 'warning' ? 'text-red-600 bg-red-50' : (feedback.type === 'success' ? 'text-emerald-600 bg-emerald-50' : 'text-slate-600 bg-slate-50');
                                el.innerHTML = `
                                    <div class="p-3 rounded-xl ${color} bg-opacity-50 dark:bg-opacity-20 mt-2 animate-fade-in border border-transparent hover:border-current transition-all group">
                                        <div class="flex items-start gap-2 text-[11px] font-bold mb-2">
                                            <i class="bi bi-lightbulb-fill mt-0.5"></i>
                                            <span class="dark:text-slate-200">${feedback.message}</span>
                                        </div>
                                        <div class="flex items-center gap-2 opacity-50 group-hover:opacity-100 transition-opacity">
                                            <button class="px-3 py-1 rounded bg-white dark:bg-slate-700 text-emerald-600 dark:text-emerald-400 text-[10px] font-bold shadow-sm hover:scale-105 active:scale-95 transition-all outline-none" onclick="this.closest('.p-3').remove()">
                                                <i class="bi bi-check-lg"></i> <span data-i18n="accept">قبول</span>
                                            </button>
                                            <button class="px-3 py-1 rounded bg-white dark:bg-slate-700 text-slate-400 dark:text-slate-200 text-[10px] font-bold shadow-sm hover:text-red-500 transition-all outline-none" onclick="this.closest('.p-3').remove()">
                                                <i class="bi bi-x-lg"></i> <span data-i18n="reject">رفض</span>
                                            </button>
                                        </div>
                                    </div>
                                `;
                            } else if(el) {
                                el.remove(); // Remove skeleton
                            }
                        });
                    }
                    if(data.goalFeedback) {
                        console.log('Rendering Goal Feedback:', data.goalFeedback);
                        Object.entries(data.goalFeedback).forEach(([id, feedbackList]) => {
                            const container = document.getElementById(`goal-ai-${id}`);
                            // Goal feedback is a list of items, take the first one (highest priority)
                            const feedback = Array.isArray(feedbackList) ? feedbackList[0] : feedbackList;
                            
                            if (container && feedback && feedback.message) {
                                renderGoalAdvisor(container, feedback, id);
                            } else if (container) {
                                container.remove(); // Remove skeleton if no feedback
                            }
                        });
                    }

                    function renderGoalAdvisor(container, feedback, id) {
                         const isWarning = feedback.type === 'warning';
                         const isSuccess = feedback.type === 'success'; 
                         
                         const colorClass = isSuccess ? 'green' : (isWarning ? 'red' : 'indigo');
                         const gradient = isSuccess 
                             ? 'from-green-50 to-emerald-50 dark:from-green-900/30 dark:to-emerald-900/10 border-r-4 border-r-green-500 dark:border-r-green-600' 
                             : (isWarning ? 'from-red-50 to-rose-50 dark:from-red-900/30 dark:to-rose-900/10 border-r-4 border-r-red-500 dark:border-r-red-600' : 'from-purple-50 to-indigo-50 dark:from-purple-900/30 dark:to-indigo-900/10 border-purple-400 dark:border-indigo-400');
                             
                         // Confetti Logic
                        // Enhanced Confetti Logic
                        let confettiHtml = '';
                        if (isSuccess) {
                            confettiHtml = '<div class="confetti-container absolute inset-0 pointer-events-none" style="z-index: 5; overflow: hidden;">';
                            const colors = ['#FFD700', '#FF6B6B', '#4ECDC4', '#45B7D1', '#FFA07A', '#F9A8D4'];
                            for (let i = 0; i < 40; i++) { // Increased count
                                const left = Math.floor(Math.random() * 100);
                                const delay = Math.random() * 2; // Reduced max delay
                                const duration = 2.5 + Math.random() * 2; // Random duration 2.5-4.5s
                                const bg = colors[i % 6];
                                const size = 5 + Math.random() * 5; // Random size 5-10px
                                const shape = Math.random() > 0.5 ? '50%' : '2px'; // Circle or Square
                                
                                confettiHtml += `<div class="confetti" style="
                                    left: ${left}%; 
                                    top: -20px;
                                    width: ${size}px; 
                                    height: ${size}px; 
                                    background-color: ${bg}; 
                                    border-radius: ${shape};
                                    position: absolute; 
                                    animation: confetti-fall ${duration}s ease-in-out infinite;
                                    animation-delay: ${delay}s;
                                    opacity: 0.8;
                                "></div>`;
                            }
                            confettiHtml += '</div>';
                        }
                        
                         container.innerHTML = `
                            <div class="mt-4 p-5 rounded-[28px] bg-gradient-to-br ${gradient} backdrop-blur-xl border border-white/20 dark:border-white/10 shadow-[0_8px_32px_rgba(0,0,0,0.05)] dark:shadow-[0_8px_32px_rgba(0,0,0,0.2)] relative overflow-hidden animate-enter group hover:shadow-[0_12px_48px_rgba(212,175,55,0.15)] transition-all duration-500">
                                ${confettiHtml}
                                <div class="flex items-start gap-3 relative z-10">
                                    <div class="w-10 h-10 rounded-full bg-white/40 backdrop-blur-md flex items-center justify-center flex-shrink-0 shadow-sm border-2 border-white/30">
                                        <span class="text-lg group-hover:animate-bounce transform transition-transform">${isSuccess ? '🏆' : (isWarning ? '⚡' : '💡')}</span>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-[10px] font-black mb-1 opacity-60 uppercase tracking-widest ${isSuccess ? 'text-green-700 dark:text-green-400' : ''}">
                                            ${isSuccess ? 'إنجاز مميز' : (isWarning ? 'تنبيه ذكي' : 'رؤية مالية')}
                                        </p>
                                        <p class="text-[14px] font-bold leading-relaxed mb-4 text-slate-800 dark:text-slate-100">
                                            ${feedback.message}
                                        </p>
                                        <div class="flex items-center gap-2 opacity-80 group-hover:opacity-100 transition-opacity">
                                            <button class="px-5 py-1.5 bg-white/60 dark:bg-white/10 backdrop-blur-md rounded-[14px] text-[10px] font-black shadow-sm hover:bg-white dark:hover:bg-white/20 hover:scale-105 transition-all text-emerald-600 dark:text-emerald-400 border border-white/20" onclick="this.closest('.mt-4').remove()">
                                                <i class="bi bi-check-lg"></i> <span data-i18n="accept">قبول</span>
                                            </button>
                                            <button class="px-5 py-1.5 bg-white/40 dark:bg-white/5 backdrop-blur-md rounded-[14px] text-[10px] font-black shadow-sm hover:text-red-500 hover:scale-105 transition-all text-slate-500 border border-white/10" onclick="this.closest('.mt-4').remove()">
                                                <i class="bi bi-x-lg"></i> <span data-i18n="dismiss">تجاهل</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                    }

                }
            })
            .catch(err => {
                console.warn('AI Insights offline', err);
                container.classList.add('hidden');
            });
        }
    }); // End DOMContentLoaded
</script>
@endpush
