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
use Illuminate\Support\Facades\DB;

class QuestionSetGenerator
{
    public function __construct(
        private AiQuestionGenerator $aiQuestionGenerator,
        private AdaptiveLearningService $adaptiveLearningService
    ) {
    }

    /**
     * Backward-compatible generator with default session type.
     */
    public function generate(
        Contestant $contestant,
        int $categoryId,
        int $ageGroupId,
        int $difficulty,
        int $numberOfQuestions
    ): array {
        return $this->generateSession(
            $contestant->id,
            $categoryId,
            $ageGroupId,
            $difficulty,
            $numberOfQuestions,
            'practice'
        );
    }

    /**
     * Build an adaptive question set with mixed AI and historical questions.
     */
    public function generateSession(
        int $contestantId,
        int $categoryId,
        int $ageGroupId,
        int $difficulty,
        int $numberOfQuestions,
        string $sessionType
    ): array {
        $contestant = Contestant::query()->with(['category', 'ageGroup'])->findOrFail($contestantId);
        $settings = AiSetting::query()->latest('id')->first();

        $difficulty = $this->applyMaxDifficulty($settings, $ageGroupId, $difficulty);
        $mixConfig = $this->resolveMixConfig($settings);

        $recentHistory = QuestionHistory::query()
            ->where('contestant_id', $contestant->id)
            ->latest('asked_at')
            ->limit(50)
            ->pluck('question_id')
            ->all();

        $missedQuestions = $this->fetchPastQuestions($contestant->id, false, $numberOfQuestions, $recentHistory);
        $correctQuestions = $this->fetchOldCorrectQuestions($contestant->id, $numberOfQuestions, $recentHistory);

        $hasHistory = $missedQuestions !== [] || $correctQuestions !== [];
        $targets = $this->calculateTargets($numberOfQuestions, $mixConfig, $hasHistory);

        $selectedMissed = array_slice($missedQuestions, 0, $targets['missed']);
        $selectedOld = array_slice($correctQuestions, 0, $targets['old']);

        $avoidIds = array_merge(
            $recentHistory,
            array_map(static fn (Question $question) => $question->id, $selectedMissed),
            array_map(static fn (Question $question) => $question->id, $selectedOld)
        );

        $seedQuestions = array_merge($selectedMissed, $selectedOld);
        $newQuestions = $this->aiQuestionGenerator->generateNewQuestions(
            $contestant,
            $targets['new'],
            $difficulty,
            $avoidIds,
            $seedQuestions
        );

        $mixedQuestions = array_merge($newQuestions, $selectedMissed, $selectedOld);
        $mixedQuestions = $this->topUpQuestions(
            $mixedQuestions,
            $contestant,
            $numberOfQuestions,
            $difficulty,
            $avoidIds,
            $seedQuestions
        );

        $questionSet = QuestionSet::query()->create([
            'competition_id' => $contestant->competition_id,
            'category_id' => $categoryId,
            'age_group_id' => $ageGroupId,
            'name' => sprintf('Adaptive Set %s', now()->format('Y-m-d H:i:s')),
            'session_type' => $sessionType,
        ]);

        $items = $this->persistItems($questionSet, $mixedQuestions, $difficulty);
        $this->updateHistory($contestant->id, $items);
        $this->updateUsage($items);

        $recentResponses = ContestantResponse::query()
            ->where('contestant_id', $contestant->id)
            ->latest('id')
            ->limit(20)
            ->get()
            ->map(static function ($response) {
                return [
                    'is_correct' => $response->is_correct,
                    'time_taken' => $response->time_taken,
                    'topic' => $response->question?->topic ?? $response->question?->lesson_reference ?? 'general',
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
            'question_set_id' => $questionSet->id,
            'breakdown' => [
                'new' => $targets['new'],
                'missed' => count($selectedMissed),
                'old' => count($selectedOld),
            ],
            'questions' => $items,
        ];
    }

    private function resolveMixConfig(?AiSetting $settings): array
    {
        return [
            'mix_new_percentage' => $settings?->mix_new_percentage ?? 50,
            'mix_missed_percentage' => $settings?->mix_missed_percentage ?? 30,
            'mix_old_percentage' => $settings?->mix_old_percentage ?? 20,
        ];
    }

    private function calculateTargets(int $count, array $mixConfig, bool $hasHistory): array
    {
        if (!$hasHistory) {
            return [
                'new' => $count,
                'missed' => 0,
                'old' => 0,
            ];
        }

        $newTarget = (int) round($count * ($mixConfig['mix_new_percentage'] / 100));
        $missedTarget = (int) round($count * ($mixConfig['mix_missed_percentage'] / 100));
        $oldTarget = $count - $newTarget - $missedTarget;

        return [
            'new' => $newTarget,
            'missed' => $missedTarget,
            'old' => $oldTarget,
        ];
    }

    private function topUpQuestions(
        array $questions,
        Contestant $contestant,
        int $numberOfQuestions,
        int $difficulty,
        array $avoidIds,
        array $seedQuestions
    ): array {
        $current = $this->normalizeQuestions($questions);
        $remaining = $numberOfQuestions - count($current);

        if ($remaining <= 0) {
            return $current;
        }

        $newQuestions = $this->aiQuestionGenerator->generateNewQuestions(
            $contestant,
            $remaining,
            $difficulty,
            array_merge($avoidIds, $this->extractQuestionIds($current)),
            $seedQuestions
        );

        return array_merge($current, $newQuestions);
    }

    private function extractQuestionIds(array $questions): array
    {
        return array_values(array_filter(array_map(static function ($question) {
            if ($question instanceof Question) {
                return $question->id;
            }

            return $question['id'] ?? null;
        }, $questions)));
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
            ->unique('id')
            ->values()
            ->all();
    }

    private function fetchOldCorrectQuestions(int $contestantId, int $limit, array $excludedIds): array
    {
        return Question::query()
            ->select('questions.*')
            ->join('contestant_responses', 'contestant_responses.question_id', '=', 'questions.id')
            ->where('contestant_responses.contestant_id', $contestantId)
            ->where('contestant_responses.is_correct', true)
            ->whereNotIn('questions.id', $excludedIds)
            ->orderByRaw('questions.last_used_at IS NULL DESC, questions.last_used_at ASC')
            ->orderBy('questions.use_count')
            ->limit($limit)
            ->get()
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
                    'topic' => $question['topic'] ?? null,
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

    private function updateUsage(Collection $items): void
    {
        $questionIds = $items->pluck('question_id')->all();

        Question::query()
            ->whereIn('id', $questionIds)
            ->update([
                'use_count' => DB::raw('use_count + 1'),
                'last_used_at' => now(),
            ]);
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

    private function normalizeQuestions(array $questions): array
    {
        return array_values(array_map(static function ($question) {
            if ($question instanceof Question) {
                return $question;
            }

            return $question;
        }, $questions));
    }
}
