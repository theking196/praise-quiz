<?php

declare(strict_types=1);

namespace App\Models;

class Question
{
    public function __construct(
        public int $id,
        public int $categoryId,
        public int $ageGroupId,
        public string $content,
        public string $type,
        public ?array $options,
        public ?string $correctAnswer,
        public ?string $lessonReference,
        public int $difficulty,
        public string $createdBy
    ) {
    }
}
