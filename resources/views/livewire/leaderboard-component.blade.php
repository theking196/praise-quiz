<div class="space-y-6">
    <div class="rounded-lg bg-white p-6 shadow">
        <h2 class="text-xl font-semibold">Leaderboard</h2>
        <p class="text-sm text-gray-500">Top performers by age group and category.</p>
    </div>

    <div class="rounded-lg bg-white p-4 shadow">
        <pre class="text-xs text-gray-600">{{ json_encode($leaderboard, JSON_PRETTY_PRINT) }}</pre>
    </div>
</div>
