<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::view('/', 'dashboard');
Route::view('/cbt-session', 'cbt-session');
Route::view('/performance', 'performance');
Route::view('/teacher-dashboard', 'teacher-dashboard');
Route::view('/student-analytics', 'student-analytics');
Route::view('/leaderboard', 'leaderboard');
