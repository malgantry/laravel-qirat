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

    public function generateBatch($goals): array
    {
        $results = [];
        $misses = collect();

        foreach ($goals as $goal) {
             $key = $this->getCacheKey($goal);
             if (Cache::has($key)) {
                 $results[$goal->id] = Cache::get($key);
             } else {
                 $misses->push($goal);
             }
        }

        if ($misses->isNotEmpty()) {
            $batchResponses = [];

            if (Config::get('ai.enabled')) {
                try {
                    $batchResponses = $this->ai->analyzeGoalsBatch($misses);
                } catch (\Throwable $e) {
                    // ignore
                }
            }
            
            foreach ($misses as $goal) {
                $rawItems = $batchResponses[$goal->id] ?? [];
                // Merge with baseline
                $baseline = $this->getBaseline($goal);
                
                // Map AI items
                $mapped = [];
                $userName = optional(auth()->user())->name ?? '';
                if (!empty($rawItems)) {
                        $aiMapped = array_map(fn ($f) => $this->mapAiFeedback($f, $userName), $rawItems);
                        $mapped = array_values(array_filter($aiMapped));
                }
                
                $final = array_merge($mapped, $baseline);

                // Sort by priority DESC so import alerts/success show first
                usort($final, fn($a, $b) => ($b['priority'] ?? 0) <=> ($a['priority'] ?? 0));
                
                Cache::put($this->getCacheKey($goal), $final, 1440);
                $results[$goal->id] = $final;
            }
        }
        
        return $results;
    }

    private function getCacheKey(Goal $goal): string
    {
        $userId = (string) (optional(auth()->user())->id ?? 'default');
        $stamp = optional($goal->updated_at ?? $goal->created_at)->toIso8601String() ?? '';

        $finger = implode(':', [
            (string) $goal->id,
            $userId,
            $stamp,
            'v5'
        ]);

        return 'insights:goal:' . md5($finger);
    }

    private function getBaseline(Goal $goal): array
    {
        $items = [];
        $target = (float) $goal->target_amount;
        $current = (float) $goal->current_amount;
        $progress = $target > 0 ? ($current / $target) : 0.0;
        $deadline = $goal->deadline ? Carbon::parse($goal->deadline) : null;
        $today = Carbon::today();
        $daysRemaining = $deadline ? max(0, $today->diffInDays($deadline, false)) : 0;
        $remainingAmount = max(0.0, $target - $current);
        $requiredPerDay = $daysRemaining > 0 ? ($remainingAmount / $daysRemaining) : $remainingAmount;
        $userName = optional(auth()->user())->name ?? '';

        // Progress slower than timeline
        if ($deadline && $target > 0) {
            $totalDays = max(1, Carbon::parse($goal->created_at ?? $today)->diffInDays($deadline, false));
            $elapsedDays = max(0, $totalDays - $daysRemaining);
            $expectedProgress = $elapsedDays / $totalDays; // 0..1

            if ($progress + 0.15 < $expectedProgress && $daysRemaining > 7) {
                $items[] = $this->make('warning', 9, 'slowGoalProgress', [
                    'goal' => $goal->name,
                    'needed_daily' => number_format($requiredPerDay, 2),
                ], __('goalSlowProgress', [
                    'goal' => $goal->name,
                    'needed_daily' => number_format($requiredPerDay, 2),
                ]));
            }
        }

        if ($deadline && $daysRemaining <= 7 && $progress < 1.0) {
            $items[] = $this->make('warning', 12, 'urgentDeadlineAlert', 
                ['days' => $daysRemaining, 'name' => $userName, 'goal' => $goal->name],
                __('goalUrgentDeadline', [
                    'goal' => $goal->name,
                    'days' => $daysRemaining,
                ]));
        }
        if($progress >= 1.0) {
             $items[] = $this->make('success', 10, 'goalCompleted', ['name' => $userName], __('goalCompletedMessage', [
                'name' => $userName,
                'goal' => $goal->name,
             ]));
        }
        elseif ($progress >= 0.8) {
             $items[] = $this->make('success', 8, 'goalNearCompletion', ['percent' => round($progress * 100), 'goal' => $goal->name], __('goalNearCompletionMessage', [
                'percent' => round($progress * 100),
                'goal' => $goal->name,
             ]));
        }
        return $items;
    }

    /**
     * @return array<string, mixed>[] List of feedback items
     */
    public function generateFor(Goal $goal): array
    {
         return $this->generateBatch(collect([$goal]))[$goal->id] ?? [];
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
            if (str_contains($lower, 'تهانينا') || str_contains($lower, 'congrats')) $key = 'greatAchievement';
            elseif (str_contains($lower, 'باقي أيام') || str_contains($lower, 'days left')) $key = 'urgentAlert';
            elseif (str_contains($lower, 'رائع') || str_contains($lower, 'great')) $key = 'keepGoingDaily';
            elseif (str_contains($lower, 'أحسنت') || str_contains($lower, 'well done')) $key = 'keepGoing';
            elseif (str_contains($lower, 'منتصف') || str_contains($lower, 'half')) $key = 'halfWay';
            else $key = 'smartAdvice';
        }

        $vars = $item['vars'] ?? [];
        $vars['name'] = $userName;
        if (isset($item['amount'])) $vars['amount'] = number_format($item['amount'], 0);
        if (isset($item['days'])) $vars['days'] = $item['days'];
        if (isset($item['percent'])) $vars['percent'] = $item['percent'];

        $message = $item['message'] ?? __($key, $vars);

        $action = $item['action_type'] ?? null;
        $id = $item['id'] ?? null;
        
        $res = $this->make($type, $priority, $key, $vars, $message, $action);
        if ($id) { $res['id'] = (string) $id; }
        return $res;
    }
}
