<?php

declare(strict_types=1);

namespace App\Http\Livewire;

use Illuminate\Support\Facades\Http;
use Livewire\Component;

class LeaderboardComponent extends Component
{
    public int $ageGroupId = 0;
    public int $categoryId = 0;
    public array $leaderboard = [];

    public function mount(): void
    {
        $this->loadLeaderboard();
    }

    public function loadLeaderboard(): void
    {
        $response = Http::get('/api/analytics/leaderboard', array_filter([
            'age_group_id' => $this->ageGroupId ?: null,
            'category_id' => $this->categoryId ?: null,
        ]));

        $this->leaderboard = $response->json() ?? [];
    }

    public function render()
    {
        return view('livewire.leaderboard-component');
    }
}
