<?php

declare(strict_types=1);

namespace App\Services;

class AiQuestionGenerator
{
    public function generate(array $contestantProfile, int $count = 10): array
    {
        $category = $contestantProfile['category_code'] ?? 'bible_quiz';
        $ageGroup = $contestantProfile['age_group'] ?? '9-12';
        $difficulty = $contestantProfile['difficulty_level'] ?? 1;

        $questionSeed = [
            'category' => $category,
            'age_group' => $ageGroup,
            'difficulty' => $difficulty,
        ];

        $questions = [];
        for ($i = 1; $i <= $count; $i++) {
            $questions[] = $this->buildQuestion($questionSeed, $i);
        }

        return [
            'generated_at' => date('c'),
            'contestant_profile' => $questionSeed,
            'questions' => $questions,
        ];
    }

    private function buildQuestion(array $seed, int $index): array
    {
        $typeMap = [
            'spelling_bee' => 'audio',
            'bible_quiz' => 'mcq',
            'essay' => 'essay',
            'debate' => 'debate',
            'draw_your_sword' => 'speed_search',
        ];

        $type = $typeMap[$seed['category']] ?? 'mcq';
        $prompt = sprintf(
            'Create a %s question for age group %s, difficulty %d.',
            $seed['category'],
            $seed['age_group'],
            $seed['difficulty']
        );

        return [
            'id' => sprintf('%s-%d', $seed['category'], $index),
            'content' => $this->templateContent($seed, $index),
            'type' => $type,
            'options' => $this->templateOptions($seed['category']),
            'correct_answer' => $this->templateAnswer($seed['category']),
            'lesson_reference' => $seed['category'] === 'bible_quiz' ? 'Luke 15:11-32' : null,
            'prompt_used' => $prompt,
        ];
    }

    private function templateContent(array $seed, int $index): string
    {
        if ($seed['category'] === 'spelling_bee') {
            return 'Spell the biblical name: "Jephthah".';
        }

        if ($seed['category'] === 'draw_your_sword') {
            return 'Find John 3:16 and read it aloud. Press submit when complete.';
        }

        if ($seed['category'] === 'essay') {
            return 'Discuss forgiveness using the parable of the prodigal son.';
        }

        if ($seed['category'] === 'debate') {
            return 'Debate: Faith without works is dead. Provide scriptural evidence.';
        }

        return sprintf('Question %d: Who led Israel after Moses?', $index);
    }

    private function templateOptions(string $category): ?array
    {
        if ($category !== 'bible_quiz') {
            return null;
        }

        return [
            'Joshua',
            'David',
            'Solomon',
            'Samuel',
        ];
    }

    private function templateAnswer(string $category): ?string
    {
        if ($category === 'essay' || $category === 'debate') {
            return null;
        }

        if ($category === 'draw_your_sword') {
            return 'John 3:16';
        }

        if ($category === 'spelling_bee') {
            return 'Jephthah';
        }

        return 'Joshua';
    }
}
