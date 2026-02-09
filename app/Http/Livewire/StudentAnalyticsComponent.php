<?php

declare(strict_types=1);

namespace App\Http\Livewire;

use Illuminate\Support\Facades\Http;
use Livewire\Component;

class StudentAnalyticsComponent extends Component
{
    public int $contestantId = 0;
    public array $analytics = [];

    public function mount(int $contestantId = 0): void
    {
        $this->contestantId = $contestantId;
        $this->loadAnalytics();
    }

    public function loadAnalytics(): void
    {
        if ($this->contestantId === 0) {
            $this->analytics = [];
            return;
        }

        $response = Http::get('/api/performance', [
            'contestant_id' => $this->contestantId,
        ]);

        $this->analytics = $response->json() ?? [];
    }

    public function render()
    {
        return view('livewire.student-analytics-component');
    }
}
