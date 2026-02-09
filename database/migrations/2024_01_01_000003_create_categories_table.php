<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', static function (Blueprint $table): void {
            // Contest categories (e.g., Bible Quiz, Spelling Bee, Debate).
            $table->id();
            $table->string('name', 100);
            $table->string('code', 50)->unique();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
