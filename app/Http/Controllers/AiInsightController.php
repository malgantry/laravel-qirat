<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use App\Services\FinancialMetricService;
use App\Services\Insights\TransactionInsightsService;
use App\Services\Insights\GoalInsightsService;
use App\Services\Insights\BudgetInsightsService;
use App\Models\Transaction;
use App\Models\Goal;
use App\Models\Budget;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AiInsightController extends Controller
{
    public function __construct(
        protected DashboardService $dashboardService,
        protected FinancialMetricService $metrics
    ) {
        $this->middleware('auth');
    }

    /**
     * Fetch AI insights for the dashboard (transactions & goals).
     */
    public function dashboard(Request $request): JsonResponse
    {
        $data = $this->dashboardService->getData(true);
        
        return response()->json([
            'status' => 'success',
            'txFeedback' => $data['txFeedback'],
            'goalFeedback' => $data['goalFeedback'],
            'globalInsights' => $data['globalInsights'] ?? [],
        ]);
    }

    /**
     * Fetch AI insights for a batch of transactions.
     */
    public function transactions(Request $request, TransactionInsightsService $insightsService): JsonResponse
    {
        $ids = $request->query('ids', []);
        if (!is_array($ids)) $ids = explode(',', $ids);

        $transactions = Transaction::where('user_id', auth()->id())->whereIn('id', $ids)->get();
        
        $txFeedback = [];
        foreach ($transactions as $tx) {
            if ($fb = $insightsService->generateFor($tx)) {
                $txFeedback[$tx->id] = $fb;
            }
        }

        return response()->json(['status' => 'success', 'feedback' => $txFeedback]);
    }

    /**
     * Fetch AI insights for all active goals on the goals page.
     */
    public function goals(Request $request, GoalInsightsService $insightsService): JsonResponse
    {
        $goals = Goal::where('user_id', auth()->id())->get();
        
        $goalFeedback = [];
        foreach ($goals as $g) {
            if ($fb = $insightsService->generateFor($g)) {
                $goalFeedback[$g->id] = $fb;
            }
        }

        return response()->json(['status' => 'success', 'feedback' => $goalFeedback]);
    }

    /**
     * Fetch AI insights for the reports page using unified metrics service.
     */
    public function reports(Request $request): JsonResponse
    {
        try {
            $userId = auth()->id();
            $start = $request->query('start') ?? now()->startOfMonth()->toDateString();
            $end = $request->query('end') ?? now()->endOfMonth()->toDateString();

            $rangeData = $this->metrics->getRangeMetrics($userId, $start, $end);
            $budgets = $this->metrics->getBudgetAdherence($userId, $start, $end);
            
            $activeGoals = Goal::where('user_id', $userId)->where('status', 'in_progress')->get()
                 ->map(fn($g) => $g->toArray() + ['progress' => $g->progress]);

            $aiInsights = $this->dashboardService->generateGlobalInsights(
                $rangeData['income'],
                $rangeData['expense'],
                $rangeData['savingsRate'],
                $budgets,
                $activeGoals
            );

            return response()->json([
                'status' => 'success',
                'aiInsights' => $aiInsights,
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('AI Reports Failed: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'aiInsights' => []], 500);
        }
    }

    /**
     * Fetch AI insights for all active budgets.
     */
    public function budgets(Request $request, BudgetInsightsService $insightsService): JsonResponse
    {
        $budgets = Budget::where('user_id', auth()->id())->whereDate('period_end', '>=', now())->get();
        
        $feedback = [];
        foreach ($budgets as $b) {
            if ($fb = $insightsService->generateFor($b)) {
                $feedback[$b->id] = $fb;
            }
        }

        return response()->json(['status' => 'success', 'feedback' => $feedback]);
    }

    /**
     * Auto-classify transaction based on description.
     */
    public function classify(Request $request): JsonResponse
    {
        $desc = strtolower($request->input('description', ''));
        if (!$desc) return response()->json(['status' => 'error', 'category' => null]);

        $keywords = [
            'food' => ['supermarket', 'pizza', 'burger', 'restaurant', 'kf', 'mcd', 'starbucks', 'coffee', 'cafe', 'طعام', 'مطعم', 'بقالة', 'تموينات', 'قهوة', 'اسبريسو'],
            'transport' => ['uber', 'careem', 'taxi', 'gas', 'fuel', 'station', 'bus', 'train', 'مواصلات', 'بانزين', 'محطة', 'اوبر', 'كريم', 'توصيل'],
            'bills' => ['electricity', 'water', 'internet', 'stc', 'mobily', 'zain', 'bill', 'subscription', 'netflix', 'spotify', 'فواتير', 'كهرباء', 'ماء', 'نت', 'فاتورة'],
            'shopping' => ['amazon', 'noon', 'jarir', 'extra', 'clothes', 'zara', 'nike', 'adidas', 'تسوق', 'ملابس', 'جرير', 'نون', 'امازون', 'شراء'],
            'health' => ['pharmacy', 'doctor', 'hospital', 'clinic', 'dentist', 'drug', 'صيدلية', 'دواء', 'طبيب', 'مستشفى', 'عيادة'],
            'entertainment' => ['cinema', 'movie', 'game', 'playstation', 'steam', 'event', 'party', 'سينما', 'تذكرة', 'لعبة', 'حفلة'],
            'education' => ['course', 'book', 'udemy', 'coursera', 'university', 'school', 'tuition', 'تعليم', 'كتاب', 'دورة', 'جامعة', 'مدرسة']
        ];

        foreach ($keywords as $catKey => $words) {
            foreach ($words as $word) {
                if (str_contains($desc, $word)) {
                    $map = ['food' => 'طعام', 'transport' => 'مواصلات', 'bills' => 'فواتير', 'shopping' => 'تسوق', 'health' => 'صحة', 'entertainment' => 'ترفيه', 'education' => 'تعليم'];
                    $name = $map[$catKey] ?? $catKey;
                    
                    $cat = \App\Models\Category::where('user_id', auth()->id())
                        ->where('name', 'LIKE', "%$name%")->first();

                    if ($cat) {
                         return response()->json(['status' => 'success', 'category_id' => $cat->id, 'category_name' => $cat->name]);
                    }
                }
            }
        }

        return response()->json(['status' => 'neutral', 'message' => 'No match found']);
    }
}
