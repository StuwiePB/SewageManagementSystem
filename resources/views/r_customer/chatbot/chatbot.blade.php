<x-layouts::customer :title="__('Chatbot') . ' – BruFlow'">
    <div class="p-4 lg:p-6">
        <h1 class="text-xl font-semibold text-zinc-100">{{ __('Chatbot') }}</h1>
        <p class="text-sm text-zinc-400 mt-1">{{ __('Get help reporting or checking incidents') }}</p>
        <div class="mt-6 rounded-xl bg-zinc-800/80 border border-zinc-700 p-8 flex flex-col items-center justify-center min-h-[280px] text-center">
            <svg class="w-16 h-16 text-zinc-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
            </svg>
            <p class="text-zinc-400">{{ __('Chatbot coming soon') }}</p>
            <p class="text-sm text-zinc-500 mt-1">{{ __('Ask how to report, check status, or get support') }}</p>
        </div>
    </div>
</x-layouts::customer>
