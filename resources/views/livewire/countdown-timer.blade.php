<div>
    @if($nextEvent)
        <div
            x-data="{
                target: {{ $targetTimestamp }},
                days: 0,
                hours: 0,
                minutes: 0,
                seconds: 0,
                passed: false,
                tick() {
                    const diff = this.target - Date.now();
                    if (diff <= 0) {
                        this.passed = true;
                        this.days = this.hours = this.minutes = this.seconds = 0;
                        return;
                    }
                    this.days = Math.floor(diff / 86400000);
                    this.hours = Math.floor((diff % 86400000) / 3600000);
                    this.minutes = Math.floor((diff % 3600000) / 60000);
                    this.seconds = Math.floor((diff % 60000) / 1000);
                },
                init() {
                    this.tick();
                    setInterval(() => this.tick(), 1000);
                }
            }"
            class="bg-white rounded-2xl shadow-lg shadow-indigo-100/50 overflow-hidden"
        >
            {{-- Header --}}
            <div class="bg-gradient-to-r from-violet-500 via-indigo-500 to-blue-500 px-6 py-5">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-bold text-white flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Countdown
                    </h2>
                    <span class="text-white/80 text-sm">bis zum nächsten Event</span>
                </div>
            </div>

            <div class="p-6">
                {{-- Event Info --}}
                <div class="text-center mb-6">
                    <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-medium
                        @if($nextEvent->type === 'flight')
                            bg-sky-100 text-sky-700
                        @elseif($nextEvent->type === 'visit')
                            bg-emerald-100 text-emerald-700
                        @else
                            bg-rose-100 text-rose-700
                        @endif">
                        @if($nextEvent->type === 'flight')
                            &#9992;
                        @elseif($nextEvent->type === 'visit')
                            &#10084;
                        @else
                            &#9733;
                        @endif
                        {{ $nextEvent->title }}
                    </div>
                    <p class="text-sm text-gray-500 mt-2">
                        {{ $nextEvent->start_time->format('d.m.Y, H:i') }} Uhr
                        &middot; erstellt von <span class="font-semibold text-gray-700">{{ $nextEvent->creator->name }}</span>
                    </p>
                </div>

                {{-- Timer Display --}}
                <div x-show="!passed" class="grid grid-cols-4 gap-3 mb-6">
                    <div class="text-center">
                        <div class="bg-gradient-to-b from-slate-800 to-slate-900 rounded-2xl p-4 shadow-lg shadow-slate-300/30">
                            <span x-text="String(days).padStart(2, '0')" class="text-4xl font-extrabold text-white font-mono tracking-wider"></span>
                        </div>
                        <span class="text-xs font-semibold text-gray-500 mt-2 block uppercase tracking-wider">Tage</span>
                    </div>
                    <div class="text-center">
                        <div class="bg-gradient-to-b from-slate-800 to-slate-900 rounded-2xl p-4 shadow-lg shadow-slate-300/30">
                            <span x-text="String(hours).padStart(2, '0')" class="text-4xl font-extrabold text-white font-mono tracking-wider"></span>
                        </div>
                        <span class="text-xs font-semibold text-gray-500 mt-2 block uppercase tracking-wider">Stunden</span>
                    </div>
                    <div class="text-center">
                        <div class="bg-gradient-to-b from-slate-800 to-slate-900 rounded-2xl p-4 shadow-lg shadow-slate-300/30">
                            <span x-text="String(minutes).padStart(2, '0')" class="text-4xl font-extrabold text-white font-mono tracking-wider"></span>
                        </div>
                        <span class="text-xs font-semibold text-gray-500 mt-2 block uppercase tracking-wider">Minuten</span>
                    </div>
                    <div class="text-center">
                        <div class="bg-gradient-to-b from-slate-800 to-slate-900 rounded-2xl p-4 shadow-lg shadow-slate-300/30 relative overflow-hidden">
                            <span x-text="String(seconds).padStart(2, '0')" class="text-4xl font-extrabold text-white font-mono tracking-wider relative z-10"></span>
                            <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-violet-500 to-indigo-500 animate-pulse"></div>
                        </div>
                        <span class="text-xs font-semibold text-gray-500 mt-2 block uppercase tracking-wider">Sekunden</span>
                    </div>
                </div>

                {{-- Progress Bar --}}
                @if($nextEvent->tracking_start)
                    @php
                        $start = $nextEvent->tracking_start->getTimestampMs();
                        $end = $nextEvent->start_time->getTimestampMs();
                    @endphp
                    <div
                        x-data="{
                            start: {{ $start }},
                            end: {{ $end }},
                            progress: 0,
                            update() {
                                const now = Date.now();
                                const total = this.end - this.start;
                                const elapsed = now - this.start;
                                this.progress = Math.min(100, Math.max(0, (elapsed / total) * 100));
                            },
                            init() {
                                this.update();
                                setInterval(() => this.update(), 1000);
                            }
                        }"
                        class="mt-2"
                    >
                        <div class="flex justify-between text-xs text-gray-500 mb-1">
                            <span>Fortschritt</span>
                            <span x-text="progress.toFixed(1) + '%'"></span>
                        </div>
                        <div class="h-3 bg-gray-100 rounded-full overflow-hidden">
                            <div
                                class="h-full bg-gradient-to-r from-violet-500 via-indigo-500 to-blue-500 rounded-full transition-all duration-1000"
                                :style="'width: ' + progress + '%'"
                            ></div>
                        </div>
                    </div>
                @endif

                {{-- Event passed --}}
                <div x-show="passed" x-cloak class="text-center py-4">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-emerald-100 mb-3">
                        <svg class="w-8 h-8 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <p class="text-lg font-bold text-emerald-600">Es ist soweit!</p>
                </div>
            </div>
        </div>
    @else
        {{-- No upcoming events --}}
        <div class="bg-white rounded-2xl shadow-lg shadow-indigo-100/50 overflow-hidden">
            <div class="bg-gradient-to-r from-violet-500 via-indigo-500 to-blue-500 px-6 py-5">
                <h2 class="text-lg font-bold text-white flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Countdown
                </h2>
            </div>
            <div class="p-8 text-center">
                <div class="text-gray-300 mb-3">
                    <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <p class="text-gray-400 text-sm">Kein kommendes Event</p>
                <p class="text-gray-300 text-xs mt-1">Erstelle ein Event im Kalender für den Countdown</p>
            </div>
        </div>
    @endif
</div>
