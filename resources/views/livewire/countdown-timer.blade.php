<div wire:poll.30s>
    <div class="bg-white rounded-2xl shadow-lg shadow-indigo-100/50 overflow-hidden">
        {{-- Header --}}
        <div class="bg-gradient-to-r from-violet-500 via-indigo-500 to-blue-500 px-6 py-5">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-bold text-white flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Countdown
                </h2>
                <span class="text-white/80 text-sm">
                    @if($activeEvent) Gerade aktiv @elseif($nextEvent) bis zum nächsten Event @endif
                </span>
            </div>
        </div>

        {{-- Filter --}}
        <div class="px-6 pt-4 flex items-center gap-2">
            <span class="text-xs text-gray-400 mr-1">Filter:</span>
            <button wire:click="setFilter('flight')"
                class="px-3 py-1.5 rounded-lg text-xs font-medium transition-all duration-200
                    {{ $filterType === 'flight' ? 'bg-sky-500 text-white shadow-md' : 'bg-gray-100 text-gray-500 hover:bg-sky-50 hover:text-sky-600' }}">
                &#9992; Flug
            </button>
            <button wire:click="setFilter('visit')"
                class="px-3 py-1.5 rounded-lg text-xs font-medium transition-all duration-200
                    {{ $filterType === 'visit' ? 'bg-emerald-500 text-white shadow-md' : 'bg-gray-100 text-gray-500 hover:bg-emerald-50 hover:text-emerald-600' }}">
                &#10084; Besuch
            </button>
            <button wire:click="setFilter('date')"
                class="px-3 py-1.5 rounded-lg text-xs font-medium transition-all duration-200
                    {{ $filterType === 'date' ? 'bg-rose-500 text-white shadow-md' : 'bg-gray-100 text-gray-500 hover:bg-rose-50 hover:text-rose-600' }}">
                &#9733; Date
            </button>
        </div>

        <div class="p-6">
            @if($activeEvent)
                {{-- ACTIVE EVENT --}}
                <div class="text-center mb-6">
                    <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-medium bg-emerald-100 text-emerald-700 animate-pulse">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        AKTIV
                    </div>
                    <div class="mt-3 inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-medium
                        @if($activeEvent->type === 'flight') bg-sky-100 text-sky-700
                        @elseif($activeEvent->type === 'visit') bg-emerald-100 text-emerald-700
                        @else bg-rose-100 text-rose-700 @endif">
                        @if($activeEvent->type === 'flight') &#9992;
                        @elseif($activeEvent->type === 'visit') &#10084;
                        @else &#9733; @endif
                        {{ $activeEvent->title }}
                    </div>
                    <p class="text-sm text-gray-500 mt-2">
                        {{ $activeEvent->start_time->format('d.m.Y, H:i') }} &mdash; {{ $activeEvent->end_time->format('H:i') }} Uhr
                    </p>
                </div>

                {{-- Active Progress --}}
                @php
                    $aStart = $activeEvent->start_time->getTimestampMs();
                    $aEnd = $activeEvent->end_time->getTimestampMs();
                @endphp
                <div
                    x-data="{
                        start: {{ $aStart }},
                        end: {{ $aEnd }},
                        progress: 0,
                        remaining: '',
                        tick() {
                            const now = Date.now();
                            const total = this.end - this.start;
                            const elapsed = now - this.start;
                            this.progress = Math.min(100, Math.max(0, (elapsed / total) * 100));
                            const left = Math.max(0, this.end - now);
                            const h = Math.floor(left / 3600000);
                            const m = Math.floor((left % 3600000) / 60000);
                            const s = Math.floor((left % 60000) / 1000);
                            this.remaining = String(h).padStart(2,'0') + ':' + String(m).padStart(2,'0') + ':' + String(s).padStart(2,'0');
                        },
                        init() { this.tick(); setInterval(() => this.tick(), 1000); }
                    }"
                >
                    <div class="flex justify-between text-xs text-gray-500 mb-1">
                        <span>Verbleibend</span>
                        <span x-text="remaining"></span>
                    </div>
                    <div class="h-3 bg-gray-100 rounded-full overflow-hidden">
                        <div class="h-full bg-gradient-to-r from-emerald-400 to-teal-500 rounded-full transition-all duration-1000"
                            :style="'width: ' + progress + '%'"></div>
                    </div>
                </div>

            @elseif($nextEvent)
                {{-- COUNTDOWN to next event --}}
                <div
                    x-data="{
                        target: {{ $targetTimestamp }},
                        days: 0, hours: 0, minutes: 0, seconds: 0,
                        passed: false,
                        tick() {
                            const diff = this.target - Date.now();
                            if (diff <= 0) { this.passed = true; this.days = this.hours = this.minutes = this.seconds = 0; return; }
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
                        <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-medium
                            @if($nextEvent->type === 'flight') bg-sky-100 text-sky-700
                            @elseif($nextEvent->type === 'visit') bg-emerald-100 text-emerald-700
                            @else bg-rose-100 text-rose-700 @endif">
                            @if($nextEvent->type === 'flight') &#9992;
                            @elseif($nextEvent->type === 'visit') &#10084;
                            @else &#9733; @endif
                            {{ $nextEvent->title }}
                        </div>
                        <p class="text-sm text-gray-500 mt-2">
                            {{ $nextEvent->start_time->format('d.m.Y, H:i') }} Uhr
                            &middot; {{ $nextEvent->creator->name }}
                        </p>
                    </div>

                    {{-- Timer --}}
                    <div x-show="!passed" class="grid grid-cols-4 gap-3 mb-4">
                        @foreach(['days' => 'Tage', 'hours' => 'Stunden', 'minutes' => 'Minuten', 'seconds' => 'Sekunden'] as $var => $label)
                        <div class="text-center">
                            <div class="bg-gradient-to-b from-slate-800 to-slate-900 rounded-2xl p-4 shadow-lg shadow-slate-300/30 {{ $var === 'seconds' ? 'relative overflow-hidden' : '' }}">
                                <span x-text="String({{ $var }}).padStart(2, '0')" class="text-4xl font-extrabold text-white font-mono tracking-wider {{ $var === 'seconds' ? 'relative z-10' : '' }}"></span>
                                @if($var === 'seconds')
                                    <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-violet-500 to-indigo-500 animate-pulse"></div>
                                @endif
                            </div>
                            <span class="text-xs font-semibold text-gray-500 mt-2 block uppercase tracking-wider">{{ $label }}</span>
                        </div>
                        @endforeach
                    </div>

                    {{-- Passed --}}
                    <div x-show="passed" x-cloak class="text-center py-4">
                        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-emerald-100 mb-3">
                            <svg class="w-8 h-8 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <p class="text-lg font-bold text-emerald-600">Es ist soweit!</p>
                    </div>
                </div>

            @else
                {{-- No events --}}
                <div class="text-center py-4">
                    <div class="text-gray-300 mb-3">
                        <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <p class="text-gray-400 text-sm">
                        {{ $filterType ? 'Kein Event für diesen Filter' : 'Kein kommendes Event' }}
                    </p>
                    <p class="text-gray-300 text-xs mt-1">Erstelle ein Event im Kalender für den Countdown</p>
                </div>
            @endif
        </div>
    </div>
</div>
