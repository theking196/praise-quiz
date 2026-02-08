<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

class AnalyticsController
{
    public function dashboard(array $summary): array
    {
        return [
            'leaderboard' => $summary['leaderboard'] ?? [],
            'average_scores' => $summary['average_scores'] ?? [],
            'weak_topics' => $summary['weak_topics'] ?? [],
            'recent_question_sets' => $summary['recent_question_sets'] ?? [],
        ];
    }
}
