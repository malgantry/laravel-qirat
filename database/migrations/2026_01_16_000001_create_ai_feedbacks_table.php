<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_feedbacks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('feedback_id', 120)->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('action', 32);
            $table->string('object_type')->nullable()->index();
            $table->unsignedBigInteger('object_id')->nullable()->index();
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_feedbacks');
    }
};
