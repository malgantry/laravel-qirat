<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class AiClient
{
    private string $baseUrl;
    private int $timeout;

    public function __construct(private readonly FeatureBuilder $features)
    {
        $this->baseUrl = rtrim(config('ai.base_url'), '/');
        $this->timeout = (int) config('ai.timeout', 10);
    }

    /**
     * Analyze a Goal using the external AI service.
     * @return array List of feedback items (type, message, action_type, priority, score, id)
     */
    public function analyzeGoal(\App\Models\Goal $goal, ?string $userId = null): array
    {
        $headers = [];
        $apiKey = config('ai.api_key');
        if (!empty($apiKey)) {
            $headers['X-API-KEY'] = $apiKey;
        }

        $payload = [
            'id' => (string) $goal->id,
            'user_id' => (string) ($userId ?? (optional(auth()->user())->id ?? 'default')),
            'title' => (string) $goal->name,
            'target_amount' => (float) $goal->target_amount,
            'current_amount' => (float) $goal->current_amount,
            'deadline' => optional($goal->deadline)->toDateString(),
            'created_at' => optional($goal->created_at)->toIso8601String(),
            'updated_at' => optional($goal->updated_at)->toIso8601String(),
            'features' => $this->features->buildGoalFeatures($goal),
        ];

        try {
            $resp = Http::withOptions([
                'connect_timeout' => min(5, max(1, (int) config('ai.connect_timeout', 3))),
                'timeout' => max(1, $this->timeout),
            ])->acceptJson()
                ->withHeaders($headers)
                ->post("{$this->baseUrl}/analyze/goal", $payload);

            if ($resp->failed()) {
                $message = $resp->json('detail') ?? $resp->body();
                Log::warning('AI analyzeGoal failed', ['url' => "{$this->baseUrl}/analyze/goal", 'status' => $resp->status(), 'body' => substr($resp->body(), 0, 200)]);
                throw new RuntimeException('AI service error (goal): ' . $message);
            }
            return $resp->json('feedbacks') ?? [];
        } catch (\Throwable $e) {
            Log::error('AI analyzeGoal exception: ' . $e->getMessage(), ['payload' => $payload]);
            // On error, return empty array (don't break page rendering). Caller can handle empty feedbacks.
            return [];
        }
    }

    /**
     * Analyze a Transaction using the external AI service.
     * @return array List of feedback items
     */
    public function analyzeTransaction(\App\Models\Transaction $tx, ?string $userId = null, ?float $budgetLimit = null): array
    {
        $headers = [];
        $apiKey = config('ai.api_key');
        if (!empty($apiKey)) {
            $headers['X-API-KEY'] = $apiKey;
        }

        $payload = [
            'id' => (string) $tx->id,
            'user_id' => (string) ($userId ?? (optional(auth()->user())->id ?? 'default')),
            'amount' => (float) $tx->amount,
            'category' => (string) $tx->category,
            'type' => (string) $tx->type,
            'description' => (string) ($tx->note ?? ''),
            'date' => optional($tx->occurred_at)->toDateString(),
            'budget_limit' => $budgetLimit !== null ? (float) $budgetLimit : null,
            'features' => $this->features->buildTransactionFeatures($tx, $budgetLimit),
        ];

        try {
            $resp = Http::withOptions([
                'connect_timeout' => min(5, max(1, (int) config('ai.connect_timeout', 3))),
                'timeout' => max(1, $this->timeout),
            ])->acceptJson()
                ->withHeaders($headers)
                ->post("{$this->baseUrl}/analyze/transaction", $payload);

            if ($resp->failed()) {
                $message = $resp->json('detail') ?? $resp->body();
                Log::warning('AI analyzeTransaction failed', ['url' => "{$this->baseUrl}/analyze/transaction", 'status' => $resp->status(), 'body' => substr($resp->body(), 0, 200)]);
                throw new RuntimeException('AI service error (transaction): ' . $message);
            }
            $json = $resp->json('feedbacks') ?? [];
            Log::debug('AI Transaction response:', ['count' => count($json), 'first' => $json[0] ?? null]);
            return $json;
        } catch (\Throwable $e) {
            Log::error('AI analyzeTransaction exception: ' . $e->getMessage(), ['payload' => $payload]);
            return [];
        }
    }

    /**
     * Analyze multiple Transactions in one batch request.
     * @param \Illuminate\Support\Collection $transactions
     * @return array<string, array> Keyed by Transaction ID
     */
    public function analyzeTransactionsBatch($transactions): array
    {
        if ($transactions->isEmpty()) return [];
        
        $userId = (string) (auth()->id() ?? 'default');
        $headers = [];
        $apiKey = config('ai.api_key');
        if (!empty($apiKey)) $headers['X-API-KEY'] = $apiKey;

        $batchList = $transactions->map(function ($tx) use ($userId) {
             return [
                'id' => (string) $tx->id,
                'user_id' => $userId,
                'amount' => (float) $tx->amount,
                'category' => (string) $tx->category,
                'type' => (string) $tx->type,
                'description' => (string) ($tx->note ?? ''),
                'date' => optional($tx->occurred_at)->toDateString(),
                'budget_limit' => null, 
                'features' => $this->features->buildTransactionFeatures($tx, null),
            ];
        })->values()->toArray();

        try {
            // Increase timeout for batch operations (e.g. 60s if base is 10s)
            $resp = Http::withOptions(['timeout' => max(30, $this->timeout * 6)])
                ->acceptJson()
                ->withHeaders($headers)
                ->post("{$this->baseUrl}/analyze/transactions/batch", ['transactions' => $batchList]);

            if ($resp->successful()) {
                return $resp->json('results') ?? [];
            }
        } catch (\Throwable $e) {
            Log::error('AI Batch Tx Exception: ' . $e->getMessage());
        }
        return [];
    }

    /**
     * Analyze multiple Goals in one batch request.
     * @param \Illuminate\Support\Collection $goals
     * @return array<string, array> Keyed by Goal ID
     */
    public function analyzeGoalsBatch($goals): array
    {
        if ($goals->isEmpty()) return [];

        $userId = (string) (auth()->id() ?? 'default');
        $headers = [];
        $apiKey = config('ai.api_key');
        if (!empty($apiKey)) $headers['X-API-KEY'] = $apiKey;

        $batchList = $goals->map(function ($goal) use ($userId) {
            return [
                'id' => (string) $goal->id,
                'user_id' => $userId,
                'title' => (string) $goal->name,
                'target_amount' => (float) $goal->target_amount,
                'current_amount' => (float) $goal->current_amount,
                'deadline' => optional($goal->deadline)->toDateString(),
                'created_at' => optional($goal->created_at)->toIso8601String(),
                'updated_at' => optional($goal->updated_at)->toIso8601String(),
                'features' => $this->features->buildGoalFeatures($goal),
            ];
        })->values()->toArray();

        try {
            // Increase timeout for batch operations
            $resp = Http::withOptions(['timeout' => max(30, $this->timeout * 6)])
                ->acceptJson()
                ->withHeaders($headers)
                ->post("{$this->baseUrl}/analyze/goals/batch", ['goals' => $batchList]);

            if ($resp->successful()) {
                return $resp->json('results') ?? [];
            }
        } catch (\Throwable $e) {
             Log::error('AI Batch Goal Exception: ' . $e->getMessage());
        }
        return [];
    }

    /**
     * Forward user feedback about AI suggestions for learning.
     */
    public function submitFeedback(string $userId, string $feedbackId, string $action, ?string $feedbackType = null): array
    {
        $headers = [];
        $apiKey = config('ai.api_key');
        if (!empty($apiKey)) {
            $headers['X-API-KEY'] = $apiKey;
        }

        try {
            $payload = [
                'user_id' => $userId,
                'feedback_id' => $feedbackId,
                'action' => $action,
            ];
            
            if ($feedbackType) {
                $payload['feedback_type'] = $feedbackType;
            }

            $resp = Http::withOptions([
                'connect_timeout' => min(5, max(1, (int) config('ai.connect_timeout', 3))),
                'timeout' => max(1, $this->timeout),
            ])->acceptJson()
                ->withHeaders($headers)
                ->post("{$this->baseUrl}/feedback", $payload);

            if ($resp->failed()) {
                $message = $resp->json('detail') ?? $resp->body();
                Log::warning('AI submitFeedback failed', ['url' => "{$this->baseUrl}/feedback", 'status' => $resp->status(), 'body' => substr($resp->body(), 0, 200)]);
                throw new RuntimeException('AI service error (feedback): ' . $message);
            }
            return $resp->json() ?? ['status' => 'ok'];
        } catch (\Throwable $e) {
            Log::error('AI submitFeedback exception: ' . $e->getMessage(), ['feedback_id' => $feedbackId, 'action' => $action]);
            return ['status' => 'error', 'message' => 'unavailable'];
        }
    }
}