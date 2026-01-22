<?php

namespace App\Services;

use App\Models\Goal;
use App\Models\Transaction;
use Carbon\CarbonInterface;

class FeatureBuilder
{
    /**
     * Build a fixed-length (12) feature vector for transaction analysis.
     */
    public function buildTransactionFeatures(Transaction $tx, ?float $budgetLimit = null): array
    {
        $occurredAt = $tx->occurred_at instanceof CarbonInterface ? $tx->occurred_at : now();
        $limit = $budgetLimit ?? 0.0;
        $amount = (float) $tx->amount;
        $ratio = $limit > 0 ? min(10.0, $amount / $limit) : 0.0;

        $features = [
            $amount,                                         // 1 amount
            $limit,                                          // 2 budget cap
            $ratio,                                          // 3 spend vs cap ratio
            $this->categoryHash((string) $tx->category),     // 4 category hash in [0,1]
            strlen((string) $tx->note ?? '') / 255,          // 5 note length normalized
            $tx->type === 'income' ? 1.0 : 0.0,              // 6 income flag
            $tx->type === 'expense' ? 1.0 : 0.0,             // 7 expense flag
            $occurredAt->dayOfWeekIso,                       // 8 weekday (1-7)
            $occurredAt->month,                              // 9 month (1-12)
            $occurredAt->day,                                // 10 day of month
            $this->isWeekend($occurredAt) ? 1.0 : 0.0,       // 11 weekend flag
            $this->isFlexibleCategory((string) $tx->category) ? 1.0 : 0.0, // 12 wants vs needs hint
        ];

        return array_map(fn ($v) => (float) $v, $features);
    }

    /**
     * Build a fixed-length (12) feature vector for goal analysis.
     */
    public function buildGoalFeatures(Goal $goal): array
    {
        $deadline = $goal->deadline instanceof CarbonInterface ? $goal->deadline : null;
        $today = now();
        $daysRemaining = $deadline ? max(0, $today->diffInDays($deadline, false)) : 0;
        $target = max(0.01, (float) $goal->target_amount);
        $current = max(0.0, (float) $goal->current_amount);
        $remaining = max(0.0, $target - $current);
        $progress = min(1.0, $current / $target);
        $dailyNeed = $daysRemaining > 0 ? $remaining / $daysRemaining : $remaining;

        $features = [
            $target,                               // 1 target amount
            $current,                              // 2 current saved
            $remaining,                            // 3 remaining amount
            $progress,                             // 4 progress ratio
            $daysRemaining,                        // 5 days remaining
            $deadline?->month ?? 0,                // 6 deadline month
            $deadline?->day ?? 0,                  // 7 deadline day
            $dailyNeed,                            // 8 required per day
            $this->isOverdue($deadline) ? 1.0 : 0.0, // 9 overdue flag
            $this->isNearDeadline($daysRemaining) ? 1.0 : 0.0, // 10 near deadline
            $this->isCompleted($progress) ? 1.0 : 0.0, // 11 completed flag
            $this->hasLargeGap($target, $remaining) ? 1.0 : 0.0, // 12 gap risk
        ];

        return array_map(fn ($v) => (float) $v, $features);
    }

    private function categoryHash(string $category): float
    {
        // Use sprintf to ensure unsigned integer representation on 32-bit systems
        $hash = sprintf('%u', crc32(mb_strtolower($category)));
        // Normalize to [0, 1] range (0 to 4294967295)
        return (float) $hash / 4294967295.0;
    }

    private function isWeekend(CarbonInterface $date): bool
    {
        return in_array($date->dayOfWeekIso, [6, 7], true);
    }

    private function isFlexibleCategory(string $category): bool
    {
        $flexible = ['ترفيه', 'مطاعم', 'تسوق', 'سفر', 'هدايا'];
        $needle = mb_strtolower($category);
        foreach ($flexible as $item) {
            if (str_contains($needle, mb_strtolower($item))) {
                return true;
            }
        }
        return false;
    }

    private function isOverdue(?CarbonInterface $deadline): bool
    {
        return $deadline ? now()->greaterThan($deadline) : false;
    }

    private function isNearDeadline(int $daysRemaining): bool
    {
        return $daysRemaining > 0 && $daysRemaining <= 7;
    }

    private function isCompleted(float $progress): bool
    {
        return $progress >= 0.999;
    }

    private function hasLargeGap(float $target, float $remaining): bool
    {
        return $target > 0 && ($remaining / $target) >= 0.5;
    }
}
