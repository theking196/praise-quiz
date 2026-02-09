<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('question_sets', static function (Blueprint $table): void {
            // Bundled question sets for competition sessions and drills.
            $table->id();
            $table->foreignId('competition_id')->constrained('competitions');
            $table->foreignId('category_id')->constrained('categories');
            $table->foreignId('age_group_id')->constrained('age_groups');
            $table->string('name', 150);
            $table->timestamps();

            $table->index(['competition_id', 'category_id', 'age_group_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('question_sets');
    }
};
