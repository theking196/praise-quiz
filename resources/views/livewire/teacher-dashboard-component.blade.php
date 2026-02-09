<div class="space-y-6">
    <div class="rounded-lg bg-white p-6 shadow">
        <h2 class="text-xl font-semibold">Teacher Dashboard</h2>
        <p class="text-sm text-gray-500">Filter students and monitor performance.</p>
    </div>

    <div class="rounded-lg bg-white p-4 shadow">
        <div class="grid gap-4 md:grid-cols-2">
            <input
                type="number"
                class="w-full rounded border px-3 py-2"
                wire:model.defer="categoryId"
                placeholder="Category ID"
            />
            <input
                type="number"
                class="w-full rounded border px-3 py-2"
                wire:model.defer="ageGroupId"
                placeholder="Age Group ID"
            />
        </div>
        <button class="mt-4 rounded bg-indigo-600 px-4 py-2 text-white" wire:click="loadStudents">Apply Filters</button>
    </div>

    <div class="rounded-lg bg-white p-4 shadow">
        <h3 class="font-semibold">Students</h3>
        <pre class="mt-2 text-xs text-gray-600">{{ json_encode($students, JSON_PRETTY_PRINT) }}</pre>
    </div>
</div>
