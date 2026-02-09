<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', static function (Blueprint $table): void {
            // Core user identity with role-based access control.
            $table->id();
            $table->string('name', 150);
            $table->string('email', 190)->unique();
            $table->string('password');
            $table->enum('role', ['contestant', 'teacher', 'director', 'admin']);
            $table->timestamps();

            $table->index('role');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
