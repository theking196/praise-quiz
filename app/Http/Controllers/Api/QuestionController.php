<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Contestant;
use App\Services\QuestionSetGenerator;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    public function __construct(
        private QuestionSetGenerator $questionSetGenerator
    ) {
    }

    public function questionSet(Request $request, int $contestantId): array
    {
        $data = $request->validate([
            'category_id' => ['required', 'integer'],
            'age_group_id' => ['required', 'integer'],
            'difficulty' => ['required', 'integer', 'min:1'],
            'number_of_questions' => ['required', 'integer', 'min:1', 'max:100'],
        ]);

        $contestant = Contestant::query()->with(['category', 'ageGroup'])->findOrFail($contestantId);

        $payload = $this->questionSetGenerator->generate(
            $contestant,
            $data['category_id'],
            $data['age_group_id'],
            $data['difficulty'],
            $data['number_of_questions']
        );

        return [
            'contestant' => $contestant,
            'question_set' => $payload['question_set'],
            'items' => $payload['items'],
            'analysis' => $payload['analysis'],
            'mix_config' => $payload['mix_config'],
        ];
    }
}
