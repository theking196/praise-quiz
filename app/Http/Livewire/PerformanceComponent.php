<?php

declare(strict_types=1);

namespace App\Http\Livewire;

use Illuminate\Support\Facades\Http;
use Livewire\Component;

class PerformanceComponent extends Component
{
    public int $contestantId = 0;
    public array $analytics = [];
    public array $drills = [];

    public function mount(int $contestantId = 0): void
    {
        $this->contestantId = $contestantId;
        $this->loadAnalytics();
    }

    public function loadAnalytics(): void
    {
        if ($this->contestantId === 0) {
            $this->analytics = [];
            $this->drills = [];
            return;
        }

        $analyticsResponse = Http::get('/api/performance', [
            'contestant_id' => $this->contestantId,
        ]);
        $this->analytics = $analyticsResponse->json() ?? [];

        $drillResponse = Http::get('/api/practice-drills', [
            'contestant_id' => $this->contestantId,
        ]);
        $this->drills = $drillResponse->json() ?? [];
    }

    public function render()
    {
        return view('livewire.performance-component');
    }
}
