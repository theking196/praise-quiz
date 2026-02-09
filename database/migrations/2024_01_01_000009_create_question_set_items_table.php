<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('question_set_items', static function (Blueprint $table): void {
            // Sequence and scoring for each question in a set.
            $table->id();
            $table->foreignId('question_set_id')->constrained('question_sets');
            $table->foreignId('question_id')->constrained('questions');
            $table->unsignedSmallInteger('sequence_order');
            $table->unsignedSmallInteger('points');

            $table->unique(['question_set_id', 'sequence_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('question_set_items');
    }
};
