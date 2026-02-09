<?php

declare(strict_types=1);

namespace App\Http\Livewire;

use Illuminate\Support\Facades\Http;
use Livewire\Component;

class DashboardComponent extends Component
{
    public int $contestantId = 0;
    public array $overview = [];

    public function mount(int $contestantId = 0): void
    {
        $this->contestantId = $contestantId;
        $this->loadOverview();
    }

    public function loadOverview(): void
    {
        if ($this->contestantId === 0) {
            $this->overview = [];
            return;
        }

        $response = Http::get('/api/contestants/' . $this->contestantId . '/analytics');
        $this->overview = $response->json() ?? [];
    }

    public function render()
    {
        return view('livewire.dashboard-component');
    }
}
