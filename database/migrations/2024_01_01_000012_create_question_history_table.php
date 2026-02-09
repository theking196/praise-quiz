<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('question_history', static function (Blueprint $table): void {
            // Tracks previously served questions to avoid repeats.
            $table->id();
            $table->foreignId('contestant_id')->constrained('contestants');
            $table->foreignId('question_id')->constrained('questions');
            $table->timestamp('asked_at')->useCurrent();

            $table->index(['contestant_id', 'question_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('question_history');
    }
};
