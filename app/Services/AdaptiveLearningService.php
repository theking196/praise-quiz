<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Contestant;
use App\Models\PerformanceAnalytics;

class AdaptiveLearningService
{
    /**
     * Analyze responses to determine weak topics, drills, and progression signals.
     */
    public function analyzeResponses(array $responses, ?PerformanceAnalytics $analytics = null): array
    {
        $topicErrors = [];
        $totalTime = 0;

        foreach ($responses as $response) {
            $totalTime += $response['time_taken'] ?? 0;
            if (!($response['is_correct'] ?? false)) {
                $topic = $response['topic'] ?? 'general';
                $topicErrors[$topic] = ($topicErrors[$topic] ?? 0) + 1;
            }
        }

        $weakTopics = $this->rankWeakTopics($topicErrors);
        $averageTime = $responses === [] ? 0 : round($totalTime / count($responses), 2);
        $learningPatterns = $this->buildLearningPatterns($responses);

        return [
            'weak_topics' => $weakTopics,
            'average_time' => $averageTime,
            'recommended_difficulty' => $this->recommendDifficulty($responses),
            'drill_plan' => $this->buildDrillPlan($weakTopics),
            'badges' => $this->updateBadges($analytics, $responses),
            'stage_progress' => $this->recommendStage($analytics, $responses),
            'learning_patterns' => $learningPatterns,
        ];
    }

    public function recommendDifficulty(array $responses): int
    {
        if ($responses === []) {
            return 1;
        }

        $correct = array_filter($responses, static fn ($response) => ($response['is_correct'] ?? false));
        $accuracy = count($correct) / count($responses);

        if ($accuracy > 0.85) {
            return 3;
        }

        if ($accuracy > 0.6) {
            return 2;
        }

        return 1;
    }

    public function updateContestantProfile(Contestant $contestant, array $analysis): Contestant
    {
        $contestant->difficulty_level = $analysis['recommended_difficulty'] ?? $contestant->difficulty_level;
        $contestant->stage_reached = $analysis['stage_progress']['stage'] ?? $contestant->stage_reached;
        $contestant->save();

        return $contestant;
    }

    /**
     * Persist a snapshot of performance analytics for dashboards and history.
     */
    public function persistAnalytics(Contestant $contestant, array $analysis, int $scoreDelta = 0): PerformanceAnalytics
    {
        $analytics = PerformanceAnalytics::query()
            ->where('contestant_id', $contestant->id)
            ->latest('id')
            ->first();

        $payload = [
            'contestant_id' => $contestant->id,
            'total_score' => ($analytics?->total_score ?? 0) + $scoreDelta,
            'average_time' => $analysis['average_time'] ?? 0,
            'weak_topics' => $analysis['weak_topics'] ?? [],
            'learning_patterns' => $analysis['learning_patterns'] ?? [],
            'badges_earned' => $analysis['badges'] ?? [],
            'stage_reached' => $analysis['stage_progress']['stage'] ?? $analytics?->stage_reached,
            'updated_at' => now(),
        ];

        return PerformanceAnalytics::query()->create($payload);
    }

    private function buildLearningPatterns(array $responses): array
    {
        $patterns = [
            'fast_responder' => 0,
            'slow_responder' => 0,
        ];

        foreach ($responses as $response) {
            $time = $response['time_taken'] ?? 0;
            if ($time <= 10) {
                $patterns['fast_responder']++;
            } elseif ($time >= 30) {
                $patterns['slow_responder']++;
            }
        }

        return $patterns;
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

    private function updateBadges(?PerformanceAnalytics $analytics, array $responses): array
    {
        $existing = $analytics?->badges_earned ?? [];
        $correct = array_filter($responses, static fn ($response) => ($response['is_correct'] ?? false));

        if (count($correct) >= 5 && !in_array('consistent_score', $existing, true)) {
            $existing[] = 'consistent_score';
        }

        return $existing;
    }

    private function recommendStage(?PerformanceAnalytics $analytics, array $responses): array
    {
        $current = $analytics?->stage_reached ?? 'starter';
        $correct = array_filter($responses, static fn ($response) => ($response['is_correct'] ?? false));

        $next = $current;
        if (count($correct) >= 8) {
            $next = 'advanced';
        } elseif (count($correct) >= 4) {
            $next = 'intermediate';
        }

        return [
            'stage' => $next,
            'previous' => $current,
        ];
    }
}
