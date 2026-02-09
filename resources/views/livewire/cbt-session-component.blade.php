<div class="space-y-6">
    <div class="rounded-lg bg-white p-6 shadow">
        <h2 class="text-xl font-semibold">CBT Session</h2>
        <p class="text-sm text-gray-500">Questions update dynamically via Livewire.</p>
    </div>

    <div class="rounded-lg bg-white p-6 shadow">
        <div class="flex items-center justify-between">
            <h3 class="font-semibold">Question {{ $currentIndex + 1 }}</h3>
            <span class="text-sm text-gray-500">Timer: {{ $timerSeconds }}s</span>
        </div>
        <div class="mt-4">
            <pre class="text-sm text-gray-700">{{ json_encode($questions[$currentIndex] ?? [], JSON_PRETTY_PRINT) }}</pre>
        </div>
        <div class="mt-4">
            <input
                type="text"
                class="w-full rounded border px-3 py-2"
                wire:model.defer="answer"
                placeholder="Type your answer"
            />
        </div>
        <div class="mt-4 flex gap-2">
            <button class="rounded bg-indigo-600 px-4 py-2 text-white" wire:click="submitAnswer">Submit</button>
            <button class="rounded border px-4 py-2" wire:click="loadQuestions">Restart</button>
        </div>
    </div>

    <div class="rounded-lg bg-white p-6 shadow">
        <h3 class="font-semibold">Session Summary</h3>
        <pre class="mt-2 text-xs text-gray-600">{{ json_encode($sessionSummary, JSON_PRETTY_PRINT) }}</pre>
    </div>
</div>
