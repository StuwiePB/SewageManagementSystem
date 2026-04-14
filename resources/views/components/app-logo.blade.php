@props([
    'sidebar' => false,
])

@if($sidebar)
<<<<<<< HEAD
    <flux:sidebar.brand name="Laravel Starter Kit" {{ $attributes }}>
=======
    <flux:sidebar.brand name="{{ config('app.name') }}" {{ $attributes }}>
>>>>>>> 3wayfusionn-(use-this)
        <x-slot name="logo" class="flex aspect-square size-8 items-center justify-center rounded-md bg-accent-content text-accent-foreground">
            <x-app-logo-icon class="size-5 fill-current text-white dark:text-black" />
        </x-slot>
    </flux:sidebar.brand>
@else
<<<<<<< HEAD
    <flux:brand name="Laravel Starter Kit" {{ $attributes }}>
=======
    <flux:brand name="{{ config('app.name') }}" {{ $attributes }}>
>>>>>>> 3wayfusionn-(use-this)
        <x-slot name="logo" class="flex aspect-square size-8 items-center justify-center rounded-md bg-accent-content text-accent-foreground">
            <x-app-logo-icon class="size-5 fill-current text-white dark:text-black" />
        </x-slot>
    </flux:brand>
@endif
