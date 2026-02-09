<div class="space-y-6">
    <div class="rounded-lg bg-white p-6 shadow">
        <h2 class="text-xl font-semibold">Student Analytics</h2>
        <p class="text-sm text-gray-500">Per-student performance reports.</p>
    </div>

    <div class="rounded-lg bg-white p-4 shadow">
        <pre class="text-xs text-gray-600">{{ json_encode($analytics, JSON_PRETTY_PRINT) }}</pre>
    </div>
</div>
