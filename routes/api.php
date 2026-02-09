<?php

declare(strict_types=1);

use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\AnalyticsController;
use App\Http\Controllers\Api\QuestionController;
use App\Http\Controllers\Api\ResponseController;

return [
    'POST /api/contestants/{contestantId}/question-set' => [QuestionController::class, 'questionSet'],
    'POST /api/contestants/{contestantId}/responses' => [ResponseController::class, 'submit'],

    'GET /api/analytics/leaderboard' => [AnalyticsController::class, 'leaderboard'],
    'GET /api/analytics/weak-topics' => [AnalyticsController::class, 'weakTopics'],
    'GET /api/analytics/average-scores' => [AnalyticsController::class, 'averageScores'],
    'GET /api/analytics/drill-recommendations' => [AnalyticsController::class, 'drillRecommendations'],
    'GET /api/analytics/recent-question-sets' => [AnalyticsController::class, 'recentQuestionSets'],
    'GET /api/analytics/export' => [AnalyticsController::class, 'exportReport'],

    'GET /api/admin/ai-settings' => [AdminController::class, 'settings'],
    'POST /api/admin/ai-settings' => [AdminController::class, 'storeSettings'],
    'PATCH /api/admin/questions/{id}/approve' => [AdminController::class, 'approveQuestion'],
];
