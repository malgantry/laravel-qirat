<?php

namespace App\Services;

use App\Models\Goal;
use App\Models\Transaction;
use App\Services\Insights\TransactionInsightsService;
use App\Services\Insights\GoalInsightsService;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    public function __construct(
        protected FinancialMetricService $metrics,
        protected TransactionInsightsService $txInsights,
        protected GoalInsightsService $goalInsights,
        protected \App\Services\Insights\BudgetInsightsService $budgetInsights
    ) {}

    public function getData(bool $includeAi = false): array
    {
        $userId = auth()->id();
        $latestTransactions = Transaction::where('user_id', $userId)->orderByDesc('occurred_at')->orderByDesc('id')->limit(10)->get();
        $goals = Goal::where('user_id', $userId)->orderBy('deadline')->get();

        $txFeedback = [];
        $goalFeedback = [];

        if ($includeAi && config('ai.enabled')) {
            $txFeedback = $this->txInsights->generateBatch($latestTransactions);
            $goalFeedback = $this->goalInsights->generateBatch($goals);
        }

        // Use FinancialMetricService for unified aggregations
        $range = [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()];
        $rangeMetrics = $this->metrics->getRangeMetrics($userId, ...$range);
        $monthly = $this->metrics->getMonthlyStats($userId);
        $categories = $this->metrics->getCategoryBreakdown($userId, ...$range);
        $budgets = $this->metrics->getBudgetAdherence($userId, ...$range);
        $totalSavings = Goal::where('user_id', $userId)->sum('current_amount');

        $dashboardData = [
            'months' => $monthly->pluck('month'),
            'income' => $monthly->pluck('income')->map(fn($v) => (float)$v),
            'expense' => $monthly->pluck('expense')->map(fn($v) => (float)$v),
            'categories' => $categories->pluck('category'),
            'categoryTotals' => $categories->pluck('total'),
            'totalIncome' => $rangeMetrics['income'],
            'totalExpense' => $rangeMetrics['expense'],
            'totalSavings' => (float) $totalSavings,
            'savingsRate' => $rangeMetrics['savingsRate'],
            'goals' => $goals->map(fn($g) => $g->toArray() + ['progress' => $g->progress]),
            'transactions' => $latestTransactions->map(fn($t) => $t->toArray()),
            'aiFeatures' => $this->calculateAiFeatures($monthly, $rangeMetrics['income'], $rangeMetrics['expense'], $goals, $latestTransactions),
            'globalInsights' => $this->generateGlobalInsights($rangeMetrics['income'], $rangeMetrics['expense'], $rangeMetrics['savingsRate'], $budgets, $goals),
        ];

        return [
            'latestTransactions' => $latestTransactions,
            'goals' => $goals,
            'dashboardData' => $dashboardData,
            'txFeedback' => $txFeedback,
            'goalFeedback' => $goalFeedback,
        ];
    }

    public function calculateAiFeatures($monthly, $totalIncome, $totalExpense, $goals, $transactions): array
    {
        // 1. Spending Ratio
        // If income is 0 but expense > 0, ratio is 1.0 (100% saturated), else 0
        $monthlySpendingRatio = $totalIncome > 0 ? ($totalExpense / $totalIncome) : ($totalExpense > 0 ? 1.0 : 0.0);
        
        // 2. Savings Ratio
        // If income 0, savings capacity is 0
        $savingsRatio = $totalIncome > 0 ? (($totalIncome - $totalExpense) / $totalIncome) : 0;
        
        // 3. Income Level (Normalized against 15k cap)
        $avgMonthlyIncome = ($monthly && $monthly->count() > 0) ? (float)$monthly->avg('income') : 0.0;
        $incomeLevel = min(1, $avgMonthlyIncome / 15000);

        // 4. Fixed Commitments (Calculated from the new is_fixed flag)
        $fixedExpensesLastMonth = Transaction::where('user_id', auth()->id())
            ->where('type', 'expense')
            ->where('is_fixed', true)
            ->whereDate('occurred_at', '>=', now()->subDays(30))
            ->sum('amount');
        
        $totalExpensesLastMonth = Transaction::where('user_id', auth()->id())
            ->where('type', 'expense')
            ->whereDate('occurred_at', '>=', now()->subDays(30))
            ->sum('amount');

        $fixedRatio = $totalExpensesLastMonth > 0 ? ($fixedExpensesLastMonth / $totalExpensesLastMonth) : 0.4;

        // 5. Goal Progress
        $goalProgressRatio = $goals->count() > 0 ? ($goals->avg('progress') / 100) : 0;

        // 6. Goal Delay (Placeholder: we could check overdue goals)
        $goalDelayRatio = $goals->where('deadline', '<', now())->where('status', '!=', 'completed')->count() > 0 ? 0.3 : 0;

        // 7. Expense Frequency (Normalized count/30)
        $txCountLast30 = Transaction::where('user_id', auth()->id())->whereDate('occurred_at', '>=', now()->subDays(30))->count();
        $expenseFrequency = min(1, $txCountLast30 / 60);

        // 8. Spending Variance
        // Calculate variance of monthly expenses
        $monthlyExpenses = $monthly->pluck('expense')->toArray();
        if (count($monthlyExpenses) > 1) {
            $mean = array_sum($monthlyExpenses) / count($monthlyExpenses);
            $variance = 0;
            foreach ($monthlyExpenses as $val) {
                $variance += pow($val - $mean, 2);
            }
            $stdDev = sqrt($variance / count($monthlyExpenses));
            $spendingVariance = $mean > 0 ? ($stdDev / $mean) : 0;
        } else {
            $spendingVariance = 0.1;
        }

        // 9. Emergency Fund (Check if there is a goal named 'Emergency' or safe gap)
        $emergencyFundRatio = ($totalIncome - $totalExpense) > ($avgMonthlyIncome * 3) ? 1.0 : max(0, ($totalIncome - $totalExpense) / 20000);

        // 10. Income Growth (Compare last month income vs avg)
        $lastItem = $monthly ? $monthly->last() : null;
        $lastMonthIncome = (is_object($lastItem) && isset($lastItem->income)) ? (float)$lastItem->income : 0.0;
        $incomeGrowthRate = $avgMonthlyIncome > 0 ? (($lastMonthIncome - $avgMonthlyIncome) / $avgMonthlyIncome) : 0;

        // 11. Income Stability (Derived from fixed commitments vs income level)
        // If income is 0, stability is 0.0 (High Risk), not 0.8 (Default)
        $incomeStability = $totalIncome > 0 ? (1 - ($fixedExpensesLastMonth / ($avgMonthlyIncome ?: 1))) : 0.0;
        $incomeStability = max(0.0, min(1.0, $incomeStability));

        return [
             'monthly_spending_ratio' => (float) $monthlySpendingRatio,
             'savings_ratio' => (float) $savingsRatio,
             'income_level' => (float) $incomeLevel,
             'fixed_commitments_ratio' => (float) $fixedRatio,
             'goal_progress_ratio' => (float) $goalProgressRatio,
             'goal_delay_ratio' => (float) $goalDelayRatio,
             'expense_frequency' => (float) $expenseFrequency,
             'spending_variance' => (float) $spendingVariance,
             'emergency_fund_ratio' => (float) $emergencyFundRatio,
             'income_growth_rate' => (float) $incomeGrowthRate,
             'income_stability' => (float) $incomeStability,
             'risk_level' => (float) (1 - $incomeStability) // Risk inversely proportional to stability
        ];
    }

    /**
     * Generates simulated AI insights (Synchronized logic between Dashboard and Reports).
     */
    public function generateGlobalInsights($income, $expense, $savingsRate, $budgets, $goals): array
    {
        $insights = [];
        $userName = optional(auth()->user())->name ?? '';
        $goalsCollection = $goals instanceof \Illuminate\Support\Collection ? $goals : collect($goals);
        $budgetsCollection = $budgets instanceof \Illuminate\Support\Collection ? $budgets : collect($budgets);

        $prefixes = [
            'success' => [
                "تحليل إيجابي: ", "إشادة مهنية: ", "تقرير قيراط السريع: ", "نلاحظ بتقدير: ", "رصد إنجاز نوعي: ",
                "وفقاً لسجلاتك: ", "إشارة نجاح: ", ""
            ],
            'warning' => [
                "تنبيه استراتيجي: ", "تحذير مالي حازم: ", "تقرير المخاطر: ", "إشعار وقائي: ", "رصد انحراف: ",
                "إخطار مهني: ", "ملاحظة رقابية: ", ""
            ],
            'info' => [
                "رؤية مالية: ", "توصية فنية: ", "تحليل البيانات: ", "ملاحظة مهنية: ", "تقرير الأداء: ",
                "اقتراح تنظيمي: ", "نصيحة قيراط: ", ""
            ]
        ];

        $getMsg = function($key, $vars, $type) use ($prefixes) {
            $prefixList = $prefixes[$type] ?? $prefixes['info'];
            $prefix = $prefixList[array_rand($prefixList)];
            return $prefix . __($key, $vars);
        };

        // 1. Savings Rate Insight
        if ($savingsRate > 20) {
            $key = 'highSavingsRate';
            $insights[] = [
                'type' => 'success',
                'icon' => 'bi-stars',
                'title' => 'أداء مالي استثنائي',
                'message' => $getMsg($key, ['rate' => number_format($savingsRate, 1), 'name' => $userName], 'success'),
                'priority' => 10 // High priority
            ];
        } elseif ($savingsRate > 0) {
            $key = 'goodSavingsStart';
            $insights[] = [
                'type' => 'info',
                'icon' => 'bi-lightning-charge',
                'title' => 'تحليل مؤشر النمو',
                'message' => $getMsg($key, ['name' => $userName], 'info'),
                'priority' => 5
            ];
        } else {
            $key = 'financialDeficit';
            $insights[] = [
                'type' => 'warning',
                'icon' => 'bi-exclamation-triangle',
                'title' => 'تنبيه استراتيجي',
                'message' => $getMsg($key, ['name' => $userName], 'warning'),
                'priority' => 9
            ];
        }

        // 2. Budget Adherence (Centralized)
        $budgetStats = $this->budgetInsights->checkGlobalStatus($budgetsCollection);
        $overBudgetsCount = $budgetStats['overrun'];
        
        if ($overBudgetsCount > 0) {
            $key = 'budgetOverrun';
            $insights[] = [
                'type' => 'danger',
                'icon' => 'bi-graph-down-arrow',
                'title' => 'رصد تجاوز الميزانية',
                'message' => $getMsg($key, ['count' => $overBudgetsCount], 'warning'),
                'priority' => 8
            ];
        }

        // 3. Goals Progress
        $nearGoals = $goalsCollection->filter(function($g) {
            // handle both array and object
            $prog = is_array($g) ? ($g['progress'] ?? 0) : ($g->progress ?? 0);
            return $prog >= 80 && $prog < 100;
        })->count();

        if ($nearGoals > 0) {
            $key = 'goalsNearCompletion';
            $insights[] = [
                'type' => 'success',
                'icon' => 'bi-award',
                'title' => 'كفاءة تحقيق الأهداف',
                'message' => $getMsg($key, ['count' => $nearGoals], 'success'),
                'priority' => 7
            ];
        }

        // 4. Goals Approaching Deadline (New)
        $urgentGoals = $goalsCollection->filter(function($g) {
            $deadline = is_array($g) ? ($g['deadline'] ?? null) : ($g->deadline ?? null);
            $prog = is_array($g) ? ($g['progress'] ?? 0) : ($g->progress ?? 0);
            if (!$deadline || $prog >= 100) return false;
            return \Carbon\Carbon::parse($deadline)->diffInDays(now(), false) >= -7 && \Carbon\Carbon::parse($deadline)->isFuture();
        });

        foreach ($urgentGoals as $ug) {
            $name = is_array($ug) ? $ug['name'] : $ug->name;
            $insights[] = [
                'type' => 'warning',
                'icon' => 'bi-alarm',
                'title' => 'اقتراب الموعد النهائي',
                'message' => "تنبيه: اقترب الموعد النهائي لهدف ($name). تحقق من تقدمك لضمان التحقيق في الوقت المحدد.",
                'priority' => 12
            ];
        }

        // 5. Expired Budgets Reset Reminder (New)
        $expiredBudgets = $budgetsCollection->filter(function($b) {
            $end = is_array($b) ? ($b['period_end'] ?? null) : ($b->period_end ?? null);
            return $end && \Carbon\Carbon::parse($end)->isPast();
        });

        if ($expiredBudgets->count() > 0) {
            $insights[] = [
                'type' => 'info',
                'icon' => 'bi-arrow-repeat',
                'title' => 'إعادة ضبط الميزانية',
                'message' => "لاحظنا انتهاء فترة بعض الميزانيات. يُنصح بإعادة ضبطها الآن للحفاظ على دقة التتبع المالي.",
                'priority' => 11
            ];
        }
        
        // Default advice if none
        if (empty($insights)) {
            $key = 'defaultIntelligenceAdvice';
            $insights[] = [
                'type' => 'info', // Changed to info string for consistency
                'icon' => 'bi-lightbulb',
                'title' => 'رؤية مالية متقدمة',
                'message' => $getMsg($key, ['name' => $userName], 'info'),
                'priority' => 1
            ];
        }

        return $insights;
    }
}
