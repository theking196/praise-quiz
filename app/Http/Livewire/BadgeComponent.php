<?php

declare(strict_types=1);

namespace App\Http\Livewire;

use Livewire\Component;

class BadgeComponent extends Component
{
    public array $badges = [];

    public function mount(array $badges = []): void
    {
        $this->badges = $badges;
    }

    public function render()
    {
        return view('livewire.badge-component');
    }
}
