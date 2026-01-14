<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Transaction;
use App\Models\Goal;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $start = $request->date('start') ?? Carbon::now()->startOfMonth();
        $end = $request->date('end') ?? Carbon::now()->endOfMonth();

        // Normalize to Carbon instances
        if (!$start instanceof Carbon) {
            $start = Carbon::parse($start);
        }
        if (!$end instanceof Carbon) {
            $end = Carbon::parse($end);
        }

        // Core totals for the selected period
        $baseQuery = Transaction::whereDate('occurred_at', '>=', $start->toDateString())
            ->whereDate('occurred_at', '<=', $end->toDateString());

        $totalIncome = (float) $baseQuery->clone()->where('type', 'income')->sum('amount');
        $totalExpense = (float) $baseQuery->clone()->where('type', 'expense')->sum('amount');
        $net = $totalIncome - $totalExpense;
        $savingsRate = $totalIncome > 0 ? max(0, ($net / $totalIncome) * 100) : 0;

        // Category breakdown (expenses)
        $categoryBreakdown = $baseQuery->clone()
            ->where('type', 'expense')
            ->select('category')
            ->selectRaw('SUM(amount) as total')
            ->groupBy('category')
            ->orderByDesc('total')
            ->get()
            ->map(function ($row) {
                return [
                    'category' => $row->category ?? 'غير مصنف',
                    'total' => (float) $row->total,
                ];
            });

        // Budgets overlapping the selected period
        $budgets = Budget::with('category')
            ->whereDate('period_end', '>=', $start->toDateString())
            ->whereDate('period_start', '<=', $end->toDateString())
            ->orderBy('period_start')
            ->get();

        // Compute spent for each budget preferring category_id; fallback to name
        $budgetCards = $budgets->map(function (Budget $budget) use ($start, $end) {
            $category = $budget->category;
            $categoryName = optional($category)->name;

            // Intersection of report period with budget period
            $effectiveStart = $start->greaterThan($budget->period_start) ? $start->copy() : Carbon::parse($budget->period_start);
            $effectiveEnd = $end->lessThan($budget->period_end) ? $end->copy() : Carbon::parse($budget->period_end);

            $spent = 0.0;
            if ($category) {
                $spent = (float) Transaction::where('type', 'expense')
                    ->when(true, function ($q) use ($category, $categoryName) {
                        // Prefer foreign key match; fallback to name if FK missing
                        return $q->where(function ($qq) use ($category, $categoryName) {
                            $qq->where('category_id', $category->id);
                            if ($categoryName) {
                                $qq->orWhere('category', $categoryName);
                            }
                        });
                    })
                    ->whereDate('occurred_at', '>=', $effectiveStart->toDateString())
                    ->whereDate('occurred_at', '<=', $effectiveEnd->toDateString())
                    ->sum('amount');
            }

            $limit = (float) $budget->limit_amount;
            $remaining = $limit - $spent;
            $progress = $limit > 0 ? min(100, round(($spent / $limit) * 100)) : 0;
            $over = $remaining < 0;

            return [
                'id' => $budget->id,
                'category' => $categoryName ?? 'غير محدد',
                'limit' => $limit,
                'spent' => $spent,
                'remaining' => $remaining,
                'progress' => $progress,
                'over' => $over,
                'period_start' => optional($budget->period_start)->toDateString(),
                'period_end' => optional($budget->period_end)->toDateString(),
                'status' => $budget->status,
            ];
        });

        // Top 3 overspends
        $overspends = $budgetCards
            ->map(fn($b) => $b + ['overspend' => max(0, $b['spent'] - $b['limit'])])
            ->sortByDesc('overspend')
            ->take(3)
            ->values();

        // Global statistics for charts/cards
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

        $transactionsCount = Transaction::count();
        $completedGoals = Goal::where('status', 'completed')->count();
        $expenseLast30 = Transaction::where('type', 'expense')
            ->whereDate('occurred_at', '>=', now()->subDays(30))
            ->sum('amount');
        $avgDaily = $expenseLast30 > 0 ? $expenseLast30 / 30 : 0;
        $avgTransaction = (float) Transaction::avg('amount');
        $topExpenseCategory = optional($categoryBreakdown->first())['category'] ?? null;

        $dashboardData = [
            'months' => $monthly->pluck('month'),
            'income' => $monthly->pluck('income')->map(fn ($v) => (float) $v),
            'expense' => $monthly->pluck('expense')->map(fn ($v) => (float) $v),
            'categories' => $categoryBreakdown->pluck('category'),
            'categoryTotals' => $categoryBreakdown->pluck('total')->map(fn ($v) => (float) $v),
            'transactionsCount' => $transactionsCount,
            'completedGoals' => $completedGoals,
            'avgDaily' => (float) $avgDaily,
            'avgTransaction' => $avgTransaction,
            'savingsRate' => (float) $savingsRate,
            'topExpenseCategory' => $topExpenseCategory,
        ];

        // Active goals list for merged page
        $goals = Goal::orderBy('deadline')->get();
        $activeGoals = $goals->filter(fn ($g) => $g->progress < 100)->sortByDesc('progress');

        return view('reports.index', [
            'start' => $start->toDateString(),
            'end' => $end->toDateString(),
            'totalIncome' => $totalIncome,
            'totalExpense' => $totalExpense,
            'net' => $net,
            'savingsRate' => $savingsRate,
            'categoryBreakdown' => $categoryBreakdown,
            'budgets' => $budgetCards,
            'overspends' => $overspends,
            'dashboardData' => $dashboardData,
            'activeGoals' => $activeGoals,
        ]);
    }

    public function export(Request $request)
    {
        $start = $request->query('start');
        $end = $request->query('end');
        $request->merge(['start' => $start, 'end' => $end]);

        // Reuse index logic for data
        $response = $this->index($request);
        if (method_exists($response, 'getData')) {
            $data = $response->getData();
        } else {
            // Fallback: recompute minimal fields
            $data = [];
        }

        $rows = [];
        $rows[] = ['من', 'إلى', 'إجمالي الدخل', 'إجمالي المصروف', 'الصافي', 'معدل الادخار %'];
        $rows[] = [
            $data['start'] ?? $start,
            $data['end'] ?? $end,
            $data['totalIncome'] ?? 0,
            $data['totalExpense'] ?? 0,
            $data['net'] ?? 0,
            $data['savingsRate'] ?? 0,
        ];

        $rows[] = [];
        $rows[] = ['الفئة', 'المصروف'];
        if (!empty($data['categoryBreakdown'])) {
            foreach ($data['categoryBreakdown'] as $row) {
                $rows[] = [$row['category'], $row['total']];
            }
        }

        $rows[] = [];
        $rows[] = ['الميزانية - الفئة', 'الفترة', 'الحد', 'المصروف', 'المتبقي'];
        if (!empty($data['budgets'])) {
            foreach ($data['budgets'] as $b) {
                $rows[] = [
                    $b['category'],
                    ($b['period_start'] ?? '') . ' -> ' . ($b['period_end'] ?? ''),
                    $b['limit'],
                    $b['spent'],
                    $b['remaining'],
                ];
            }
        }

        $fh = fopen('php://temp', 'w+');
        foreach ($rows as $r) {
            fputcsv($fh, $r);
        }
        rewind($fh);
        $csv = stream_get_contents($fh);
        fclose($fh);

        $filename = 'report_' . ($data['start'] ?? $start) . '_' . ($data['end'] ?? $end) . '.csv';
        return response($csv)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    public function exportPdf(Request $request)
    {
        $start = $request->query('start');
        $end = $request->query('end');
        $request->merge(['start' => $start, 'end' => $end]);

        // Compute data
        $startC = $start ? Carbon::parse($start) : Carbon::now()->startOfMonth();
        $endC = $end ? Carbon::parse($end) : Carbon::now()->endOfMonth();

        $baseQuery = Transaction::whereBetween('occurred_at', [$startC->toDateString(), $endC->toDateString()]);
        $totalIncome = (float) $baseQuery->clone()->where('type', 'income')->sum('amount');
        $totalExpense = (float) $baseQuery->clone()->where('type', 'expense')->sum('amount');
        $net = $totalIncome - $totalExpense;
        $savingsRate = $totalIncome > 0 ? max(0, ($net / $totalIncome) * 100) : 0;
        $categoryBreakdown = $baseQuery->clone()
            ->where('type','expense')
            ->select('category')
            ->selectRaw('SUM(amount) as total')
            ->groupBy('category')
            ->orderByDesc('total')
            ->get();

        $budgets = Budget::with('category')
            ->whereDate('period_end', '>=', $startC->toDateString())
            ->whereDate('period_start', '<=', $endC->toDateString())
            ->orderBy('period_start')
            ->get();

        $budgetCards = $budgets->map(function (Budget $budget) use ($startC, $endC) {
            $category = $budget->category;
            $categoryName = optional($category)->name;
            $effectiveStart = $startC->greaterThan($budget->period_start) ? $startC->copy() : Carbon::parse($budget->period_start);
            $effectiveEnd = $endC->lessThan($budget->period_end) ? $endC->copy() : Carbon::parse($budget->period_end);
            $spent = 0.0;
            if ($category) {
                $spent = (float) Transaction::where('type','expense')
                    ->where(function ($qq) use ($category, $categoryName) {
                        $qq->where('category_id', $category->id);
                        if ($categoryName) { $qq->orWhere('category', $categoryName); }
                    })
                    ->whereBetween('occurred_at', [$effectiveStart->toDateString(), $effectiveEnd->toDateString()])
                    ->sum('amount');
            }
            $limit = (float) $budget->limit_amount;
            $remaining = $limit - $spent;
            $progress = $limit > 0 ? min(100, round(($spent / $limit) * 100)) : 0;
            $over = $remaining < 0;
            return compact('categoryName','limit','spent','remaining','progress','over') + [
                'period_start' => optional($budget->period_start)->toDateString(),
                'period_end' => optional($budget->period_end)->toDateString(),
            ];
        });

        $data = [
            'start' => $startC->toDateString(),
            'end' => $endC->toDateString(),
            'totalIncome' => $totalIncome,
            'totalExpense' => $totalExpense,
            'net' => $net,
            'savingsRate' => $savingsRate,
            'categoryBreakdown' => $categoryBreakdown,
            'budgets' => $budgetCards,
        ];

        $pdf = Pdf::loadView('reports.pdf', $data);
        $filename = 'report_' . $data['start'] . '_' . $data['end'] . '.pdf';
        return $pdf->download($filename);
    }
}
