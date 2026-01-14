@extends('layouts.app')

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

        $stats = [
            ['icon' => 'bi-list', 'label' => 'إجمالي المعاملات', 'value' => $dashboardData['transactionsCount'] ?? $transactions->count(), 'color' => '#2563eb'],
            ['icon' => 'bi-bullseye', 'label' => 'الأهداف المكتملة', 'value' => $dashboardData['completedGoals'] ?? 0, 'color' => '#10b981'],
            ['icon' => 'bi-calendar3', 'label' => 'متوسط الإنفاق اليومي', 'value' => number_format($dashboardData['avgDaily'] ?? 0, 2) . ' د.ل', 'color' => '#f97316'],
            ['icon' => 'bi-calculator', 'label' => 'متوسط المعاملة', 'value' => number_format($dashboardData['avgTransaction'] ?? 0, 2) . ' د.ل', 'color' => '#7c3aed'],
            ['icon' => 'bi-piggy-bank', 'label' => 'معدل الادخار', 'value' => number_format($dashboardData['savingsRate'] ?? 0, 1) . '%', 'color' => '#22c55e'],
            ['icon' => 'bi-graph-down-arrow', 'label' => 'أعلى فئة إنفاق', 'value' => $dashboardData['topExpenseCategory'] ?? '—', 'color' => '#ef4444'],
        ];

        $categoryPalette = [
            'طعام' => '#f97316',
            'مواصلات' => '#0ea5e9',
            'تسوق' => '#8b5cf6',
            'صحة' => '#ef4444',
            'ترفيه' => '#ec4899',
            'تعليم' => '#1d4ed8',
            'فواتير' => '#6b7280',
            'رواتب' => '#22c55e',
            'هدايا' => '#a855f7',
            'أخرى' => '#64748b',
        ];

        // Guard optional routes so the homepage never errors if modules are disabled.
        $profileRoute = Route::has('profile.edit') ? route('profile.edit') : '#';
        $settingsRoute = Route::has('settings.index') ? route('settings.index') : '#';
        $reportsRoute = Route::has('reports.index') ? route('reports.index') : '#';
    @endphp

    <style>
        .goal-shell-home { background: var(--card-bg); box-shadow: var(--shadow-soft); border-radius: 24px; }
        .goal-inner-home { border: 1px solid var(--card-border); border-radius: 18px; padding: 22px; background: var(--card-bg); box-shadow: inset 0 1px 0 rgba(255,255,255,0.06); }
        .goal-ring-home { position: relative; width: 120px; height: 120px; border-radius: 9999px; background: conic-gradient(var(--brand-start) calc(var(--p) * 1%), var(--card-border) 0deg); display: grid; place-items: center; margin-inline: auto; }
        .goal-ring-home::after { content: ""; position: absolute; inset: 14px; border-radius: 9999px; background: var(--card-bg); box-shadow: inset 0 0 0 1px var(--card-border); z-index: 0; }
        .goal-ring-home .ring-value { position: relative; z-index: 1; font-weight: 800; color: var(--brand-start); font-size: 1.2rem; display: flex; align-items: center; justify-content: center; width: 100%; height: 100%; }
        .goal-label { color: var(--text-secondary); font-weight: 600; }
        .goal-value { color: var(--text-primary); font-weight: 700; }
        .goal-remaining { color: #f97316; font-weight: 800; }
    </style>

    <section class="hero mb-4 space-y-4">
        <div class="space-y-3">
            <div class="chip mb-1">
                <i class="bi bi-stars"></i>
                <span>واجهة متعددة اللغات + الوضع الداكن</span>
            </div>
            <h1 class="text-3xl font-extrabold text-slate-900 dark:text-slate-50">مرحبا بك في قيراط</h1>
            <p class="text-slate-600 dark:text-slate-300">تتبع الدخل والمصروفات، حدد أهدافك، واستعرض إحصاءات لحظية بتصميم مستوحى من التطبيق المحمول.</p>
            <div class="flex flex-wrap gap-2">
                <a class="btn btn-primary" href="{{ route('transactions.create') }}"><i class="bi bi-plus"></i> إضافة معاملة</a>
                <a class="btn btn-primary" href="{{ route('goals.create') }}"><i class="bi bi-bullseye"></i> هدف جديد</a>
            </div>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 items-stretch">
            <div class="card-soft p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-lg h-full">
                <div class="flex flex-col gap-3">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <div class="text-slate-500 dark:text-slate-400">الرصيد الحالي</div>
                            <div class="stat-value text-slate-900 dark:text-slate-50">{{ number_format($balance, 2) }} د.ل</div>
                        </div>
                        <div class="avatar-icon" style="background: linear-gradient(135deg, #0b0b0b, var(--brand-start));">
                            <i class="bi bi-wallet2"></i>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        <div class="list-card flex items-center justify-between gap-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
                            <div class="flex items-center gap-2">
                                <span class="pill-badge pill-income"><i class="bi bi-graph-up-arrow"></i> دخل</span>
                            </div>
                            <div class="fw-bold text-slate-900 dark:text-slate-50">{{ number_format($income, 2) }}</div>
                        </div>
                        <div class="list-card flex items-center justify-between gap-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
                            <div class="flex items-center gap-2">
                                <span class="pill-badge pill-expense"><i class="bi bi-graph-down"></i> مصروف</span>
                            </div>
                            <div class="fw-bold text-slate-900 dark:text-slate-50">{{ number_format($expense, 2) }}</div>
                        </div>
                    </div>
                    <div class="flex items-center justify-between text-sm text-slate-600 dark:text-slate-300">
                        <span>صافي الرصيد</span>
                        <span class="font-bold text-slate-900 dark:text-slate-50">{{ number_format($balance, 2) }} د.ل</span>
                    </div>
                </div>
            </div>
            <div class="card-soft p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm h-full">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <div class="text-slate-500 dark:text-slate-400">توزيع المصروفات</div>
                        <div class="text-lg font-semibold text-slate-900 dark:text-slate-50">Pie chart حسب الفئات</div>
                    </div>
                    <span class="chip"><i class="bi bi-pie-chart"></i> رؤية سريعة</span>
                </div>
                <div class="h-48 sm:h-56 lg:h-64 relative">
                    <canvas id="homeExpensePie" aria-label="مخطط المصروفات"></canvas>
                    <div id="homeExpenseEmpty" class="absolute inset-0 flex items-center justify-center text-slate-400 dark:text-slate-500 d-none">لا توجد بيانات مصروفات بعد</div>
                </div>
            </div>
        </div>
    </section>

    <section id="goals" class="mb-4">
        <div class="flex items-center justify-between mb-2">
            <h5 class="mb-0 text-slate-900 dark:text-slate-100">الأهداف المالية</h5>
        </div>
        @if($goalsList->isEmpty())
            <p class="text-muted mb-0">لا توجد أهداف بعد.</p>
        @else
            <div class="row g-3">
                @foreach($goalsList as $goal)
                    @php
                        $progress = max(0, min(100, (int)$goal->progress));
                        $remaining = max(0, ($goal->target_amount ?? 0) - ($goal->current_amount ?? 0));
                    @endphp
                    <div class="col-md-6 col-lg-4">
                        <div class="goal-shell-home h-100 p-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
                            <div class="goal-inner-home text-center">
                                <div class="fw-bold text-lg text-slate-900 dark:text-slate-100 mb-3">{{ $goal->name }}</div>
                                <div class="d-flex flex-column align-items-center gap-3">
                                    <div class="goal-ring-home" style="--p: {{ $progress }};">
                                        <div class="ring-value">{{ $progress }}%</div>
                                    </div>
                                    <div class="w-100" dir="rtl">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="goal-label">المبلغ الحالي:</span>
                                            <span class="goal-value">${{ number_format($goal->current_amount, 2) }}</span>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="goal-label">المبلغ المستهدف:</span>
                                            <span class="goal-value">${{ number_format($goal->target_amount, 2) }}</span>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="goal-label">المتبقي:</span>
                                            <span class="goal-remaining">${{ number_format($remaining, 2) }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <a class="btn btn-outline-secondary w-100 d-flex align-items-center justify-content-center gap-2" href="{{ route('goals.edit', $goal) }}">تعديل <i class="bi bi-pencil-square"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </section>

    <section id="transactions" class="mb-4">
        <div class="flex items-center justify-between mb-2">
            <h5 class="mb-0 text-slate-900 dark:text-slate-100">آخر المعاملات</h5>
            <a class="btn btn-sm btn-outline-primary" href="{{ route('transactions.index') }}">عرض الكل</a>
        </div>
        @if($transactions->isEmpty())
            <p class="text-muted mb-0">لا توجد معاملات بعد.</p>
        @else
            <div class="space-y-2">
                @foreach($transactions as $transaction)
                    @php
                        $isIncome = $transaction->type === 'income';
                        $catColor = $categoryPalette[$transaction->category] ?? '#6b7280';
                    @endphp
                    <div class="list-card flex items-center justify-between gap-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
                        <div class="flex items-center gap-3">
                            <div class="avatar-icon" style="background: {{ $catColor }};">
                                <i class="bi {{ $isIncome ? 'bi-arrow-up-right-circle' : 'bi-arrow-down-right-circle' }}"></i>
                            </div>
                            <div>
                                <div class="fw-bold text-slate-900 dark:text-slate-100">{{ $transaction->title }}</div>
                                <div class="text-muted small">{{ $transaction->category }} · {{ $transaction->date }}</div>
                            </div>
                        </div>
                        <div class="text-end">
                            <div class="fw-bold {{ $isIncome ? 'text-success' : 'text-danger' }}">{{ $isIncome ? '+' : '-' }}{{ number_format($transaction->amount, 2) }} د.ل</div>
                            <span class="badge text-bg-light">{{ $transaction->type === 'income' ? 'دخل' : 'مصروف' }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </section>

    
@endsection
