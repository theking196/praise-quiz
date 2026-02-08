<?php

declare(strict_types=1);

namespace App\Models;

class PerformanceAnalytics
{
    public function __construct(
        public int $contestantId,
        public int $totalScore,
        public float $averageTime,
        public array $weakTopics,
        public array $badgesEarned,
        public ?string $stageReached
    ) {
    }
}
