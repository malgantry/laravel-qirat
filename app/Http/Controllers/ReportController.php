<?php

namespace App\Http\Controllers;

use App\Services\FinancialMetricService;
use App\Services\ReportExportService;
use App\Models\Goal;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function __construct(
        protected FinancialMetricService $metrics,
        protected ReportExportService $exporter,
        protected \App\Services\DashboardService $dashboardService
    ) {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $userId = auth()->id();
        $start = $request->query('start', now()->startOfMonth()->toDateString());
        $end = $request->query('end', now()->endOfMonth()->toDateString());

        $data = $this->getAggregatedData($userId, $start, $end);

        return view('reports.index', array_merge($data, [
            'start' => $start,
            'end' => $end
        ]));
    }

    public function exportExcel(Request $request)
    {
        $userId = auth()->id();
        $start = $request->query('start', now()->startOfMonth()->toDateString());
        $end = $request->query('end', now()->endOfMonth()->toDateString());

        $data = $this->collectExportData($userId, $start, $end);
        return $this->exporter->generateExcel($data);
    }

    public function exportPdf(Request $request)
    {
        $userId = auth()->id();
        $start = $request->query('start', now()->startOfMonth()->toDateString());
        $end = $request->query('end', now()->endOfMonth()->toDateString());

        $data = $this->collectExportData($userId, $start, $end);
        return $this->exporter->generatePdf($data, $start, $end);
    }

    /**
     * Collect data specifically for export inclusion (transactions).
     */
    protected function collectExportData(int $userId, string $start, string $end): array
    {
        $data = $this->getAggregatedData($userId, $start, $end);
        $data['transactions'] = Transaction::where('user_id', $userId)
            ->whereBetween('occurred_at', [$start, $end])
            ->orderByDesc('occurred_at')
            ->get();
        return $data;
    }

    /**
     * Centralized data aggregation for internal controller use.
     */
    protected function getAggregatedData(int $userId, string $start, string $end): array
    {
        $rangeMetrics = $this->metrics->getRangeMetrics($userId, $start, $end);
        $categoryBreakdown = $this->metrics->getCategoryBreakdown($userId, $start, $end);
        $budgets = $this->metrics->getBudgetAdherence($userId, $start, $end);
        $monthly = $this->metrics->getMonthlyStats($userId);

        $activeGoals = Goal::where('user_id', $userId)->where('status', 'in_progress')->get()
            ->map(function ($g) {
                $g->progress = $g->target_amount > 0 ? min(100, round(($g->current_amount / $g->target_amount) * 100)) : 0;
                return $g;
            });

        return [
            'totalIncome' => $rangeMetrics['income'],
            'totalExpense' => $rangeMetrics['expense'],
            'net' => $rangeMetrics['net'],
            'savingsRate' => $rangeMetrics['savingsRate'],
            'avgDailyExpense' => $rangeMetrics['avgDailyExpense'],
            'categoryBreakdown' => $categoryBreakdown,
            'budgets' => $budgets,
            'activeGoals' => $activeGoals,
            'dashboardData' => [
                'months' => $monthly->pluck('month'),
                'income' => $monthly->pluck('income')->map(fn($v) => (float)$v),
                'expense' => $monthly->pluck('expense')->map(fn($v) => (float)$v),
                'categories' => $categoryBreakdown->pluck('category'),
                'categoryTotals' => $categoryBreakdown->pluck('total'),
                'categoryColors' => $categoryBreakdown->pluck('color'),
                'aiFeatures' => $this->dashboardService->calculateAiFeatures($monthly, $rangeMetrics['income'], $rangeMetrics['expense'], $activeGoals, collect()),
            ]
        ];
    }

    /**
     * BC helper for external controllers.
     */
    public function getReportMetrics($userId, $start, $end): array
    {
         return $this->getAggregatedData($userId, $start, $end);
    }

    public function setLocale(Request $request)
    {
        session(['locale' => $request->input('locale', 'ar')]);
        return back();
    }
}
