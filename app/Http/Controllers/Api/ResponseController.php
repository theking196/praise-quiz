<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Services\AdaptiveLearningService;
use App\Services\ScoringService;

class ResponseController
{
    public function __construct(
        private ScoringService $scoringService,
        private AdaptiveLearningService $adaptiveService
    ) {
    }

    public function submit(array $response, int $difficulty): array
    {
        $scoreData = $this->scoringService->scoreResponse($response, $difficulty);
        $analytics = $this->adaptiveService->analyzeResponses([$response]);

        return [
            'score' => $scoreData,
            'analytics' => $analytics,
        ];
    }
}
