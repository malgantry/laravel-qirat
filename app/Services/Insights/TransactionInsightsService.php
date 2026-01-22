<?php

namespace App\Services\Insights;

use App\Models\Transaction;
use App\Services\AiClient;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

class TransactionInsightsService
{
    public function __construct(private readonly AiClient $ai)
    {
    }

    /**
     * @param \Illuminate\Database\Eloquent\Collection $transactions
     * @return array
     */
    public function generateBatch($transactions): array
    {
        $results = [];
        $misses = collect();

        // 1. Check Cache for all
        foreach ($transactions as $tx) {
             $key = $this->getCacheKey($tx);
             if (Cache::has($key)) {
                 $results[$tx->id] = Cache::get($key);
             } else {
                 $misses->push($tx);
             }
        }

        // 2. Fetch Misses from AI (Batch) or Fallback
        if ($misses->isNotEmpty()) {
            $batchResponses = [];

            if (Config::get('ai.enabled')) {
                try {
                    // Call AI Batch endpoint
                    $batchResponses = $this->ai->analyzeTransactionsBatch($misses);
                } catch (\Throwable $e) {
                    // Ignore AI failure, proceed to baseline
                }
            }
            
            foreach ($misses as $tx) {
                $raw = $batchResponses[$tx->id] ?? [];
                $mapped = null;
                if (!empty($raw)) {
                        $mapped = $this->mapAiFeedback($raw[0], optional(auth()->user())->name ?? '') ?? null;
                }
                
                // Fallback to baseline rule if AI empty
                if (!$mapped) {
                    $mapped = $this->getBaseline($tx);
                }

                if ($mapped) {
                    $mapped = $this->adjustPriority($mapped, $tx);
                }
                
                
                // Cache result
                Cache::put($this->getCacheKey($tx), $mapped, 1440); // 24 hours
                $results[$tx->id] = $mapped;
            }
        }

        return $results;
    }

    private function getCacheKey(Transaction $tx): string
    {
        $userId = (string) (optional(auth()->user())->id ?? 'default');
        $stamp = optional($tx->updated_at ?? $tx->created_at)->toIso8601String() ?? '';

        // Cache key driven by transaction identity + last update only, so the same recommendation is reused across pages for the same record
        $finger = implode(':', [
            (string) $tx->id,
            $userId,
            $stamp,
            'v5'
        ]);

        return 'insights:tx:' . md5($finger);
    }

    private function getBaseline(Transaction $tx): ?array
    {
         $isIncome = $tx->type === 'income';
        $amount = (float) $tx->amount;
        $userName = optional(auth()->user())->name ?? '';

        // Unusual spend spike vs last 30-day average for the same category
        if (!$isIncome && $tx->category) {
            $avg = \App\Models\Transaction::where('user_id', $tx->user_id)
                ->where('type', 'expense')
                ->where('category', $tx->category)
                ->whereDate('occurred_at', '>=', now()->subDays(30))
                ->avg('amount');

            // Noise gate: require a meaningful baseline and total spend over last 7 days
            $weeklySum = \App\Models\Transaction::where('user_id', $tx->user_id)
                ->where('type', 'expense')
                ->where('category', $tx->category)
                ->whereDate('occurred_at', '>=', now()->subDays(7))
                ->sum('amount');

            if ($avg && $avg >= 30 && $weeklySum >= 80 && $amount >= 1.6 * $avg && $amount >= 50) {
                return $this->make(
                    'warning',
                    9,
                    'unusualSpendSpike',
                    [
                        'category' => $tx->category,
                        'percent' => number_format((($amount / $avg) - 1) * 100, 0),
                        'name' => $userName
                    ],
                    "تنبيه: إنفاقك في فئة {$tx->category} ارتفع بنسبة " . number_format((($amount / $avg) - 1) * 100, 0) . "% عن متوسط 30 يوماً. خفف المصروفات اليوم لاستعادة التوازن."
                );
            }
        }

        // Large single expense fallback
        if (!$isIncome && $amount >= 50.0) {
            return $this->make(
                'warning', 
                7, 
                'largeAmountWarning', 
                ['amount' => number_format($amount, 0), 'name' => $userName],
                __('largeAmountWarning', ['amount' => number_format($amount, 0), 'name' => $userName])
            );
        }
        return null;
    }

    /**
     * @return array<string, mixed>|null Single feedback item or null
     */
    public function generateFor(Transaction $tx): ?array
    {
        // ... kept for single generic calls if needed, or redirect to batch logic
        return $this->generateBatch(collect([$tx]))[$tx->id] ?? null;
    }

    private function make(string $type, int $priority, string $key, array $vars = [], ?string $message = null, ?string $action = null): array
    {
        return compact('type', 'priority', 'key', 'vars', 'message', 'action');
    }

    private function mapAiFeedback(array $item, string $userName): ?array
    {
        $type = $item['type'] ?? 'info';
        $priority = (int) ($item['priority'] ?? 5);
        $key = $item['key'] ?? null;
        
        if (!$key) {
            $msg = $item['message'] ?? '';
            $lower = mb_strtolower($msg);
            if (str_contains($lower, 'كبير') || str_contains($lower, 'large')) $key = 'largeAmountWarning';
            elseif (str_contains($lower, 'ممتاز') || str_contains($lower, 'great income')) $key = 'greatIncome';
            elseif (str_contains($lower, 'معتدل') || str_contains($lower, 'moderate')) $key = 'moderateExpense';
            elseif (str_contains($lower, 'بسيط') || str_contains($lower, 'small')) $key = 'smallExpense';
            else $key = 'smartAdvice';
        }

        $vars = $item['vars'] ?? [];
        $vars['name'] = $userName;
        if (isset($item['amount'])) {
            $vars['amount'] = number_format($item['amount'], 0);
        }

        $message = $item['message'] ?? __($key, $vars);

        $action = $item['action_type'] ?? null;
        $id = $item['id'] ?? null;
        
        $res = $this->make($type, $priority, $key, $vars, $message, $action);
        if ($id) { $res['id'] = (string) $id; }
        return $res;
    }

    private function adjustPriority(array $fb, Transaction $tx): array
    {
        $priority = (int) ($fb['priority'] ?? 5);
        $category = $tx->category;

        if ($category) {
            $count7d = Transaction::where('user_id', $tx->user_id)
                ->where('category', $category)
                ->where('type', $tx->type)
                ->whereDate('occurred_at', '>=', now()->subDays(7))
                ->count();

            if ($count7d >= 5) {
                $priority += 3;
            } elseif ($count7d >= 3) {
                $priority += 2;
            }
        }

        $fb['priority'] = min(10, $priority);
        return $fb;
    }

    private function resolveBudgetLimitFor(Transaction $tx): ?float
    {
        // Try to find a budget limit for this category and date
        try {
            $categoryName = (string) $tx->category;
            $date = optional($tx->occurred_at)->toDateString();
            if ($categoryName === '' || !$date) return null;
            $cat = \App\Models\Category::query()
                ->where('user_id', $tx->user_id)
                ->where('name', $categoryName)
                ->first();
            if (!$cat) return null;
            $budget = \App\Models\Budget::query()
                ->where('category_id', $cat->id)
                ->where('user_id', $tx->user_id)
                ->whereDate('period_start', '<=', $date)
                ->whereDate('period_end', '>=', $date)
                ->orderByDesc('period_start')
                ->first();
            return $budget ? (float) $budget->limit_amount : null;
        } catch (\Throwable $e) {
            return null;
        }
    }
}
