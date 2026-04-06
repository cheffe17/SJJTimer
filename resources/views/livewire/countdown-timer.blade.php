<div>
    <div class="bg-white rounded-2xl shadow-lg shadow-indigo-100/50 overflow-hidden border border-gray-100/50">
        {{-- Header --}}
        <div class="bg-gradient-to-r from-violet-500 via-indigo-500 to-blue-500 px-6 py-5">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-bold text-white flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-white/20 backdrop-blur-sm flex items-center justify-center">
                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    Countdown
                </h2>
                <span class="text-white/80 text-sm font-medium">
                    @if($activeEvent)
                        @if($isStayActive && $location) auf Besuch @else Gerade aktiv @endif
                    @elseif($nextEvent)
                        bis zum nächsten Event
                    @endif
                </span>
            </div>
        </div>

        {{-- Filter --}}
        <div class="px-6 pt-4 pb-1 flex items-center gap-2 flex-wrap">
            <span class="text-xs text-gray-400 mr-1 uppercase tracking-wider font-semibold">Filter</span>
            @php
                $filters = [
                    'visit' => ['icon' => '&#9992;', 'label' => 'Besuche', 'color' => 'sky'],
                    'virtual_date' => ['icon' => '&#128187;', 'label' => 'Virtuell', 'color' => 'violet'],
                    'live_date' => ['icon' => '&#10084;', 'label' => 'Live Date', 'color' => 'rose'],
                    'anniversary' => ['icon' => '&#127874;', 'label' => 'Jahrestag', 'color' => 'amber'],
                ];
            @endphp
            @foreach($filters as $filterKey => $filter)
                <button wire:click="setFilter('{{ $filterKey }}')"
                    class="px-3 py-1.5 rounded-lg text-xs font-medium transition-all duration-200
                        {{ $filterType === $filterKey
                            ? 'bg-' . $filter['color'] . '-500 text-white shadow-md shadow-' . $filter['color'] . '-200/50'
                            : 'bg-gray-50 text-gray-500 hover:bg-' . $filter['color'] . '-50 hover:text-' . $filter['color'] . '-600 border border-gray-100' }}">
                    {!! $filter['icon'] !!} {{ $filter['label'] }}
                </button>
            @endforeach
        </div>

        <div class="p-6">
            @if($activeEvent)
                {{-- ============ ACTIVE EVENT (rein Alpine.js) ============ --}}
                <div
                    x-data="{
                        start: {{ $activeStartTimestamp }},
                        end: {{ $activeEndTimestamp }},
                        progress: 0,
                        days: 0, hours: 0, minutes: 0, seconds: 0,
                        finished: false,
                        tick() {
                            const now = Date.now();
                            const total = this.end - this.start;
                            const elapsed = now - this.start;
                            this.progress = Math.min(100, Math.max(0, (elapsed / total) * 100));
                            const left = Math.max(0, this.end - now);
                            if (left <= 0) {
                                this.finished = true;
                                this.days = this.hours = this.minutes = this.seconds = 0;
                                setTimeout(() => $wire.$refresh(), 2000);
                                return;
                            }
                            this.days = Math.floor(left / 86400000);
                            this.hours = Math.floor((left % 86400000) / 3600000);
                            this.minutes = Math.floor((left % 3600000) / 60000);
                            this.seconds = Math.floor((left % 60000) / 1000);
                        },
                        init() { this.tick(); setInterval(() => this.tick(), 1000); }
                    }"
                >
                    {{-- Visit location banner --}}
                    @if($isStayActive && $location)
                        <div class="text-center mb-5">
                            <div class="inline-flex items-center gap-2.5 px-6 py-3 rounded-2xl bg-gradient-to-r from-green-50 to-emerald-50 border-2 border-green-200 shadow-sm shadow-green-100/50">
                                <span class="relative flex h-3 w-3">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
                                </span>
                                <span class="text-lg font-bold text-green-700">
                                    auf Besuch in {{ $location }} aktiv!
                                </span>
                            </div>
                        </div>
                    @else
                        <div class="text-center mb-5">
                            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-bold tracking-wide
                                {{ $isStayActive ? 'bg-green-100 text-green-700' : 'bg-emerald-100 text-emerald-700' }}">
                                <span class="relative flex h-2 w-2">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full {{ $isStayActive ? 'bg-green-400' : 'bg-emerald-400' }} opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-2 w-2 {{ $isStayActive ? 'bg-green-500' : 'bg-emerald-500' }}"></span>
                                </span>
                                {{ $isStayActive ? 'ZUSAMMEN' : 'AKTIV' }}
                            </div>
                        </div>
                    @endif

                    {{-- Event info --}}
                    @php $color = \App\Models\Event::typeColor($activeEventType); @endphp
                    <div class="text-center mb-5">
                        <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-medium
                            bg-{{ $color }}-100 text-{{ $color }}-700">
                            {!! \App\Models\Event::typeIcon($activeEventType) !!}
                            {{ $activeEventTitle }}
                        </div>
                        <p class="text-sm text-gray-500 mt-2">
                            {{ $activeEventDate }}
                            &mdash;
                            {{ $activeEventEndDate }} Uhr
                            @if($isStayActive && $activeEvent->return_time)
                                <span class="text-xs text-gray-400">(Rückflug)</span>
                            @endif
                        </p>
                    </div>

                    {{-- Countdown boxes with colon separators --}}
                    <div x-show="!finished" class="flex items-center justify-center gap-2 sm:gap-3 mb-5">
                        @foreach(['days' => 'Tage', 'hours' => 'Std', 'minutes' => 'Min', 'seconds' => 'Sek'] as $var => $label)
                            @if($var !== 'days')
                                <div class="text-2xl sm:text-3xl font-bold {{ $isStayActive ? 'text-green-300' : 'text-indigo-200' }} -mx-0.5 self-start mt-3">:</div>
                            @endif
                            <div class="text-center">
                                <div class="relative bg-gradient-to-b {{ $isStayActive ? 'from-green-700 to-green-900' : 'from-slate-800 to-slate-900' }} rounded-2xl px-3 sm:px-4 py-3 sm:py-4 shadow-lg {{ $isStayActive ? 'shadow-green-900/30' : 'shadow-slate-400/20' }} min-w-[60px] sm:min-w-[72px] overflow-hidden">
                                    {{-- Flip-clock divider line --}}
                                    <div class="absolute inset-x-0 top-1/2 h-px bg-black/10"></div>
                                    <span x-text="String({{ $var }}).padStart(2, '0')" class="relative z-10 text-3xl sm:text-4xl font-extrabold text-white font-mono tracking-wider"></span>
                                    @if($var === 'seconds')
                                        <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r {{ $isStayActive ? 'from-green-400 to-emerald-400' : 'from-violet-500 to-indigo-500' }} animate-pulse"></div>
                                    @endif
                                </div>
                                <span class="text-[10px] sm:text-xs font-semibold text-gray-400 mt-2 block uppercase tracking-widest">{{ $label }}</span>
                            </div>
                        @endforeach
                    </div>

                    {{-- Progress bar --}}
                    <div x-show="!finished">
                        <div class="flex justify-between text-xs text-gray-500 mb-1.5 font-medium">
                            <span>{{ $isStayActive ? 'Noch zusammen' : 'Verbleibend' }}</span>
                            <span x-text="Math.round(progress) + '%'" class="font-mono"></span>
                        </div>
                        <div class="h-2.5 bg-gray-100 rounded-full overflow-hidden">
                            <div class="h-full rounded-full transition-all duration-1000
                                {{ $isStayActive
                                    ? 'bg-gradient-to-r from-green-400 to-emerald-500'
                                    : 'bg-gradient-to-r from-violet-400 via-indigo-500 to-blue-500' }}"
                                :style="'width: ' + progress + '%'"></div>
                        </div>
                    </div>

                    {{-- Finished --}}
                    <div x-show="finished" x-cloak class="text-center py-4">
                        <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-emerald-100 mb-3">
                            <svg class="w-8 h-8 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <p class="text-lg font-bold text-emerald-600">Event beendet!</p>
                        <p class="text-sm text-gray-400 mt-1">Countdown wechselt zum nächsten Event...</p>
                    </div>
                </div>

            @elseif($nextEvent)
                {{-- ============ COUNTDOWN (rein Alpine.js) ============ --}}
                <div
                    x-data="{
                        target: {{ $targetTimestamp }},
                        days: 0, hours: 0, minutes: 0, seconds: 0,
                        passed: false,
                        tick() {
                            const diff = this.target - Date.now();
                            if (diff <= 0) {
                                this.passed = true;
                                this.days = this.hours = this.minutes = this.seconds = 0;
                                setTimeout(() => $wire.$refresh(), 2000);
                                return;
                            }
                            this.days = Math.floor(diff / 86400000);
                            this.hours = Math.floor((diff % 86400000) / 3600000);
                            this.minutes = Math.floor((diff % 3600000) / 60000);
                            this.seconds = Math.floor((diff % 60000) / 1000);
                        },
                        init() { this.tick(); setInterval(() => this.tick(), 1000); }
                    }"
                >
                    {{-- Event Info --}}
                    <div class="text-center mb-6">
                        @php $color = \App\Models\Event::typeColor($nextEventType); @endphp
                        <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-medium
                            bg-{{ $color }}-100 text-{{ $color }}-700">
                            {!! \App\Models\Event::typeIcon($nextEventType) !!}
                            {{ $nextEventTitle }}
                        </div>
                        @if($nextEventLocation)
                            <p class="text-sm text-gray-600 mt-2 font-medium flex items-center justify-center gap-1">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                {{ $nextEventLocation }}
                            </p>
                        @endif
                        <p class="text-sm text-gray-400 mt-1">
                            {{ $nextEventDate }} Uhr
                            &middot; {{ $nextEventCreator }}
                        </p>
                    </div>

                    {{-- Timer with colon separators --}}
                    <div x-show="!passed" class="flex items-center justify-center gap-2 sm:gap-3 mb-5">
                        @foreach(['days' => 'Tage', 'hours' => 'Std', 'minutes' => 'Min', 'seconds' => 'Sek'] as $var => $label)
                            @if($var !== 'days')
                                <div class="text-2xl sm:text-3xl font-bold text-indigo-200 -mx-0.5 self-start mt-3 sm:mt-4">:</div>
                            @endif
                            <div class="text-center">
                                <div class="relative bg-gradient-to-b from-slate-800 to-slate-900 rounded-2xl px-3 sm:px-4 py-3 sm:py-4 shadow-lg shadow-slate-300/20 min-w-[60px] sm:min-w-[72px] overflow-hidden">
                                    {{-- Flip-clock divider line --}}
                                    <div class="absolute inset-x-0 top-1/2 h-px bg-white/5"></div>
                                    <span x-text="String({{ $var }}).padStart(2, '0')" class="relative z-10 text-3xl sm:text-4xl font-extrabold text-white font-mono tracking-wider"></span>
                                    @if($var === 'seconds')
                                        <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-violet-500 to-indigo-500 animate-pulse"></div>
                                    @endif
                                </div>
                                <span class="text-[10px] sm:text-xs font-semibold text-gray-400 mt-2 block uppercase tracking-widest">{{ $label }}</span>
                            </div>
                        @endforeach
                    </div>

                    {{-- Passed -> auto refresh --}}
                    <div x-show="passed" x-cloak class="text-center py-4">
                        <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-emerald-100 mb-3">
                            <svg class="w-8 h-8 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <p class="text-lg font-bold text-emerald-600">Es ist soweit!</p>
                        <p class="text-sm text-gray-400 mt-1">Countdown wechselt zum aktiven Event...</p>
                    </div>
                </div>

            @else
                {{-- No events --}}
                <div class="text-center py-8">
                    <div class="inline-flex items-center justify-center w-20 h-20 rounded-2xl bg-gray-50 mb-4">
                        <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <p class="text-gray-500 text-sm font-medium">
                        {{ $filterType ? 'Kein Event für diesen Filter' : 'Kein kommendes Event' }}
                    </p>
                    <p class="text-gray-400 text-xs mt-1">Erstelle ein Event im Kalender für den Countdown</p>
                </div>
            @endif
        </div>
    </div>
</div>
