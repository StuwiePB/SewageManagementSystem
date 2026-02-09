<x-layouts::customer :title="__('Map') . ' – BruFlow'">
    <div class="p-4 lg:p-6">
        <h1 class="text-xl font-semibold text-zinc-100">{{ __('Map') }}</h1>
        <p class="text-sm text-zinc-400 mt-1">{{ __('View incidents on the map') }}</p>
        <div class="mt-6 rounded-xl bg-zinc-800/80 border border-zinc-700 p-8 flex flex-col items-center justify-center min-h-[280px] text-center">
            <svg class="w-16 h-16 text-zinc-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
            </svg>
            <p class="text-zinc-400">{{ __('Map view coming soon') }}</p>
            <p class="text-sm text-zinc-500 mt-1">{{ __('Incidents will appear here by location') }}</p>
        </div>
    </div>
</x-layouts::customer>
