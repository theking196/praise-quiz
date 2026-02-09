<?php

declare(strict_types=1);

namespace App\Services;

class ScoringService
{
    /**
     * Score CBT/quiz style responses with difficulty multiplier + time bonus.
     */
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

    /**
     * Score spelling bee responses with accuracy weighting.
     */
    public function scoreSpellingBee(array $response, int $difficulty): array
    {
        $basePoints = $response['points'] ?? 10;
        $accuracy = $response['accuracy'] ?? 1;
        $timeTaken = $response['time_taken'] ?? 0;

        $difficultyMultiplier = 1 + ($difficulty * 0.2);
        $timeBonus = $this->calculateTimeBonus($timeTaken);
        $score = (int) round($basePoints * $accuracy * $difficultyMultiplier + $timeBonus);

        return [
            'score' => $score,
            'accuracy' => $accuracy,
            'difficulty_multiplier' => $difficultyMultiplier,
            'time_bonus' => $timeBonus,
        ];
    }

    /**
     * Score recitation with accuracy minus fluency penalties.
     */
    public function scoreRecitation(array $response): array
    {
        $accuracy = $response['accuracy'] ?? 0;
        $fluencyPenalty = $response['fluency_penalty'] ?? 0;
        $score = max(0, (int) round($accuracy - $fluencyPenalty));

        return [
            'score' => $score,
            'accuracy' => $accuracy,
            'fluency_penalty' => $fluencyPenalty,
        ];
    }

    /**
     * Rubric scoring for essays.
     */
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

    /**
     * Rubric scoring for debates.
     */
    public function scoreDebate(array $rubric): array
    {
        $argument = $rubric['argument_strength'] ?? 0;
        $scripture = $rubric['scripture_application'] ?? 0;
        $delivery = $rubric['delivery'] ?? 0;

        $score = $argument + $scripture + $delivery;

        return [
            'score' => $score,
            'breakdown' => [
                'argument_strength' => $argument,
                'scripture_application' => $scripture,
                'delivery' => $delivery,
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
