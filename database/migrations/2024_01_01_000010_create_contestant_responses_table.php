<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contestant_responses', static function (Blueprint $table): void {
            // Captures each contestant response for scoring and analytics.
            $table->id();
            $table->foreignId('contestant_id')->constrained('contestants');
            $table->foreignId('question_id')->constrained('questions');
            $table->text('response');
            $table->boolean('is_correct')->default(false);
            $table->decimal('time_taken', 8, 2)->default(0);
            $table->timestamp('created_at')->nullable();

            $table->index(['contestant_id', 'question_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contestant_responses');
    }
};
