<?php

namespace App\Jobs;

use App\Models\AiSuggestion;
use App\Models\Transaction;
use App\Services\AiClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class AnalyzeTransactionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Transaction $transaction
    ) {}

    /**
     * Execute the job.
     */
    public function handle(AiClient $aiClient): void
    {
        try {
            // Include related models to avoid N+1 if accessed in feature builder
            $this->transaction->load('categoryRef'); 

            $feedbacks = $aiClient->analyzeTransaction($this->transaction);

            foreach ($feedbacks as $feedback) {
                AiSuggestion::create([
                    'user_id' => $this->transaction->user_id,
                    'category_id' => $this->transaction->category_id, // Nullable in DB? likely needs check
                    'suggestion' => $feedback['message'],
                    'source' => 'ai_transaction_analysis',
                    'score' => $feedback['score'] ?? 1.0,
                    'metadata' => [
                        'transaction_id' => $this->transaction->id,
                        'feedback_id' => $feedback['id'] ?? null,
                        'priority' => $feedback['priority'] ?? 0,
                        'type' => $feedback['type'] ?? 'info',
                    ],
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('AnalyzeTransactionJob failed', [
                'tx_id' => $this->transaction->id, 
                'error' => $e->getMessage()
            ]);
        }
    }
}
