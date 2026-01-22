@extends('layouts.app')

@section('content')
    @php
        $income = $dashboardData['totalIncome'] ?? 0;
        $expense = $dashboardData['totalExpense'] ?? 0;
        $balance = $income - $expense;
    @endphp
    <script>
        window.dashboardData = @json($dashboardData ?? []);
    </script>

    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <div>
            <div class="text-sm text-muted" data-i18n="reportsHeader">لوحة التقارير والتحليلات</div>
            <h3 class="text-xl font-bold text-text-main" data-i18n="reportsTitle">التقارير</h3>
        </div>
        <div class="flex flex-wrap gap-2">
            <a class="btn btn-primary" href="{{ route('transactions.create') }}"><i class="bi bi-plus"></i> <span data-i18n="newTransaction">إضافة معاملة</span></a>
            <a class="btn btn-primary" href="{{ route('goals.create') }}"><i class="bi bi-bullseye"></i> <span data-i18n="newGoal">هدف جديد</span></a>
            <a class="btn btn-outline-secondary" href="{{ route('dashboard.data') }}" target="_blank"><i class="bi bi-download"></i> <span data-i18n="exportData">تصدير (JSON)</span></a>
        </div>
    </div>

    <div class="flex flex-wrap gap-3 items-center mb-4">
        <div class="flex-1 min-w-[260px] card-soft flex items-center gap-2 px-3 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl">
            <i class="bi bi-search text-muted"></i>
            <input type="text" class="w-full bg-transparent focus:outline-none text-sm text-text-main placeholder:text-muted" data-i18n-placeholder="instantSearch" placeholder="Instant Search or Type to Analyze" data-i18n-aria-label="search" aria-label="بحث">
            <i class="bi bi-mic text-muted"></i>
        </div>
        <div class="flex gap-2 flex-wrap">
            <button class="px-3 pb-1 pt-2 rounded-lg text-sm font-semibold border border-transparent relative" style="color: var(--brand-start); background: var(--brand-soft);" data-i18n="monthly">
                شهري
                <span class="absolute left-2 right-2 -bottom-1 h-0.5 rounded-full" style="background: var(--brand-start);"></span>
            </button>
            <button class="px-3 py-2 rounded-lg text-sm font-semibold text-slate-600 dark:text-slate-300 border border-transparent" data-i18n="weekly">أسبوعي</button>
            <button class="px-3 py-2 rounded-lg text-sm font-semibold text-slate-600 dark:text-slate-300 border border-transparent" data-i18n="daily">يومي</button>
            <button class="px-3 py-2 rounded-lg text-sm font-semibold text-slate-600 dark:text-slate-300 border border-transparent" data-i18n="yearly">سنوي</button>
        </div>
    </div>

    @php
        $cards = [
            ['icon' => 'bi-list', 'label' => 'transactionsCount', 'display' => 'إجمالي المعاملات', 'value' => $dashboardData['transactionsCount'] ?? 0, 'color' => 'var(--brand-start)'],
            ['icon' => 'bi-bullseye', 'label' => 'completedGoals', 'display' => 'الأهداف المكتملة', 'value' => $dashboardData['completedGoals'] ?? 0, 'color' => 'var(--brand-mid)'],
            ['icon' => 'bi-calendar3', 'label' => 'avgDailySpending', 'display' => 'متوسط الإنفاق اليومي', 'value' => number_format($dashboardData['avgDaily'] ?? 0, 2) . ' د.ل', 'color' => 'var(--brand-blue)'],
            ['icon' => 'bi-calculator', 'label' => 'avgTransaction', 'display' => 'متوسط المعاملة', 'value' => number_format($dashboardData['avgTransaction'] ?? 0, 2) . ' د.ل', 'color' => 'var(--warning)'],
            ['icon' => 'bi-piggy-bank', 'label' => 'savingsRate', 'display' => 'معدل الادخار', 'value' => number_format($dashboardData['savingsRate'] ?? 0, 1) . '%', 'color' => 'var(--brand-end)'],
            ['icon' => 'bi-graph-down-arrow', 'label' => 'topExpenseCategory', 'display' => 'أعلى فئة إنفاق', 'value' => $dashboardData['topExpenseCategory'] ?? '—', 'color' => 'var(--danger)'],
        ];
    @endphp

    <section class="mb-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
            @foreach($cards as $stat)
                <div class="card-soft p-4 h-full rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 flex flex-col gap-2">
                    <div class="flex items-center justify-between">
                        <div class="avatar-icon" style="background: {{ $stat['color'] }};">
                            <i class="bi {{ $stat['icon'] }}"></i>
                        </div>
                        <i class="bi bi-three-dots text-muted"></i>
                    </div>
                    <div class="text-sm text-slate-600 dark:text-slate-400" data-i18n="{{ $stat['label'] }}">{{ $stat['display'] }}</div>
                    <div class="text-2xl font-extrabold text-text-main">
                        {{ $stat['value'] }}
                        @if(str_contains($stat['value'], 'د.ل'))
                            <span data-i18n="lydSymbol" class="hidden">د.ل</span>
                        @endif
                    </div>
                    <div class="h-1.5 rounded-full" style="background: var(--brand-soft);">
                        <div class="h-full rounded-full" style="width: 65%; background: var(--brand-start);"></div>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <section class="mb-4">
        <div class="row g-3 align-items-stretch">
            <div class="col-lg-5">
                <div class="card-soft h-100 p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 flex flex-col justify-between">
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <div class="text-sm text-muted" data-i18n="currentBalance">الرصيد الحالي</div>
                            <h5 class="card-title mb-0 text-text-main">{{ number_format($balance, 2) }} <span data-i18n="lydSymbol">د.ل</span></h5>
                        </div>
                        <div class="avatar-icon" style="background: linear-gradient(135deg, #22c55e, #2563eb);">
                            <i class="bi bi-wallet2"></i>
                        </div>
                    </div>
                    <div class="flex gap-2 flex-wrap mb-3">
                        <span class="pill-badge pill-income"><i class="bi bi-graph-up-arrow"></i> {{ number_format($income, 2) }} <span data-i18n="income">دخل</span></span>
                        <span class="pill-badge pill-expense"><i class="bi bi-graph-down"></i> {{ number_format($expense, 2) }} <span data-i18n="expense">مصروف</span></span>
                    </div>
                    @php $savingsRate = $dashboardData['savingsRate'] ?? 0; @endphp
                    <div class="progress progress-soft mb-1">
                        <div class="progress-bar bg-success" role="progressbar" style="width: {{ min(100, max(0, $savingsRate)) }}%" aria-valuenow="{{ $savingsRate }}" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <small class="text-text-muted" data-i18n="savingsRate">معدل الادخار</small>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="card-soft h-100 p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <div class="text-sm text-muted" data-i18n="monthlyPerformance">الأداء الشهري</div>
                            <h5 class="card-title mb-0 text-text-main" data-i18n="incomeVsExpense">الدخل مقابل المصروف</h5>
                        </div>
                        <span class="chip"><i class="bi bi-graph-up"></i> <span data-i18n="linear">خطي</span></span>
                    </div>
                    <div style="height:320px;">
                        <canvas id="monthlyChart"></canvas>
                        <div id="monthlyEmpty" class="text-muted text-center d-none mt-4" data-i18n="noMonthlyData">لا توجد بيانات شهرية بعد.</div>
                    </div>
                    <div class="flex gap-3 mt-3 text-sm">
                        <span class="inline-flex items-center gap-2 px-2 py-1 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200">
                            <span class="w-3 h-3 rounded-full" style="background: var(--brand-start);"></span> <span data-i18n="income">الدخل</span>
                        </span>
                        <span class="inline-flex items-center gap-2 px-2 py-1 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200">
                            <span class="w-3 h-3 rounded-full" style="background: var(--danger);"></span> <span data-i18n="expense">المصروف</span>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="mb-4">
        <div class="card-soft h-100 p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <div class="text-sm text-muted" data-i18n="spentInsight">نظرة على المصروف</div>
                    <h5 class="card-title mb-0 text-text-main" data-i18n="byCategory">حسب الفئة</h5>
                </div>
                <span class="chip"><i class="bi bi-pie-chart"></i> <span data-i18n="donut">دونات</span></span>
            </div>
            <div style="height:320px;">
                <canvas id="categoryChart"></canvas>
                <div id="categoryEmpty" class="text-muted text-center d-none mt-4" data-i18n="noExpenseData">لا توجد بيانات مصروفات بعد.</div>
            </div>
            <div class="flex flex-wrap gap-2 mt-3 text-sm">
                <span class="inline-flex items-center gap-2 px-2 py-1 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200">
                    <span class="w-3 h-3 rounded-full" style="background: var(--brand-start);"></span> <span data-i18n="withinBudget">ضمن الميزانية</span>
                </span>
                <span class="inline-flex items-center gap-2 px-2 py-1 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200">
                    <span class="w-3 h-3 rounded-full" style="background: var(--brand-mid);"></span> <span data-i18n="underMonitoring">تحت المراقبة</span>
                </span>
                <span class="inline-flex items-center gap-2 px-2 py-1 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200">
                    <span class="w-3 h-3 rounded-full" style="background: var(--danger);"></span> <span data-i18n="overspent">تجاوز/مصروف عالٍ</span>
                </span>
            </div>
        </div>
    </section>


    <section class="mb-4">
        <div class="flex items-center justify-between mb-2">
            <h5 class="mb-0 text-text-main" data-i18n="activeGoalsTitle">الأهداف النشطة</h5>
            <a href="{{ route('goals.index') }}" class="btn btn-sm btn-primary"><i class="bi bi-bullseye"></i> <span data-i18n="manageGoals">إدارة الأهداف</span></a>
        </div>
        @if(($activeGoals ?? collect())->isEmpty())
            <p class="text-muted mb-0" data-i18n="noActiveGoals">لا توجد أهداف نشطة.</p>
        @else
            <div class="row g-3">
                @foreach($activeGoals as $goal)
                    @php $progress = max(0, min(100, (int) $goal->progress)); @endphp
                    <div class="col-md-6 col-lg-4">
                        <div class="goal-card-premium h-100 p-5 flex flex-col">
                            <div class="flex justify-between items-center mb-4">
                                <div class="font-bold text-text-main text-lg">{{ $goal->name }}</div>
                                <span class="text-[10px] font-black px-2 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-500" data-i18n="{{ optional($goal->deadline)->toDateString() ? '' : 'noDeadline' }}">{{ optional($goal->deadline)->format('Y-m-d') ?? 'بدون موعد' }}</span>
                            </div>

                            <div class="flex flex-col items-center mb-6">
                                <div class="ring-premium" style="--p: {{ $progress }}%;">
                                    <div class="ring-premium-val">{{ $progress }}%</div>
                                </div>
                            </div>

                            <div class="space-y-2 mt-auto">
                                <div class="flex justify-between text-xs"><span class="text-text-muted" data-i18n="target">المستهدف:</span><span class="font-bold text-text-main">{{ number_format($goal->target_amount, 2) }}</span></div>
                                <div class="flex justify-between text-xs"><span class="text-text-muted" data-i18n="current">الحالي:</span><span class="font-bold text-emerald-600">{{ number_format($goal->current_amount, 2) }}</span></div>
                                <div class="w-full h-px bg-slate-100 dark:bg-slate-800 my-2"></div>
                                <div class="flex justify-between text-xs"><span class="text-text-muted" data-i18n="left">المتبقي:</span><span class="font-bold text-amber-600">{{ number_format(max(0, $goal->target_amount - $goal->current_amount), 2) }}</span></div>
                            </div>

                            <div class="flex justify-end gap-2 mt-6">
                                <a class="w-8 h-8 rounded-lg bg-slate-50 dark:bg-slate-800 flex items-center justify-center text-slate-400 hover:text-blue-500 transition-colors" href="{{ route('goals.edit', $goal) }}" data-i18n-title="edit" title="تعديل">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                <form action="{{ route('goals.destroy', $goal) }}" method="POST" onsubmit="return confirm(i18n[currentLang()].deleteConfirm);">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-8 h-8 rounded-lg bg-slate-50 dark:bg-slate-800 flex items-center justify-center text-slate-400 hover:text-red-500 transition-colors" data-i18n-title="delete" title="حذف">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </section>
@endsection
