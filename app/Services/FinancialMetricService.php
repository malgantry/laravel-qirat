<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\Budget;
use App\Models\Goal;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class FinancialMetricService
{
    /**
     * Get core financial metrics for a user within a date range.
     */
    public function getRangeMetrics(int $userId, string $start, string $end): array
    {
        $income = Transaction::where('user_id', $userId)
            ->where('type', 'income')
            ->whereBetween('occurred_at', [$start, $end])
            ->sum('amount');

        $expense = Transaction::where('user_id', $userId)
            ->where('type', 'expense')
            ->whereBetween('occurred_at', [$start, $end])
            ->sum('amount');

        $net = $income - $expense;
        $savingsRate = $income > 0 ? max(0, (($net / $income) * 100)) : 0;
        
        $daysInPeriod = Carbon::parse($start)->diffInDays(Carbon::parse($end)) + 1;
        $avgDailyExpense = $daysInPeriod > 0 ? $expense / $daysInPeriod : 0;

        return [
            'income' => (float) $income,
            'expense' => (float) $expense,
            'net' => (float) $net,
            'savingsRate' => (float) $savingsRate,
            'avgDailyExpense' => (float) $avgDailyExpense,
        ];
    }

    /**
     * Get expense breakdown by category.
     */
    public function getCategoryBreakdown(int $userId, string $start, string $end): Collection
    {
        return Transaction::leftJoin('categories', 'transactions.category_id', '=', 'categories.id')
            ->where('transactions.user_id', $userId)
            ->where('transactions.type', 'expense')
            ->whereBetween('transactions.occurred_at', [$start, $end])
            ->selectRaw('COALESCE(categories.name, transactions.category) as category_name')
            ->selectRaw('MAX(categories.color) as category_color')
            ->selectRaw('MAX(categories.icon) as category_icon')
            ->selectRaw('SUM(transactions.amount) as total')
            ->groupBy('category_name')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row) => [
                'category' => $row->category_name ?? __('Uncategorized'),
                'total' => (float) $row->total,
                'color' => $row->category_color ?? '#9ca3af',
                'icon' => $row->category_icon ?? 'bi-tag',
            ]);
    }

    /**
     * Get budget adherence for a given period.
     */
    public function getBudgetAdherence(int $userId, string $start, string $end): Collection
    {
        $budgets = Budget::with('category')
            ->where('user_id', $userId)
            ->whereDate('period_end', '>=', $start)
            ->whereDate('period_start', '<=', $end)
            ->get();

        return $budgets->map(function (Budget $budget) use ($start, $end, $userId) {
            $s = Carbon::parse($start);
            $e = Carbon::parse($end);
            
            $effectiveStart = $s->greaterThan($budget->period_start) ? $s : Carbon::parse($budget->period_start);
            $effectiveEnd = $e->lessThan($budget->period_end) ? $e : Carbon::parse($budget->period_end);

            $spent = (float) Transaction::where('type', 'expense')
                ->where('user_id', $userId)
                ->where(function ($qq) use ($budget) {
                    if ($budget->category_id) $qq->where('category_id', $budget->category_id);
                    else $qq->where('category', $budget->category_name); // Fallback logic
                })
                ->whereBetween('occurred_at', [$effectiveStart, $effectiveEnd])
                ->sum('amount');

            $limit = (float) $budget->limit_amount;
            
            return [
                'id' => $budget->id,
                'category' => optional($budget->category)->name ?? 'غير محدد',
                'limit' => $limit,
                'spent' => $spent,
                'remaining' => $limit - $spent,
                'progress' => $limit > 0 ? min(100, round(($spent / $limit) * 100)) : 0,
                'over' => ($limit - $spent) < 0,
                'period_start' => $budget->period_start->toDateString(),
                'period_end' => $budget->period_end->toDateString(),
            ];
        });
    }

    /**
     * Get monthly aggregation for charts.
     */
    public function getMonthlyStats(int $userId): Collection
    {
        $driver = DB::connection()->getDriverName();
        $monthExpression = match ($driver) {
            'sqlite' => "strftime('%Y-%m', occurred_at)",
            'pgsql' => "to_char(occurred_at, 'YYYY-MM')",
            default => "DATE_FORMAT(occurred_at, '%Y-%m')",
        };

        return Transaction::where('user_id', $userId)
            ->selectRaw("{$monthExpression} as month")
            ->selectRaw("SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as income")
            ->selectRaw("SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as expense")
            ->groupBy('month')
            ->orderBy('month')
            ->get();
    }
}
