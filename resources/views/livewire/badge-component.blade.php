<div class="flex flex-wrap gap-2">
    @forelse ($badges as $badge)
        <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs text-emerald-700">{{ $badge }}</span>
    @empty
        <span class="text-xs text-gray-500">No badges earned yet.</span>
    @endforelse
</div>
