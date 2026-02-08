<?php

declare(strict_types=1);

namespace App\Models;

class Contestant
{
    public function __construct(
        public int $id,
        public int $userId,
        public int $ageGroupId,
        public int $categoryId,
        public int $competitionId,
        public int $difficultyLevel = 1,
        public int $currentXp = 0,
        public ?string $stageReached = null
    ) {
    }
}
