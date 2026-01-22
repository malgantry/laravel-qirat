<?php

declare(strict_types=1);

namespace App\Services\Insights;

use App\Models\Budget;
use App\Models\Transaction;
use App\Services\AiClient;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class BudgetInsightsService
{
    /**
     * Limit showing the same insight key across a batch to avoid repetition fatigue.
     */
    private int $maxPerKey = 2;

    /**
     * Limit showing identical message text across a batch to avoid duplicate wording.
     */
    private int $maxPerMessage = 2;

    public function __construct(private readonly AiClient $ai)
    {
    }

    /**
     * Generate insights for multiple budgets with caching.
     */
    public function generateBatch($budgets): array
    {
        $results = [];
        $misses = collect();
        $keyCounts = [];
        $messageCounts = [];

        foreach ($budgets as $budget) {
            $key = $this->getCacheKey($budget);
            if (Cache::has($key)) {
                $results[$this->getBudgetId($budget)] = Cache::get($key);
            } else {
                $misses->push($budget);
            }
        }

        foreach ($misses as $budget) {
            $insight = $this->generateLocalInsight($budget);
            if ($insight) {
                $insightKey = $insight['key'] ?? null;
                $messageText = isset($insight['message']) ? (string) $insight['message'] : '';
                $canShow = true;

                if ($insightKey) {
                    $current = $keyCounts[$insightKey] ?? 0;
                    if ($current >= $this->maxPerKey) {
                        $canShow = false; // skip repeated copies of the same recommendation key
                    } else {
                        $keyCounts[$insightKey] = $current + 1;
                    }
                }

                if ($canShow && $messageText !== '') {
                    $finger = md5($messageText);
                    $currentMsg = $messageCounts[$finger] ?? 0;
                    if ($currentMsg >= $this->maxPerMessage) {
                        $canShow = false; // skip duplicated wording
                    } else {
                        $messageCounts[$finger] = $currentMsg + 1;
                    }
                }

                if ($canShow) {
                    Cache::put($this->getCacheKey($budget), $insight, 60);
                    $results[$this->getBudgetId($budget)] = $insight;
                }
            }
        }

        return $results;
    }

    public function generateFor(Budget $budget): ?array
    {
        return $this->generateBatch(collect([$budget]))[$budget->id] ?? null;
    }

    private function getCacheKey($budget): string
    {
        $id = $this->getBudgetId($budget);
        $userId = $this->getBudgetUserId($budget);
        $stamp = $this->getBudgetTimestamp($budget);

        $finger = implode(':', [
            (string) $id,
            (string) $userId,
            (string) $stamp,
            app()->getLocale(),
            'v6',
        ]);

        return 'insights:budget:' . md5($finger);
    }

    private function getBudgetId($budget): int
    {
        return is_array($budget) ? (int) ($budget['id'] ?? 0) : (int) $budget->id;
    }

    private function getBudgetUserId($budget): string
    {
        return is_array($budget)
            ? (string) ($budget['user_id'] ?? (auth()->id() ?? 'default'))
            : (string) ($budget->user_id ?? (auth()->id() ?? 'default'));
    }

    private function getBudgetTimestamp($budget): string
    {
        if (is_array($budget)) {
            return (string) ($budget['updated_at'] ?? $budget['created_at'] ?? '');
        }

        $stamp = $budget->updated_at ?? $budget->created_at;
        return $stamp ? optional($stamp)->toIso8601String() : '';
    }

    private function generateLocalInsight($budget): ?array
    {
        $isArr = is_array($budget);
        $limit = (float) ($isArr ? ($budget['limit'] ?? ($budget['limit_amount'] ?? 0)) : $budget->limit_amount);

        if ($limit <= 0) {
            return null;
        }

        $spent = $this->resolveSpent($budget, $isArr);
        $ratio = $spent / $limit;
        $catName = $isArr ? ($budget['category'] ?? 'this category') : (optional($budget->category)->name ?? 'this category');

        if ($ratio >= 1.0) {
            return [
                'type' => 'warning',
                'priority' => 10,
                'key' => 'budgetExceeded',
                'message' => __('budgetExceededMessage', [
                    'category' => $catName,
                    'percent' => round(($ratio - 1) * 100),
                ]),
                'action_type' => 'review_transactions',
            ];
        }

        $periodEnd = $isArr ? ($budget['period_end'] ?? null) : ($budget->period_end ?? null);
        $daysLeft = $periodEnd ? now()->diffInDays(Carbon::parse($periodEnd), false) : null;

        if ($periodEnd && $daysLeft !== null && $daysLeft >= 0 && $daysLeft <= 3 && $ratio < 0.5) {
            return [
                'type' => 'info',
                'priority' => 4,
                'key' => 'budgetUnderUsed',
                'message' => __('budgetUnderUsedMessage', [
                    'category' => $catName,
                    'percent' => round($ratio * 100),
                ]),
                'action_type' => 'rebalance',
            ];
        }

        if ($ratio >= 0.85) {
            return [
                'type' => 'warning',
                'priority' => 8,
                'key' => 'budgetNearLimit',
                'message' => __('budgetNearLimitMessage', [
                    'category' => $catName,
                    'percent' => round($ratio * 100),
                ]),
                'action_type' => 'slow_down',
            ];
        }

        if ($ratio < 0.5 && $periodEnd && Carbon::parse($periodEnd)->isPast()) {
            return [
                'type' => 'success',
                'priority' => 6,
                'key' => 'budgetSaved',
                'message' => __('budgetSavedMessage', [
                    'category' => $catName,
                ]),
                'action_type' => 'celebrate',
            ];
        }

        if ($periodEnd && Carbon::parse($periodEnd)->isPast()) {
            return [
                'type' => 'info',
                'priority' => 7,
                'key' => 'budgetResetNeeded',
                'message' => __('budgetResetNeededMessage', [
                    'category' => $catName,
                ]),
                'action_type' => 'reset_budget',
            ];
        }

        return null;
    }

    private function resolveSpent($budget, bool $isArr): float
    {
        if ($isArr && isset($budget['spent'])) {
            return (float) $budget['spent'];
        }

        if ($isArr && isset($budget['spent_amount'])) {
            return (float) $budget['spent_amount'];
        }

        if (! $isArr && isset($budget->spent_amount)) {
            return (float) $budget->spent_amount;
        }

        return (float) Transaction::where('user_id', $isArr ? $budget['user_id'] : $budget->user_id)
            ->where('category_id', $isArr ? $budget['category_id'] : $budget->category_id)
            ->whereBetween('occurred_at', [
                $isArr ? $budget['period_start'] : $budget->period_start,
                $isArr ? $budget['period_end'] : $budget->period_end,
            ])
            ->where('type', 'expense')
            ->sum('amount');
    }

    public function checkGlobalStatus($budgets): array
    {
        $overrunCount = 0;
        $nearLimitCount = 0;

        foreach ($budgets as $budget) {
            $isArr = is_array($budget);
            $limit = (float) ($isArr ? ($budget['limit'] ?? ($budget['limit_amount'] ?? 0)) : $budget->limit_amount);

            if ($limit <= 0) {
                continue;
            }

            $spent = $this->resolveSpent($budget, $isArr);
            $ratio = $spent / $limit;

            if ($ratio > 1.0) {
                $overrunCount++;
            } elseif ($ratio > 0.85) {
                $nearLimitCount++;
            }
        }

        return [
            'overrun' => $overrunCount,
            'near_limit' => $nearLimitCount,
        ];
    }
}
