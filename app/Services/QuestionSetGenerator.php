<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AiSetting;
use App\Models\Contestant;
use App\Models\ContestantResponse;
use App\Models\PerformanceAnalytics;
use App\Models\Question;
use App\Models\QuestionHistory;
use App\Models\QuestionSet;
use App\Models\QuestionSetItem;
use Illuminate\Support\Collection;

class QuestionSetGenerator
{
    public function __construct(
        private AiQuestionGenerator $aiQuestionGenerator,
        private AdaptiveLearningService $adaptiveLearningService
    ) {
    }

    /**
     * Build an adaptive question set with mixed AI and historical questions.
     */
    public function generate(
        Contestant $contestant,
        int $categoryId,
        int $ageGroupId,
        int $difficulty,
        int $numberOfQuestions
    ): array {
        $settings = AiSetting::query()->latest('id')->first();
        $difficulty = $this->applyMaxDifficulty($settings, $ageGroupId, $difficulty);
        $mixConfig = [
            'mix_new_percentage' => $settings?->mix_new_percentage ?? 50,
            'mix_missed_percentage' => $settings?->mix_missed_percentage ?? 30,
            'mix_old_percentage' => $settings?->mix_old_percentage ?? 20,
        ];

        $recentHistory = QuestionHistory::query()
            ->where('contestant_id', $contestant->id)
            ->pluck('question_id')
            ->all();

        $missedQuestions = $this->fetchPastQuestions($contestant->id, false, $numberOfQuestions, $recentHistory);
        $correctQuestions = $this->fetchPastQuestions($contestant->id, true, $numberOfQuestions, $recentHistory);

        $aiQuestions = $this->aiQuestionGenerator->buildAiQuestions($contestant, $numberOfQuestions, $difficulty);
        $mixedQuestions = $this->aiQuestionGenerator->mixQuestions(
            $aiQuestions,
            $missedQuestions,
            $correctQuestions,
            $mixConfig,
            $numberOfQuestions
        );

        $questionSet = QuestionSet::query()->create([
            'competition_id' => $contestant->competition_id,
            'category_id' => $categoryId,
            'age_group_id' => $ageGroupId,
            'name' => sprintf('Adaptive Set %s', now()->format('Y-m-d H:i:s')),
        ]);

        $items = $this->persistItems($questionSet, $mixedQuestions, $difficulty);
        $this->updateHistory($contestant->id, $items);

        $recentResponses = ContestantResponse::query()
            ->where('contestant_id', $contestant->id)
            ->latest('id')
            ->limit(20)
            ->get()
            ->map(static function ($response) {
                return [
                    'is_correct' => $response->is_correct,
                    'time_taken' => $response->time_taken,
                    'topic' => $response->question?->lesson_reference ?? 'general',
                ];
            })
            ->all();

        $analytics = PerformanceAnalytics::query()
            ->where('contestant_id', $contestant->id)
            ->latest('id')
            ->first();

        $analysis = $this->adaptiveLearningService->analyzeResponses($recentResponses, $analytics);
        $this->adaptiveLearningService->persistAnalytics($contestant, $analysis);
        $this->adaptiveLearningService->updateContestantProfile($contestant, $analysis);

        return [
            'question_set' => $questionSet,
            'items' => $items,
            'mix_config' => $mixConfig,
            'analysis' => $analysis,
        ];
    }

    private function fetchPastQuestions(
        int $contestantId,
        bool $isCorrect,
        int $limit,
        array $excludedIds
    ): array {
        return ContestantResponse::query()
            ->where('contestant_id', $contestantId)
            ->where('is_correct', $isCorrect)
            ->whereNotIn('question_id', $excludedIds)
            ->with('question')
            ->latest('id')
            ->limit($limit)
            ->get()
            ->pluck('question')
            ->filter()
            ->all();
    }

    private function persistItems(QuestionSet $questionSet, array $questions, int $difficulty): Collection
    {
        $items = collect();
        foreach ($questions as $index => $question) {
            if ($question instanceof Question) {
                $questionId = $question->id;
            } else {
                $questionId = Question::query()->create([
                    'category_id' => $questionSet->category_id,
                    'age_group_id' => $questionSet->age_group_id,
                    'content' => $question['content'],
                    'type' => $question['type'],
                    'options' => $question['options'],
                    'correct_answer' => $question['correct_answer'],
                    'lesson_reference' => $question['lesson_reference'],
                    'difficulty' => $difficulty,
                    'created_by' => 'ai',
                ])->id;
            }

            $items->push(QuestionSetItem::query()->create([
                'question_set_id' => $questionSet->id,
                'question_id' => $questionId,
                'sequence_order' => $index + 1,
                'points' => 10,
            ]));
        }

        return $items;
    }

    private function updateHistory(int $contestantId, Collection $items): void
    {
        foreach ($items as $item) {
            QuestionHistory::query()->create([
                'contestant_id' => $contestantId,
                'question_id' => $item->question_id,
                'asked_at' => now(),
            ]);
        }
    }

    private function applyMaxDifficulty(?AiSetting $settings, int $ageGroupId, int $difficulty): int
    {
        $limits = $settings?->max_difficulty_by_age_group ?? [];
        $maxDifficulty = $limits[$ageGroupId] ?? null;

        if ($maxDifficulty === null) {
            return $difficulty;
        }

        return (int) min($difficulty, $maxDifficulty);
    }
}
