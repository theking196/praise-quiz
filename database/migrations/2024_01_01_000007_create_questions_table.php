<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('questions', static function (Blueprint $table): void {
            // Question bank with moderation fields for AI-generated content.
            $table->id();
            $table->foreignId('category_id')->constrained('categories');
            $table->foreignId('age_group_id')->constrained('age_groups');
            $table->text('content');
            $table->enum('type', ['mcq', 'fill_in', 'typed', 'audio', 'essay', 'debate', 'speed_search']);
            $table->json('options')->nullable();
            $table->text('correct_answer')->nullable();
            $table->string('lesson_reference', 200)->nullable();
            $table->unsignedTinyInteger('difficulty');
            $table->string('created_by', 120);
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamps();

            $table->index(['category_id', 'age_group_id', 'difficulty']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
