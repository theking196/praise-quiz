<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contestants', static function (Blueprint $table): void {
            // Contestant participation per competition, category, and age group.
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('age_group_id')->constrained('age_groups');
            $table->foreignId('category_id')->constrained('categories');
            $table->foreignId('competition_id')->constrained('competitions');
            $table->unsignedTinyInteger('difficulty_level')->default(1);
            $table->unsignedInteger('current_xp')->default(0);
            $table->string('stage_reached', 100)->nullable();
            $table->timestamps();

            $table->index(['competition_id', 'category_id', 'age_group_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contestants');
    }
};
