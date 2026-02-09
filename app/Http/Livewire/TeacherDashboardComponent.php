<?php

declare(strict_types=1);

namespace App\Http\Livewire;

use Illuminate\Support\Facades\Http;
use Livewire\Component;

class TeacherDashboardComponent extends Component
{
    public int $categoryId = 0;
    public int $ageGroupId = 0;
    public array $students = [];

    public function mount(): void
    {
        $this->loadStudents();
    }

    public function loadStudents(): void
    {
        $response = Http::get('/api/teacher/students', array_filter([
            'category_id' => $this->categoryId ?: null,
            'age_group_id' => $this->ageGroupId ?: null,
        ]));

        $this->students = $response->json() ?? [];
    }

    public function render()
    {
        return view('livewire.teacher-dashboard-component');
    }
}
