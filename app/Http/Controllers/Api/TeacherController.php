<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Contestant;
use App\Models\ContestantResponse;
use Illuminate\Http\Request;

class TeacherController extends Controller
{
    /**
     * Fetch students filtered by category, age group, or competition.
     */
    public function students(Request $request): array
    {
        $data = $request->validate([
            'category_id' => ['sometimes', 'integer'],
            'age_group_id' => ['sometimes', 'integer'],
            'competition_id' => ['sometimes', 'integer'],
        ]);

        $query = Contestant::query()->with(['user', 'category', 'ageGroup', 'competition']);

        if (isset($data['category_id'])) {
            $query->where('category_id', $data['category_id']);
        }

        if (isset($data['age_group_id'])) {
            $query->where('age_group_id', $data['age_group_id']);
        }

        if (isset($data['competition_id'])) {
            $query->where('competition_id', $data['competition_id']);
        }

        return [
            'students' => $query->limit(100)->get(),
        ];
    }

    /**
     * View recent contestant responses for review and coaching.
     */
    public function recentResponses(Request $request): array
    {
        $data = $request->validate([
            'contestant_id' => ['sometimes', 'integer'],
        ]);

        $query = ContestantResponse::query()->with(['contestant', 'question'])->latest('id');

        if (isset($data['contestant_id'])) {
            $query->where('contestant_id', $data['contestant_id']);
        }

        return [
            'responses' => $query->limit(50)->get(),
        ];
    }

    /**
     * Teacher/director analytics summary for dashboards.
     */
    public function analytics(Request $request): array
    {
        $data = $request->validate([
            'competition_id' => ['sometimes', 'integer'],
        ]);

        $query = Contestant::query()->with('performanceAnalytics');

        if (isset($data['competition_id'])) {
            $query->where('competition_id', $data['competition_id']);
        }

        return [
            'analytics' => $query->limit(100)->get(),
        ];
    }

    /**
     * Recent question sets for review.
     */
    public function questionSets(Request $request): array
    {
        $data = $request->validate([
            'competition_id' => ['sometimes', 'integer'],
        ]);

        $query = \App\Models\QuestionSet::query()->with('items')->latest('id');

        if (isset($data['competition_id'])) {
            $query->where('competition_id', $data['competition_id']);
        }

        return [
            'question_sets' => $query->limit(20)->get(),
        ];
    }
}
