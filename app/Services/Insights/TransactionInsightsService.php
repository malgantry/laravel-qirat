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
     * @return array<string, mixed>|null Single feedback item or null
     */
    public function generateFor(Transaction $tx): ?array
    {
        $isIncome = $tx->type === 'income';
        $amount = (float) $tx->amount;

        $finger = implode(':', [(string)$tx->id, $isIncome ? '1' : '0', (string)$amount]);
        $cacheKey = 'insights:tx:' . md5($finger);
        return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($isIncome, $amount, $tx) {

        // Rule-based: flag very large expenses
        if (!$isIncome && $amount >= 500.0) {
            $baseline = $this->make('warning', 7, 'مصروف كبير؛ راجع الميزانية لتفادي التجاوز.');
        } else {
            $baseline = null;
        }

        // Try AI: features [type(0/1), amount]
        if (Config::get('ai.enabled')) {
            try {
                $features = [$isIncome ? 1.0 : 0.0, $amount];
                $result = $this->ai->predict($features);
                $class = $result['top_class'] ?? null;
                if ($class !== null) {
                    $mapped = $this->mapClassToFeedback((int) $class, $isIncome, $amount);
                    return $mapped ?? $baseline;
                }
            } catch (\Throwable $e) {
                // ignore, return baseline
            }
        }

        return $baseline;
        });
    }

    private function make(string $type, int $priority, string $message, ?string $action = null): array
    {
        return compact('type', 'priority', 'message', 'action');
    }

    private function mapClassToFeedback(int $class, bool $isIncome, float $amount): ?array
    {
        // Simplified mapping; tailor to your model
        return match ($class) {
            0 => $isIncome
                ? $this->make('success', 5, 'دخل جيد؛ فكّر في تحويل جزء للادخار.')
                : $this->make('info', 5, 'مصروف اعتيادي؛ استمر في متابعة الميزانية.'),
            1 => $this->make('warning', 7, 'مصروف مرتفع نسبيًا؛ يُنصح بتقليل الإنفاق في هذه الفئة.'),
            2 => $this->make('info', 6, 'توصية: راجع الاشتراكات أو العروض لتقليل المصاريف.'),
            default => null,
        };
    }
}
