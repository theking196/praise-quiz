<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('competitions', static function (Blueprint $table): void {
            // Annual competition cycles for historical tracking.
            $table->id();
            $table->unsignedSmallInteger('year');
            $table->date('start_date');
            $table->date('end_date');
            $table->timestamps();

            $table->unique('year');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('competitions');
    }
};
