<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AiSetting;
use App\Models\Question;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    /**
     * Retrieve latest AI settings for admin dashboards.
     */
    public function settings(): array
    {
        return [
            'settings' => AiSetting::query()->latest('id')->first(),
        ];
    }

    /**
     * Store new AI settings for mix ratios and difficulty caps.
     */
    public function storeSettings(Request $request): array
    {
        $data = $request->validate([
            'mix_new_percentage' => ['required', 'integer', 'min:0', 'max:100'],
            'mix_missed_percentage' => ['required', 'integer', 'min:0', 'max:100'],
            'mix_old_percentage' => ['required', 'integer', 'min:0', 'max:100'],
            'max_difficulty_by_age_group' => ['nullable', 'array'],
        ]);

        $settings = AiSetting::query()->create($data);

        return [
            'settings' => $settings,
        ];
    }

    /**
     * Approve a question for production use.
     */
    public function approveQuestion(Request $request, int $questionId): array
    {
        $question = Question::query()->findOrFail($questionId);
        $question->approved_at = now();
        $question->approved_by = $request->user()?->id;
        $question->save();

        return [
            'question' => $question,
        ];
    }
}
