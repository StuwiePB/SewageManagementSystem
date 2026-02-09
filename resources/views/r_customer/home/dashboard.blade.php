<x-layouts::customer :title="__('Home') . ' – BruFlow'">
    <div class="p-4 lg:p-6 space-y-6">
        <p class="text-sm text-zinc-400">
            {{ __('Sewage & drainage incident overview') }}
        </p>

        {{-- Dummy graph with grid --}}
        <section>
            <h2 class="text-sm font-medium text-zinc-400 uppercase tracking-wider mb-3">{{ __('Incidents last 7 days') }}</h2>
            <div class="rounded-xl bg-zinc-800/80 p-4 overflow-hidden">
                @php $chartLeft = 24; $chartRight = 390; $chartGap = ($chartRight - $chartLeft) / 7; @endphp
                <svg viewBox="0 0 400 220" class="w-full h-52 text-zinc-500 -ml-3" preserveAspectRatio="xMidYMid meet" aria-hidden="true">
                    {{-- Grid lines (horizontal) --}}
                    @for ($i = 0; $i <= 5; $i++)
                        <line x1="{{ $chartLeft }}" y1="{{ 20 + $i * 36 }}" x2="{{ $chartRight }}" y2="{{ 20 + $i * 36 }}" stroke="currentColor" stroke-width="0.5" opacity="0.4"/>
                    @endfor
                    {{-- Grid lines (vertical) --}}
                    @for ($i = 0; $i <= 7; $i++)
                        <line x1="{{ $chartLeft + $i * $chartGap }}" y1="20" x2="{{ $chartLeft + $i * $chartGap }}" y2="200" stroke="currentColor" stroke-width="0.5" opacity="0.4"/>
                    @endfor
                    {{-- Y-axis labels --}}
                    @foreach([10, 8, 6, 4, 2, 0] as $idx => $val)
                        <text x="{{ $chartLeft - 8 }}" y="{{ 24 + $idx * 36 }}" text-anchor="end" font-size="10" fill="currentColor" opacity="0.7">{{ $val }}</text>
                    @endforeach
                    {{-- X-axis labels --}}
                    @foreach(['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] as $idx => $day)
                        <text x="{{ $chartLeft + ($idx + 0.5) * $chartGap }}" y="214" text-anchor="middle" font-size="10" fill="currentColor" opacity="0.7">{{ $day }}</text>
                    @endforeach
                    {{-- Line graph (dummy data: 4, 7, 3, 8, 5, 6, 4) --}}
                    @php $values = [4, 7, 3, 8, 5, 6, 4]; $max = 10; @endphp
                    @php
                        $points = collect($values)->map(fn ($v, $i) => ($chartLeft + ($i + 0.5) * $chartGap) . ',' . (200 - ($v / $max) * 180))->implode(' ');
                    @endphp
                    <polyline points="{{ $points }}" fill="none" stroke="#3b82f6" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                    @foreach($values as $i => $v)
                        @php $cx = $chartLeft + ($i + 0.5) * $chartGap; $cy = 200 - ($v / $max) * 180; @endphp
                        <circle cx="{{ $cx }}" cy="{{ $cy }}" r="4" fill="#3b82f6"/>
                    @endforeach
                    {{-- Axis frame --}}
                    <line x1="{{ $chartLeft }}" y1="20" x2="{{ $chartLeft }}" y2="200" stroke="currentColor" stroke-width="1" opacity="0.6"/>
                    <line x1="{{ $chartLeft }}" y1="200" x2="{{ $chartRight }}" y2="200" stroke="currentColor" stroke-width="1" opacity="0.6"/>
                </svg>
                <p class="text-xs text-zinc-500 mt-2">{{ __('Sample data – connect to your incident database') }}</p>
            </div>
        </section>

        {{-- Active incidents right now --}}
        <section>
            <h2 class="text-sm font-medium text-zinc-400 uppercase tracking-wider mb-3">{{ __('Active incidents right now') }}</h2>
            <div class="space-y-3">
                <div class="rounded-xl bg-zinc-800/80 border border-zinc-700 p-4">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <p class="font-medium text-zinc-200">{{ __('Blocked drain, Jalan Sultan') }}</p>
                            <p class="text-sm text-zinc-500 mt-1">{{ __('Reported 2 min ago · JKR notified') }}</p>
                        </div>
                        <span class="h-2 w-2 rounded-full bg-amber-400 shrink-0 mt-1.5" aria-hidden="true"></span>
                    </div>
                </div>
                <div class="rounded-xl bg-zinc-800/80 border border-zinc-700 p-4">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <p class="font-medium text-zinc-200">{{ __('Sewage smell, Kg Ayer') }}</p>
                            <p class="text-sm text-zinc-500 mt-1">{{ __('Reported 28 min ago · Under review') }}</p>
                        </div>
                        <span class="h-2 w-2 rounded-full bg-amber-400 shrink-0 mt-1.5" aria-hidden="true"></span>
                    </div>
                </div>
                <div class="rounded-xl bg-zinc-800/80 border border-zinc-700 p-4">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <p class="font-medium text-zinc-200">{{ __('Water pooling, Seria') }}</p>
                            <p class="text-sm text-zinc-500 mt-1">{{ __('Reported 1h ago · Crew dispatched') }}</p>
                        </div>
                        <span class="h-2 w-2 rounded-full bg-amber-400 shrink-0 mt-1.5" aria-hidden="true"></span>
                    </div>
                </div>
            </div>
            <p class="text-xs text-zinc-500 mt-2">{{ __('These are sample entries – connect to your incident database') }}</p>
        </section>
    </div>
</x-layouts::customer>
