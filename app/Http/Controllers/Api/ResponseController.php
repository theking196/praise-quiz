<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Contestant;
use App\Models\ContestantResponse;
use App\Models\PerformanceAnalytics;
use App\Services\AdaptiveLearningService;
use App\Services\ScoringService;
use Illuminate\Http\Request;

class ResponseController extends Controller
{
    public function __construct(
        private ScoringService $scoringService,
        private AdaptiveLearningService $adaptiveService
    ) {
    }

    public function submit(Request $request, int $contestantId): array
    {
        $data = $request->validate([
            'question_id' => ['required', 'integer'],
            'response' => ['required', 'string'],
            'is_correct' => ['required', 'boolean'],
            'time_taken' => ['required', 'numeric'],
            'points' => ['sometimes', 'integer'],
            'topic' => ['sometimes', 'string'],
            'difficulty' => ['required', 'integer', 'min:1'],
        ]);

        $contestant = Contestant::query()->findOrFail($contestantId);

        $contestantResponse = ContestantResponse::query()->create([
            'contestant_id' => $contestant->id,
            'question_id' => $data['question_id'],
            'response' => $data['response'],
            'is_correct' => $data['is_correct'],
            'time_taken' => $data['time_taken'],
        ]);

        $scoreData = $this->scoringService->scoreResponse($data, $data['difficulty']);
        $analytics = PerformanceAnalytics::query()->where('contestant_id', $contestant->id)->latest('id')->first();
        $analysis = $this->adaptiveService->analyzeResponses([$data], $analytics);
        $this->adaptiveService->persistAnalytics($contestant, $analysis, $scoreData['score']);
        $this->adaptiveService->updateContestantProfile($contestant, $analysis);

        return [
            'contestant' => $contestant,
            'response' => $contestantResponse,
            'score' => $scoreData,
            'analysis' => $analysis,
        ];
    }
}
