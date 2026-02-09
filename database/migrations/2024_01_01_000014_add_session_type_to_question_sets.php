<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('question_sets', static function (Blueprint $table): void {
            $table->string('session_type', 50)->default('practice')->after('name');
            $table->index('session_type');
        });
    }

    public function down(): void
    {
        Schema::table('question_sets', static function (Blueprint $table): void {
            $table->dropIndex(['session_type']);
            $table->dropColumn('session_type');
        });
    }
};
