<?php

declare(strict_types=1);

namespace App\Services;

class ScoringService
{
    public function scoreResponse(array $response, int $difficulty): array
    {
        $basePoints = $response['points'] ?? 10;
        $timeTaken = $response['time_taken'] ?? 0;

        $difficultyMultiplier = 1 + ($difficulty * 0.25);
        $timeBonus = $this->calculateTimeBonus($timeTaken);

        $score = 0;
        if ($response['is_correct']) {
            $score = (int) round($basePoints * $difficultyMultiplier + $timeBonus);
        }

        return [
            'score' => $score,
            'difficulty_multiplier' => $difficultyMultiplier,
            'time_bonus' => $timeBonus,
        ];
    }

    public function scoreEssay(array $rubric): array
    {
        $content = $rubric['content'] ?? 0;
        $scripture = $rubric['scripture_application'] ?? 0;
        $structure = $rubric['structure'] ?? 0;

        $score = $content + $scripture + $structure;

        return [
            'score' => $score,
            'breakdown' => [
                'content' => $content,
                'scripture_application' => $scripture,
                'structure' => $structure,
            ],
        ];
    }

    private function calculateTimeBonus(float $timeTaken): int
    {
        if ($timeTaken <= 10) {
            return 5;
        }

        if ($timeTaken <= 20) {
            return 3;
        }

        if ($timeTaken <= 30) {
            return 1;
        }

        return 0;
    }
}
