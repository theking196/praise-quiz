<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('questions', static function (Blueprint $table): void {
            $table->string('topic', 150)->nullable()->after('lesson_reference');
            $table->unsignedInteger('use_count')->default(0)->after('difficulty');
            $table->timestamp('last_used_at')->nullable()->after('use_count');

            $table->index('topic');
        });
    }

    public function down(): void
    {
        Schema::table('questions', static function (Blueprint $table): void {
            $table->dropIndex(['topic']);
            $table->dropColumn(['topic', 'use_count', 'last_used_at']);
        });
    }
};
