<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AiSetting;
use App\Models\Contestant;
use App\Models\ContestantResponse;
use App\Models\Question;

class AiQuestionGenerator
{
    public function __construct(private AiProviderService $aiProviderService)
    {
    }

    /**
     * Generate a mixed question list for a contestant profile.
     */
    public function generate(Contestant $contestant, int $count = 10): array
    {
        $settings = AiSetting::query()->latest('id')->first();
        $mixConfig = $this->resolveMixConfig($settings);

        $missedQuestions = ContestantResponse::query()
            ->where('contestant_id', $contestant->id)
            ->where('is_correct', false)
            ->with('question')
            ->latest('id')
            ->limit($count)
            ->get()
            ->pluck('question')
            ->filter()
            ->all();

        $correctQuestions = ContestantResponse::query()
            ->where('contestant_id', $contestant->id)
            ->where('is_correct', true)
            ->with('question')
            ->latest('id')
            ->limit($count)
            ->get()
            ->pluck('question')
            ->filter()
            ->all();

        $aiQuestions = $this->buildAiQuestions($contestant, $count);
        $mixed = $this->mixQuestions($aiQuestions, $missedQuestions, $correctQuestions, $mixConfig, $count);

        return [
            'generated_at' => now()->toISOString(),
            'contestant_profile' => [
                'contestant_id' => $contestant->id,
                'category' => $contestant->category->code,
                'age_group' => $contestant->ageGroup->name,
                'difficulty' => $contestant->difficulty_level,
            ],
            'mix_config' => $mixConfig,
            'questions' => $mixed,
        ];
    }

    public function buildAiQuestions(Contestant $contestant, int $count, ?int $difficulty = null): array
    {
        $difficultyLevel = $difficulty ?? $contestant->difficulty_level;
        $questions = [];
        for ($i = 1; $i <= $count; $i++) {
            $questions[] = $this->buildQuestion([
                'category' => $contestant->category->code,
                'age_group' => $contestant->ageGroup->name,
                'difficulty' => $difficultyLevel,
            ], $i);
        }

        return $questions;
    }

    /**
     * Generate AI-only questions with avoid list and seed examples.
     */
    public function generateNewQuestions(
        Contestant $contestant,
        int $count,
        int $difficulty,
        array $avoidIds,
        array $seedQuestions
    ): array {
        if ($count <= 0) {
            return [];
        }

        $prompt = sprintf(
            'Generate %d %s questions for age group %s at difficulty %d. Avoid question IDs: %s.',
            $count,
            $contestant->category->code,
            $contestant->ageGroup->name,
            $difficulty,
            implode(',', $avoidIds)
        );

        $seed = array_map(static function (Question $question) {
            return [
                'content' => $question->content,
                'type' => $question->type,
                'lesson_reference' => $question->lesson_reference,
                'topic' => $question->topic,
            ];
        }, $seedQuestions);

        $this->aiProviderService->generatePrompt($prompt, [
            'seed_examples' => $seed,
        ]);

        $questions = [];
        for ($i = 1; $i <= $count; $i++) {
            $questions[] = $this->buildQuestion([
                'category' => $contestant->category->code,
                'age_group' => $contestant->ageGroup->name,
                'difficulty' => $difficulty,
            ], $i);
        }

        return $questions;
    }

    public function mixQuestions(
        array $aiQuestions,
        array $missedQuestions,
        array $correctQuestions,
        array $mixConfig,
        int $count
    ): array {
        if ($missedQuestions === [] && $correctQuestions === []) {
            return array_slice($aiQuestions, 0, $count);
        }

        $newTarget = (int) round($count * ($mixConfig['mix_new_percentage'] / 100));
        $missedTarget = (int) round($count * ($mixConfig['mix_missed_percentage'] / 100));
        $oldTarget = $count - $newTarget - $missedTarget;

        $aiSlice = array_slice($aiQuestions, 0, $newTarget);
        $missedSlice = array_slice($missedQuestions, 0, $missedTarget);
        $oldSlice = array_slice($correctQuestions, 0, $oldTarget);

        $mixed = array_merge($aiSlice, $missedSlice, $oldSlice);

        if (count($mixed) < $count) {
            $remaining = $count - count($mixed);
            $mixed = array_merge($mixed, array_slice($aiQuestions, $newTarget, $remaining));
        }

        return $this->normalizeQuestions($mixed);
    }

    private function normalizeQuestions(array $questions): array
    {
        return array_values(array_map(static function ($question) {
            if ($question instanceof Question) {
                return [
                    'id' => $question->id,
                    'content' => $question->content,
                    'type' => $question->type,
                    'options' => $question->options,
                    'correct_answer' => $question->correct_answer,
                    'lesson_reference' => $question->lesson_reference,
                    'topic' => $question->topic,
                ];
            }

            return $question;
        }, $questions));
    }

    private function resolveMixConfig(?AiSetting $settings): array
    {
        return [
            'mix_new_percentage' => $settings?->mix_new_percentage ?? 50,
            'mix_missed_percentage' => $settings?->mix_missed_percentage ?? 30,
            'mix_old_percentage' => $settings?->mix_old_percentage ?? 20,
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
            'topic' => $seed['category'] === 'bible_quiz' ? 'leadership' : null,
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
