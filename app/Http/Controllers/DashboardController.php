<?php

namespace App\Http\Controllers;

use App\Models\Goal;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        if (auth()->check() && auth()->user()->is_admin && ! $request->wantsJson()) {
            return redirect()->route('admin.dashboard');
        }

        // Guests see a lightweight landing page before authentication.
        if (! auth()->check() && ! $request->wantsJson()) {
            return view('landing');
        }

        $latestTransactions = Transaction::orderByDesc('occurred_at')->orderByDesc('id')->limit(10)->get();
        $goals = Goal::orderBy('deadline')->get();

        $driver = DB::connection()->getDriverName();
        $monthExpression = match ($driver) {
            'sqlite' => "strftime('%Y-%m', occurred_at)",
            'pgsql' => "to_char(occurred_at, 'YYYY-MM')",
            default => "DATE_FORMAT(occurred_at, '%Y-%m')",
        };

        $monthly = Transaction::selectRaw("{$monthExpression} as month")
            ->selectRaw("SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as income")
            ->selectRaw("SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as expense")
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $categoryBreakdown = Transaction::where('type', 'expense')
            ->select('category')
            ->selectRaw('SUM(amount) as total')
            ->groupBy('category')
            ->orderByDesc('total')
            ->get();

        $totalIncome = Transaction::where('type', 'income')->sum('amount');
        $totalExpense = Transaction::where('type', 'expense')->sum('amount');

        $transactionsCount = Transaction::count();
        $completedGoals = Goal::where('status', 'completed')->count();
        $expenseLast30 = Transaction::where('type', 'expense')
            ->whereDate('occurred_at', '>=', now()->subDays(30))
            ->sum('amount');
        $avgDaily = $expenseLast30 > 0 ? $expenseLast30 / 30 : 0;
        $avgTransaction = (float) Transaction::avg('amount');
        $savingsRate = $totalIncome > 0 ? max(0, (($totalIncome - $totalExpense) / $totalIncome) * 100) : 0;
        $topExpenseCategory = optional($categoryBreakdown->first())->category;

        $dashboardData = [
            'months' => $monthly->pluck('month'),
            'income' => $monthly->pluck('income')->map(fn ($v) => (float) $v),
            'expense' => $monthly->pluck('expense')->map(fn ($v) => (float) $v),
            'categories' => $categoryBreakdown->pluck('category'),
            'categoryTotals' => $categoryBreakdown->pluck('total')->map(fn ($v) => (float) $v),
            'totalIncome' => (float) $totalIncome,
            'totalExpense' => (float) $totalExpense,
            'transactionsCount' => $transactionsCount,
            'completedGoals' => $completedGoals,
            'avgDaily' => (float) $avgDaily,
            'avgTransaction' => $avgTransaction,
            'savingsRate' => (float) $savingsRate,
            'topExpenseCategory' => $topExpenseCategory,
            'goals' => $goals->map(fn ($goal) => [
                'id' => $goal->id,
                'name' => $goal->name,
                'deadline' => optional($goal->deadline)->toDateString(),
                'target_amount' => (float) $goal->target_amount,
                'current_amount' => (float) $goal->current_amount,
                'progress' => $goal->progress,
                'status' => $goal->status,
            ]),
            'transactions' => $latestTransactions->map(fn ($transaction) => [
                'id' => $transaction->id,
                'type' => $transaction->type,
                'category' => $transaction->category,
                'amount' => (float) $transaction->amount,
                'occurred_at' => optional($transaction->occurred_at)->toDateString(),
                'note' => $transaction->note,
            ]),
        ];

        if ($request->wantsJson()) {
            return response()->json($dashboardData);
        }

        return view('welcome', [
            'latestTransactions' => $latestTransactions,
            'goals' => $goals,
            'dashboardData' => $dashboardData,
        ]);
    }
}
