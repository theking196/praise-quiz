<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('performance_analytics', static function (Blueprint $table): void {
            // Aggregated analytics snapshots for dashboards and adaptive learning.
            $table->id();
            $table->foreignId('contestant_id')->constrained('contestants');
            $table->unsignedInteger('total_score')->default(0);
            $table->decimal('average_time', 8, 2)->default(0);
            $table->json('weak_topics')->nullable();
            $table->json('learning_patterns')->nullable();
            $table->json('badges_earned')->nullable();
            $table->string('stage_reached', 100)->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->index('contestant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('performance_analytics');
    }
};
