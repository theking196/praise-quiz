<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_settings', static function (Blueprint $table): void {
            // Admin-tunable AI mixing ratios and difficulty caps.
            $table->id();
            $table->unsignedTinyInteger('mix_new_percentage')->default(50);
            $table->unsignedTinyInteger('mix_missed_percentage')->default(30);
            $table->unsignedTinyInteger('mix_old_percentage')->default(20);
            $table->json('max_difficulty_by_age_group')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_settings');
    }
};
