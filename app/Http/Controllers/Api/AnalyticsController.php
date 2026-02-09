<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ContestantResponse;
use App\Models\PerformanceAnalytics;
use App\Models\QuestionSet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    /**
     * Return analytics history for a contestant.
     */
    public function performance(Request $request, int $contestantId): array
    {
        $data = $request->validate([
            'limit' => ['sometimes', 'integer', 'min:1', 'max:50'],
        ]);

        $limit = $data['limit'] ?? 10;

        return [
            'analytics' => PerformanceAnalytics::query()
                ->where('contestant_id', $contestantId)
                ->latest('id')
                ->limit($limit)
                ->get(),
        ];
    }

    /**
     * Leaderboard view filtered by age group, category, or competition year.
     */
    public function leaderboard(Request $request): array
    {
        $data = $request->validate([
            'age_group_id' => ['sometimes', 'integer'],
            'category_id' => ['sometimes', 'integer'],
            'competition_year' => ['sometimes', 'integer'],
        ]);

        $query = PerformanceAnalytics::query()
            ->select('contestant_id', DB::raw('MAX(total_score) as total_score'))
            ->groupBy('contestant_id')
            ->with('contestant');

        if (isset($data['age_group_id'])) {
            $query->whereHas('contestant', static fn ($q) => $q->where('age_group_id', $data['age_group_id']));
        }

        if (isset($data['category_id'])) {
            $query->whereHas('contestant', static fn ($q) => $q->where('category_id', $data['category_id']));
        }

        if (isset($data['competition_year'])) {
            $query->whereHas('contestant.competition', static fn ($q) => $q->where('year', $data['competition_year']));
        }

        return [
            'leaderboard' => $query->orderByDesc('total_score')->limit(50)->get(),
        ];
    }

    /**
     * Weak topic heatmap across contestants or per contestant.
     */
    public function weakTopics(Request $request): array
    {
        $data = $request->validate([
            'contestant_id' => ['sometimes', 'integer'],
        ]);

        $query = PerformanceAnalytics::query();
        if (isset($data['contestant_id'])) {
            $query->where('contestant_id', $data['contestant_id']);
        }

        return [
            'weak_topics' => $query->get()->pluck('weak_topics')->filter()->values(),
        ];
    }

    /**
     * Average scores for dashboard trend analysis.
     */
    public function averageScores(Request $request): array
    {
        $data = $request->validate([
            'age_group_id' => ['sometimes', 'integer'],
            'category_id' => ['sometimes', 'integer'],
        ]);

        $query = PerformanceAnalytics::query()
            ->select('contestant_id', DB::raw('AVG(total_score) as average_score'))
            ->groupBy('contestant_id')
            ->with('contestant');

        if (isset($data['age_group_id'])) {
            $query->whereHas('contestant', static fn ($q) => $q->where('age_group_id', $data['age_group_id']));
        }

        if (isset($data['category_id'])) {
            $query->whereHas('contestant', static fn ($q) => $q->where('category_id', $data['category_id']));
        }

        return [
            'average_scores' => $query->get(),
        ];
    }

    /**
     * Drill recommendations derived from weak topics.
     */
    public function drillRecommendations(Request $request): array
    {
        $data = $request->validate([
            'contestant_id' => ['sometimes', 'integer'],
        ]);

        $query = PerformanceAnalytics::query();
        if (isset($data['contestant_id'])) {
            $query->where('contestant_id', $data['contestant_id']);
        }

        $drills = $query->get()->map(static function ($analytics) {
            return [
                'contestant_id' => $analytics->contestant_id,
                'drills' => collect($analytics->weak_topics ?? [])->map(static function ($topic) {
                    return [
                        'topic' => $topic['topic'] ?? 'general',
                        'target_questions' => max(5, ($topic['mistakes'] ?? 1) * 2),
                        'focus' => 'timed_practice',
                    ];
                }),
            ];
        });

        return [
            'drill_recommendations' => $drills,
        ];
    }

    /**
     * Recent adaptive question sets for teacher/director dashboards.
     */
    public function recentQuestionSets(Request $request): array
    {
        $data = $request->validate([
            'competition_id' => ['sometimes', 'integer'],
        ]);

        $query = QuestionSet::query()->latest('id')->with('items');
        if (isset($data['competition_id'])) {
            $query->where('competition_id', $data['competition_id']);
        }

        return [
            'question_sets' => $query->limit(20)->get(),
        ];
    }

    /**
     * Export reporting data (placeholder for CSV/XLSX streaming).
     */
    public function exportReport(Request $request): array
    {
        $data = $request->validate([
            'format' => ['required', 'in:csv,xlsx'],
        ]);

        $rows = ContestantResponse::query()->with(['contestant', 'question'])->limit(500)->get();

        return [
            'format' => $data['format'],
            'rows' => $rows,
            'note' => 'Export handling should stream CSV/XLSX from storage in production.',
        ];
    }
}
