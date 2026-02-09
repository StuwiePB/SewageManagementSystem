<x-layouts::customer :title="__('Report new incident') . ' – BruFlow'">
    <div class="p-4 lg:p-6">
        <h1 class="text-xl font-semibold text-zinc-100">{{ __('Report new incident') }}</h1>
        <p class="text-sm text-zinc-400 mt-1">
            {{ __('Describe the sewage, drainage or related issue. JKR will be notified.') }}
        </p>
        <div class="mt-6 rounded-xl bg-zinc-800/80 border border-zinc-700 p-6">
            <p class="text-zinc-400">
                {{ __('Report form coming soon. You will be able to add location, description, photos and contact details here.') }}
            </p>
            <a href="{{ route('customer.dashboard') }}" class="inline-flex items-center gap-2 mt-4 text-sm text-amber-400 hover:text-amber-300" wire:navigate>
                ← {{ __('Back to Home') }}
            </a>
        </div>
    </div>
</x-layouts::customer>
