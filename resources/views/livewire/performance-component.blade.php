<div class="space-y-6">
    <div class="rounded-lg bg-white p-6 shadow">
        <h2 class="text-xl font-semibold">Performance Analytics</h2>
        <p class="text-sm text-gray-500">Weak topics and drill suggestions update automatically.</p>
    </div>

    <div class="grid gap-4 md:grid-cols-2">
        <div class="rounded-lg bg-white p-4 shadow">
            <h3 class="font-semibold">Analytics</h3>
            <pre class="mt-2 text-xs text-gray-600">{{ json_encode($analytics, JSON_PRETTY_PRINT) }}</pre>
        </div>
        <div class="rounded-lg bg-white p-4 shadow">
            <h3 class="font-semibold">Practice Drills</h3>
            <pre class="mt-2 text-xs text-gray-600">{{ json_encode($drills, JSON_PRETTY_PRINT) }}</pre>
        </div>
    </div>
</div>
