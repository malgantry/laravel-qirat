<?php

namespace App\Observers;

use App\Models\Budget;
use App\Models\Transaction;
use App\Jobs\AnalyzeTransactionJob;
use Illuminate\Support\Carbon;

class TransactionObserver
{
    /**
     * Handle the Transaction "created" event.
     */
    public function created(Transaction $transaction): void
    {
        if ($transaction->type === 'expense') {
            $this->updateBudgetSpent($transaction, $transaction->amount);
        }
        
        // Dispatch AI Analysis
        AnalyzeTransactionJob::dispatch($transaction);
    }

    /**
     * Handle the Transaction "updated" event.
     */
    public function updated(Transaction $transaction): void
    {
        // If type changed from income to expense or vice versa, this is complex.
        // Simplified: Handle standard amount/category updates.
        
        // 1. Revert Old (if it was expense)
        if ($transaction->getOriginal('type') === 'expense') {
             $this->updateBudgetSpent($transaction, -$transaction->getOriginal('amount'), true);
        }

        // 2. Apply New (if it is expense)
        if ($transaction->type === 'expense') {
             $this->updateBudgetSpent($transaction, $transaction->amount);
        }

        // Dispatch AI Analysis
        AnalyzeTransactionJob::dispatch($transaction);
    }

    /**
     * Handle the Transaction "deleted" event.
     */
    public function deleted(Transaction $transaction): void
    {
        if ($transaction->type === 'expense') {
            $this->updateBudgetSpent($transaction, -$transaction->amount);
        }
    }

    /**
     * Helper to find and update relevant budget.
     * @param Transaction $transaction
     * @param float $amount Change amount (positive to add, negative to subtract)
     * @param bool $useOriginalAttrs Whether to use original attributes (for reverting old category/date)
     */
    private function updateBudgetSpent(Transaction $transaction, float $amount, bool $useOriginalAttrs = false): void
    {
        // Get relevant attributes (Category, Date)
        $categoryId = $useOriginalAttrs ? $transaction->getOriginal('category_id') : $transaction->category_id;
        $dateStr = $useOriginalAttrs ? $transaction->getOriginal('occurred_at') : $transaction->occurred_at;
        
        if (!$categoryId || !$dateStr) return; // Can't link to budget without category/date

        $date = Carbon::parse($dateStr);
        $userId = $transaction->user_id;

        // Find active budget for this category covering this date
        // Logic: Budget period_start <= transaction_date <= period_end
        $budgets = Budget::where('user_id', $userId)
            ->where('category_id', $categoryId)
            ->whereDate('period_start', '<=', $date)
            ->whereDate('period_end', '>=', $date)
            ->get();

        foreach ($budgets as $budget) {
            // Increment/Decrement spent_amount
            // Use current value + diff
            $newSpent = ($budget->spent_amount ?? 0) + $amount;
            
            // Safety: spent shouldn't be negative unless data was corrupt, but allow math to work
            $budget->update(['spent_amount' => $newSpent]);
            
            // Optional: Check status? If spent > limit? UI handles that.
        }
        
        // Fallback: If no category_id but 'category' string matches? 
        // Our App relies on budget->category_id link strongly.
    }
}
