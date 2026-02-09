<?php

declare(strict_types=1);

namespace App\Http\Livewire;

use Illuminate\Support\Facades\Http;
use Livewire\Component;

class CBTSessionComponent extends Component
{
    public int $contestantId = 0;
    public int $categoryId = 0;
    public int $difficultyLevel = 1;
    public int $timerSeconds = 30;
    public int $currentIndex = 0;
    public array $questions = [];
    public string $answer = '';
    public array $sessionSummary = [];

    public function mount(int $contestantId = 0, int $categoryId = 0, int $difficultyLevel = 1): void
    {
        $this->contestantId = $contestantId;
        $this->categoryId = $categoryId;
        $this->difficultyLevel = $difficultyLevel;
        $this->loadQuestions();
    }

    public function loadQuestions(): void
    {
        $response = Http::get('/api/questions', [
            'contestant_id' => $this->contestantId,
            'category_id' => $this->categoryId,
            'difficulty_level' => $this->difficultyLevel,
            'number_of_questions' => 10,
        ]);

        $payload = $response->json() ?? [];
        $this->questions = $payload['items'] ?? [];
        $this->currentIndex = 0;
        $this->sessionSummary = [];
    }

    public function submitAnswer(): void
    {
        if (!isset($this->questions[$this->currentIndex])) {
            return;
        }

        $question = $this->questions[$this->currentIndex];

        $response = Http::post('/api/responses', [
            'contestant_id' => $this->contestantId,
            'question_id' => $question['question_id'] ?? null,
            'response' => $this->answer,
            'is_correct' => true,
            'time_taken' => $this->timerSeconds,
            'difficulty' => $this->difficultyLevel,
            'question_type' => 'bible_quiz',
        ]);

        $this->sessionSummary[] = $response->json() ?? [];
        $this->answer = '';
        $this->currentIndex++;
    }

    public function render()
    {
        return view('livewire.cbt-session-component');
    }
}
