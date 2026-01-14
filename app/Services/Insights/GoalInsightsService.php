<?php

namespace App\Services\Insights;

use App\Models\Goal;
use App\Services\AiClient;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

class GoalInsightsService
{
    public function __construct(private readonly AiClient $ai)
    {
    }

    /**
     * @return array<string, mixed>[] List of feedback items
     */
    public function generateFor(Goal $goal): array
    {
        // Cache key based on goal state fingerprint
        $finger = implode(':', [
            (string) $goal->id,
            (string) $goal->target_amount,
            (string) $goal->current_amount,
            (string) optional($goal->deadline)->toDateString(),
        ]);
        $cacheKey = 'insights:goal:' . md5($finger);
        return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($goal) {
            $items = [];

        // Compute simple features
        $target = (float) $goal->target_amount;
        $current = (float) $goal->current_amount;
        $progress = $target > 0 ? ($current / $target) : 0.0; // 0..1
        $today = Carbon::today();
        $deadline = $goal->deadline ? Carbon::parse($goal->deadline) : null;
        $daysRemaining = $deadline ? max(0, $today->diffInDays($deadline, false)) : 0;
        $remainingAmount = max(0.0, $target - $current);
        $requiredPerDay = $daysRemaining > 0 ? ($remainingAmount / $daysRemaining) : $remainingAmount;

        // Rule-based baseline (fallback)
        if ($deadline && $daysRemaining <= 7 && $remainingAmount > 0) {
            $items[] = $this->make(
                'warning',
                9,
                'الموعد يقترب، تبقّى أقل من 7 أيام. يُنصح بإضافة مبلغ قريباً.',
                'إضافة مبلغ'
            );
        }
        if ($remainingAmount > 0 && $requiredPerDay > max(10.0, $target * 0.02)) {
            $items[] = $this->make(
                'info',
                7,
                'المبلغ اليومي المطلوب مرتفع نسبياً، حاول زيادة الادخار أو تمديد الموعد.',
                'إضافة مبلغ'
            );
        }

        // Try AI if enabled
        if (Config::get('ai.enabled')) {
            try {
                $features = [$progress, (float) $daysRemaining, $target, $current];
                $result = $this->ai->predict($features);
                $class = $result['top_class'] ?? null;
                if ($class !== null) {
                    $mapped = $this->mapClassToFeedback((int) $class, $remainingAmount, $daysRemaining);
                    if ($mapped) $items = array_merge([$mapped], $items); // put AI on top
                }
            } catch (\Throwable $e) {
                // Ignore AI errors; baseline items already present
            }
        }

        return $items;
        });
    }

    private function make(string $type, int $priority, string $message, ?string $action = null): array
    {
        return compact('type', 'priority', 'message', 'action');
    }

    private function mapClassToFeedback(int $class, float $remainingAmount, int $daysRemaining): ?array
    {
        // Simple mapping; adjust to your model semantics
        return match ($class) {
            0 => $this->make('success', 5, 'المسار جيد، استمر في الادخار وفق خطتك.'),
            1 => $this->make('info', 6, 'توصية بزيادة الادخار بشكل طفيف هذا الأسبوع.'),
            2 => $this->make('warning', 8, 'خطر تأخير الهدف؛ يُنصح بإضافة مبلغ الآن.', 'إضافة مبلغ'),
            default => null,
        };
    }
}
