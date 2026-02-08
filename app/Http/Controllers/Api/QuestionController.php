<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Services\AiQuestionGenerator;
use App\Services\AdaptiveLearningService;

class QuestionController
{
    public function __construct(
        private AiQuestionGenerator $generator,
        private AdaptiveLearningService $adaptiveService
    ) {
    }

    public function fetch(array $contestantProfile, int $count = 10): array
    {
        return $this->generator->generate($contestantProfile, $count);
    }

    public function drills(array $responses): array
    {
        return $this->adaptiveService->analyzeResponses($responses);
    }
}
