<?php

use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        // Backfill in chunks to avoid memory spikes
        Transaction::whereNull('category_id')
            ->orderBy('id')
            ->chunkById(500, function ($transactions) {
                foreach ($transactions as $tx) {
                    if (!$tx->category || !$tx->type) {
                        continue;
                    }
                    $cat = Category::where('name', $tx->category)
                        ->where('type', $tx->type)
                        ->first();
                    if ($cat) {
                        $tx->category_id = $cat->id;
                        // Ensure name sync (in case of canonicalization)
                        $tx->category = $cat->name;
                        $tx->save();
                    }
                }
            });
    }

    public function down(): void
    {
        // No-op: keeping backfill as one-way operation
    }
};
