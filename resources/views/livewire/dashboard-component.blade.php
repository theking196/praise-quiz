<div class="space-y-6">
    <div class="rounded-lg bg-white p-6 shadow">
        <h2 class="text-xl font-semibold">Contestant Dashboard</h2>
        <p class="text-sm text-gray-500">Overview of XP, badges, and recent analytics.</p>
    </div>

    <div class="grid gap-4 md:grid-cols-2">
        <div class="rounded-lg bg-white p-4 shadow">
            <h3 class="font-semibold">XP & Stage</h3>
            <pre class="mt-2 text-xs text-gray-600">{{ json_encode($overview, JSON_PRETTY_PRINT) }}</pre>
        </div>
        <div class="rounded-lg bg-white p-4 shadow">
            <h3 class="font-semibold">Badges</h3>
            <livewire:badge-component :badges="$overview['analytics'][0]['badges_earned'] ?? []" />
        </div>
    </div>
</div>
