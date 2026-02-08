<?php

declare(strict_types=1);

namespace App\Services;

class AdaptiveLearningService
{
    public function analyzeResponses(array $responses): array
    {
        $topicErrors = [];
        $totalTime = 0;

        foreach ($responses as $response) {
            $totalTime += $response['time_taken'];
            if (!$response['is_correct']) {
                $topic = $response['topic'] ?? 'general';
                $topicErrors[$topic] = ($topicErrors[$topic] ?? 0) + 1;
            }
        }

        $weakTopics = $this->rankWeakTopics($topicErrors);

        return [
            'weak_topics' => $weakTopics,
            'average_time' => $responses === [] ? 0 : round($totalTime / count($responses), 2),
            'recommended_difficulty' => $this->recommendDifficulty($responses),
            'drill_plan' => $this->buildDrillPlan($weakTopics),
        ];
    }

    public function recommendDifficulty(array $responses): int
    {
        if ($responses === []) {
            return 1;
        }

        $correct = array_filter($responses, static fn ($response) => $response['is_correct']);
        $accuracy = count($correct) / count($responses);

        if ($accuracy > 0.85) {
            return 3;
        }

        if ($accuracy > 0.6) {
            return 2;
        }

        return 1;
    }

    private function rankWeakTopics(array $topicErrors): array
    {
        arsort($topicErrors);

        $weakTopics = [];
        foreach ($topicErrors as $topic => $count) {
            $weakTopics[] = [
                'topic' => $topic,
                'mistakes' => $count,
            ];
        }

        return $weakTopics;
    }

    private function buildDrillPlan(array $weakTopics): array
    {
        $drills = [];
        foreach ($weakTopics as $weakTopic) {
            $drills[] = [
                'topic' => $weakTopic['topic'],
                'target_questions' => max(5, $weakTopic['mistakes'] * 2),
                'focus' => 'timed_practice',
            ];
        }

        return $drills;
    }
}
