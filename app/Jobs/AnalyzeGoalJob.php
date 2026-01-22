<?php

namespace App\Jobs;

use App\Models\AiSuggestion;
use App\Models\Goal;
use App\Services\AiClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class AnalyzeGoalJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Goal $goal
    ) {}

    /**
     * Execute the job.
     */
    public function handle(AiClient $aiClient): void
    {
        try {
            $feedbacks = $aiClient->analyzeGoal($this->goal);

            foreach ($feedbacks as $feedback) {
                AiSuggestion::create([
                    'user_id' => $this->goal->user_id,
                    'suggestion' => $feedback['message'],
                    'source' => 'ai_goal_analysis',
                    'score' => $feedback['score'] ?? 1.0,
                    'metadata' => [
                        'goal_id' => $this->goal->id,
                        'feedback_id' => $feedback['id'] ?? null,
                        'priority' => $feedback['priority'] ?? 0,
                        'type' => $feedback['type'] ?? 'info',
                    ],
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('AnalyzeGoalJob failed', [
                'goal_id' => $this->goal->id, 
                'error' => $e->getMessage()
            ]);
        }
    }
}
