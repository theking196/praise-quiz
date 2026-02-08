<?php

declare(strict_types=1);

use App\Http\Controllers\Api\AnalyticsController;
use App\Http\Controllers\Api\QuestionController;
use App\Http\Controllers\Api\ResponseController;

return [
    'GET /api/contestants/{contestantId}/questions' => [QuestionController::class, 'fetch'],
    'POST /api/contestants/{contestantId}/responses' => [ResponseController::class, 'submit'],
    'POST /api/contestants/{contestantId}/drills' => [QuestionController::class, 'drills'],
    'GET /api/analytics/dashboard' => [AnalyticsController::class, 'dashboard'],
];
