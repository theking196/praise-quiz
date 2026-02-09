<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('age_groups', static function (Blueprint $table): void {
            // Defines age ranges for competition groupings.
            $table->id();
            $table->string('name', 50);
            $table->unsignedTinyInteger('min_age');
            $table->unsignedTinyInteger('max_age');

            $table->unique(['min_age', 'max_age']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('age_groups');
    }
};
