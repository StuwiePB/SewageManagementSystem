<x-layouts::customer :title="__('Profile') . ' – BruFlow'">
    <div class="p-4 lg:p-6 space-y-6 flex flex-col items-center">
        <p class="text-xs text-zinc-400 pl-1">
            {{ __('Your profile and account') }}
        </p>

        <div style="display: flex; flex-direction: column; gap: 0.375rem; margin-top: 5rem; max-width: 20rem; width: 100%;">
            <div class="rounded-xl bg-white dark:bg-white" style="background-color: rgba(255, 255, 255, 0.25); padding-top: 0.5rem; padding-bottom: 0.5rem; padding-left: 0.75rem; padding-right: 0.75rem;">
                <button type="button" class="w-full flex items-center gap-4 text-left text-white text-xs hover:opacity-90 transition-opacity">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    <span class="font-medium pl-0.5">{{ __('My Profile') }}</span>
                </button>
            </div>
            <div class="rounded-xl bg-white dark:bg-white" style="background-color: rgba(255, 255, 255, 0.25); padding-top: 0.5rem; padding-bottom: 0.5rem; padding-left: 0.75rem; padding-right: 0.75rem;">
                <button type="button" class="w-full flex items-center gap-4 text-left text-white text-xs hover:opacity-90 transition-opacity">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                    <span class="font-medium pl-0.5">{{ __('My Payments') }}</span>
                </button>
            </div>
            <div class="rounded-xl bg-white dark:bg-white" style="background-color: rgba(255, 255, 255, 0.25); padding-top: 0.5rem; padding-bottom: 0.5rem; padding-left: 0.75rem; padding-right: 0.75rem;">
                <button type="button" class="w-full flex items-center gap-4 text-left text-white text-xs hover:opacity-90 transition-opacity">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span class="font-medium pl-0.5">{{ __('General') }}</span>
                </button>
            </div>
            <div class="rounded-xl bg-white dark:bg-white" style="background-color: rgba(255, 255, 255, 0.25); padding-top: 0.5rem; padding-bottom: 0.5rem; padding-left: 0.75rem; padding-right: 0.75rem;">
                <button type="button" class="w-full flex items-center gap-4 text-left text-white text-xs hover:opacity-90 transition-opacity">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span class="font-medium pl-0.5">{{ __('Help FAQ') }}</span>
                </button>
            </div>
            <div class="rounded-xl bg-white dark:bg-white" style="background-color: rgba(255, 255, 255, 0.25); padding-top: 0.5rem; padding-bottom: 0.5rem; padding-left: 0.75rem; padding-right: 0.75rem;">
                <button type="button" class="w-full flex items-center gap-4 text-left text-white text-xs hover:opacity-90 transition-opacity">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    <span class="font-medium pl-0.5">{{ __('Contact Customer Support') }}</span>
                </button>
            </div>
            <div class="rounded-xl bg-white dark:bg-white" style="background-color: rgba(255, 255, 255, 0.25); padding-top: 0.5rem; padding-bottom: 0.5rem; padding-left: 0.75rem; padding-right: 0.75rem;">
                <button type="button" class="w-full flex items-center gap-4 text-left text-white text-xs hover:opacity-90 transition-opacity">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span class="font-medium pl-0.5">{{ __('Report') }}</span>
                </button>
            </div>

            <form method="POST" action="{{ route('logout') }}" class="block">
                @csrf
                <div class="rounded-xl bg-white dark:bg-white" style="background-color: rgba(255, 255, 255, 0.25); padding-top: 0.5rem; padding-bottom: 0.5rem; padding-left: 0.75rem; padding-right: 0.75rem;">
                    <button type="submit" class="w-full flex items-center gap-4 text-left text-white text-xs hover:opacity-90 transition-opacity cursor-pointer">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        <span class="font-medium pl-0.5">{{ __('Logs out') }}</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts::customer>
