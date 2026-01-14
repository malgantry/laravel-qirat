<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('budgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->decimal('limit_amount', 12, 2);
            $table->decimal('spent_amount', 12, 2)->default(0);
            $table->date('period_start');
            $table->date('period_end');
            $table->string('status')->default('active');
            $table->timestamps();

            $table->index(['user_id', 'period_start', 'period_end']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budgets');
    }
};
