<div class="space-y-8" wire:poll.10s>
    {{-- Calendar --}}
    <div class="bg-white rounded-2xl shadow-lg shadow-indigo-100/50 overflow-hidden">
        {{-- Header with navigation and view switcher --}}
        <div class="bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 px-6 py-5">
            <div class="flex items-center justify-between mb-3">
                <button wire:click="previousPeriod" class="p-2 rounded-xl bg-white/20 hover:bg-white/30 text-white transition-all duration-200 backdrop-blur-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </button>
                <div class="text-center">
                    <h2 class="text-xl font-bold text-white tracking-wide">{{ $periodLabel }}</h2>
                </div>
                <button wire:click="nextPeriod" class="p-2 rounded-xl bg-white/20 hover:bg-white/30 text-white transition-all duration-200 backdrop-blur-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
            </div>
            {{-- View Switcher --}}
            <div class="flex items-center justify-center gap-1">
                <button wire:click="goToToday" class="px-3 py-1.5 rounded-lg text-xs font-medium text-white/80 hover:bg-white/20 transition-all duration-200 mr-2">
                    Heute
                </button>
                @foreach(['year' => 'Jahr', 'month' => 'Monat', 'week' => 'Woche', 'day' => 'Tag'] as $v => $label)
                    <button wire:click="setView('{{ $v }}')"
                        class="px-3 py-1.5 rounded-lg text-xs font-medium transition-all duration-200
                            {{ $view === $v ? 'bg-white text-indigo-600 shadow-md' : 'text-white/80 hover:bg-white/20' }}">
                        {{ $label }}
                    </button>
                @endforeach
            </div>
        </div>

        {{-- Active Events Banner --}}
        @if($activeEvents->isNotEmpty())
            <div class="bg-emerald-50 border-b border-emerald-100 px-6 py-3">
                <div class="flex items-center gap-2 mb-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span class="text-sm font-semibold text-emerald-700">Gerade aktiv</span>
                </div>
                <div class="flex flex-wrap gap-2">
                    @foreach($activeEvents as $event)
                        @php $color = \App\Models\Event::typeColor($event->type); @endphp
                        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-medium
                            bg-{{ $color }}-100 text-{{ $color }}-700">
                            {!! \App\Models\Event::typeIcon($event->type) !!}
                            {{ $event->title }}
                            <span class="opacity-60">{{ $event->start_time->format('H:i') }}&ndash;{{ $event->end_time->format('H:i') }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- ======================== YEAR VIEW ======================== --}}
        @if($view === 'year')
            <div class="p-6">
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                    @foreach($yearMonths as $month)
                        <div>
                            <h3 class="text-sm font-bold text-gray-700 mb-2">{{ $month['name'] }}</h3>
                            <div class="grid grid-cols-7 gap-px text-center">
                                @foreach(['M','D','M','D','F','S','S'] as $d)
                                    <div class="text-[10px] text-gray-400 font-medium py-0.5">{{ $d }}</div>
                                @endforeach
                                @foreach($month['days'] as $day)
                                    @if($day === null)
                                        <div class="w-6 h-6"></div>
                                    @else
                                        <div
                                            class="w-6 h-6 flex items-center justify-center text-[11px] rounded-full cursor-pointer transition-all
                                                {{ $day['isToday'] ? 'bg-indigo-500 text-white font-bold' : '' }}
                                                {{ $day['hasEvents'] && !$day['isToday'] ? 'bg-purple-100 text-purple-700 font-semibold' : '' }}
                                                {{ !$day['isToday'] && !$day['hasEvents'] ? 'text-gray-600 hover:bg-gray-100' : '' }}"
                                            wire:click="openModal('{{ $day['date'] }}')"
                                        >
                                            {{ $day['day'] }}
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

        {{-- ======================== MONTH VIEW ======================== --}}
        @elseif($view === 'month')
            <div class="grid grid-cols-7 bg-gray-50 border-b border-gray-100">
                @foreach(['Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa', 'So'] as $day)
                    <div class="py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ $day }}</div>
                @endforeach
            </div>

            <div class="grid grid-cols-7">
                @foreach($calendarDays as $calDay)
                    @if($calDay === null)
                        <div class="min-h-[100px] border-b border-r border-gray-50"></div>
                    @else
                        <div
                            wire:click="openModal('{{ $calDay['date'] }}')"
                            class="min-h-[100px] border-b border-r border-gray-50 p-2 cursor-pointer transition-all duration-200 hover:bg-indigo-50/50 group
                                {{ $calDay['isToday'] ? 'bg-indigo-50/80' : '' }}"
                        >
                            <span class="inline-flex items-center justify-center w-7 h-7 text-sm rounded-full transition-all duration-200
                                {{ $calDay['isToday']
                                    ? 'bg-indigo-500 text-white font-bold shadow-md shadow-indigo-200'
                                    : 'text-gray-700 group-hover:bg-indigo-100 group-hover:text-indigo-700' }}">
                                {{ $calDay['day'] }}
                            </span>
                            <div class="mt-1 space-y-1">
                                @foreach($calDay['events'] as $event)
                                    @php $color = \App\Models\Event::typeColor($event->type); @endphp
                                    <div
                                        wire:click.stop="{{ $event->created_by === $currentUserId ? 'editEvent('.$event->id.')' : '' }}"
                                        class="text-xs px-2 py-1 rounded-lg truncate font-medium transition-all duration-200 hover:shadow-sm relative
                                            {{ $event->created_by === $currentUserId ? 'cursor-pointer hover:scale-[1.02]' : 'cursor-default' }}
                                            bg-{{ $color }}-100 text-{{ $color }}-700"
                                        title="{{ $event->title }} — {{ $event->creator->name }}"
                                    >
                                        {!! \App\Models\Event::typeIcon($event->type) !!}
                                        {{ $event->title }}
                                        @if($event->created_by !== $currentUserId)
                                            <span class="text-[10px] opacity-60">&middot; {{ $event->creator->name }}</span>
                                        @endif
                                        @if($event->shared)
                                            <span class="text-[10px] opacity-50">&#128107;</span>
                                        @endif
                                        @if($event->isRecurring())
                                            <span class="text-[10px] opacity-50">&#128260;</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>

        {{-- ======================== WEEK VIEW ======================== --}}
        @elseif($view === 'week')
            <div class="overflow-x-auto">
                <div class="grid grid-cols-8 border-b border-gray-100 min-w-[800px]">
                    <div class="py-3 px-2 text-xs text-gray-400 font-medium"></div>
                    @foreach($weekData['days'] as $day)
                        <div class="py-3 px-2 text-center border-l border-gray-100
                            {{ $day['isToday'] ? 'bg-indigo-50' : '' }}">
                            <div class="text-xs font-semibold {{ $day['isToday'] ? 'text-indigo-600' : 'text-gray-500' }}">
                                {{ $day['label'] }}
                            </div>
                            @if($day['events']->isNotEmpty())
                                <div class="flex justify-center gap-0.5 mt-1">
                                    @foreach($day['events']->take(3) as $event)
                                        @php $color = \App\Models\Event::typeColor($event->type); @endphp
                                        <span class="w-1.5 h-1.5 rounded-full bg-{{ $color }}-400"></span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>

                <div class="min-w-[800px]" style="max-height: 600px; overflow-y: auto;">
                    @foreach($weekData['hours'] as $hour)
                        <div class="grid grid-cols-8 border-b border-gray-50 min-h-[50px]">
                            <div class="py-1 px-2 text-[11px] text-gray-400 text-right pr-3 pt-1">
                                {{ str_pad($hour, 2, '0', STR_PAD_LEFT) }}:00
                            </div>
                            @foreach($weekData['days'] as $day)
                                <div class="border-l border-gray-50 p-0.5 cursor-pointer hover:bg-indigo-50/30 transition-colors
                                    {{ $day['isToday'] ? 'bg-indigo-50/30' : '' }}"
                                    wire:click="openModal('{{ $day['date']->format('Y-m-d') }}')">
                                    @foreach($day['events'] as $event)
                                        @if($event->start_time->hour === $hour)
                                            @php $color = \App\Models\Event::typeColor($event->type); @endphp
                                            <div
                                                wire:click.stop="{{ $event->created_by === $currentUserId ? 'editEvent('.$event->id.')' : '' }}"
                                                class="text-[10px] px-1.5 py-0.5 rounded truncate font-medium mb-0.5
                                                    {{ $event->created_by === $currentUserId ? 'cursor-pointer' : 'cursor-default' }}
                                                    bg-{{ $color }}-100 text-{{ $color }}-700">
                                                {{ $event->start_time->format('H:i') }} {{ $event->title }}
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </div>

        {{-- ======================== DAY VIEW ======================== --}}
        @elseif($view === 'day')
            <div class="p-4">
                <h3 class="text-lg font-semibold text-gray-700 mb-4">{{ $dayData['label'] }}</h3>

                @if($dayData['events']->isNotEmpty())
                    <div class="flex flex-wrap gap-2 mb-4">
                        @foreach($dayData['events'] as $event)
                            @php $color = \App\Models\Event::typeColor($event->type); @endphp
                            <div
                                wire:click="{{ $event->created_by === $currentUserId ? 'editEvent('.$event->id.')' : '' }}"
                                class="inline-flex items-center gap-2 px-3 py-2 rounded-xl text-sm font-medium
                                    {{ $event->created_by === $currentUserId ? 'cursor-pointer hover:shadow-md' : 'cursor-default' }}
                                    bg-{{ $color }}-100 text-{{ $color }}-700">
                                {!! \App\Models\Event::typeIcon($event->type) !!}
                                {{ $event->title }}
                                <span class="opacity-60 text-xs">{{ $event->start_time->format('H:i') }}@if($event->end_time)&ndash;{{ $event->end_time->format('H:i') }}@endif</span>
                            </div>
                        @endforeach
                    </div>
                @endif

                <div style="max-height: 600px; overflow-y: auto;">
                    @foreach($dayData['hours'] as $hour)
                        <div class="flex border-b border-gray-50 min-h-[50px] hover:bg-indigo-50/30 transition-colors cursor-pointer"
                            wire:click="openModal('{{ $dayData['date']->format('Y-m-d') }}')">
                            <div class="w-16 flex-shrink-0 py-1 text-right pr-3 text-xs text-gray-400 pt-1">
                                {{ str_pad($hour, 2, '0', STR_PAD_LEFT) }}:00
                            </div>
                            <div class="flex-1 border-l border-gray-100 p-1">
                                @foreach($dayData['events'] as $event)
                                    @if($event->start_time->hour === $hour)
                                        @php $color = \App\Models\Event::typeColor($event->type); @endphp
                                        <div
                                            wire:click.stop="{{ $event->created_by === $currentUserId ? 'editEvent('.$event->id.')' : '' }}"
                                            class="text-xs px-3 py-2 rounded-lg font-medium mb-1
                                                {{ $event->created_by === $currentUserId ? 'cursor-pointer hover:shadow-md' : 'cursor-default' }}
                                                bg-{{ $color }}-100 text-{{ $color }}-700">
                                            {!! \App\Models\Event::typeIcon($event->type) !!}
                                            {{ $event->title }}
                                            <span class="opacity-60">{{ $event->start_time->format('H:i') }}@if($event->end_time) &ndash; {{ $event->end_time->format('H:i') }}@endif</span>
                                            @if($event->created_by !== $currentUserId)
                                                <span class="opacity-50 ml-1">&middot; {{ $event->creator->name }}</span>
                                            @endif
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    {{-- Bottom: Add + Upcoming --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-1">
            <button
                wire:click="openModal"
                class="w-full bg-gradient-to-r from-indigo-500 to-purple-600 text-white rounded-2xl shadow-lg shadow-indigo-200/50 p-6 hover:shadow-xl hover:shadow-indigo-200/60 transition-all duration-300 hover:-translate-y-0.5 group"
            >
                <div class="flex items-center justify-center space-x-3">
                    <svg class="w-6 h-6 group-hover:rotate-90 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span class="text-lg font-semibold">Neues Event</span>
                </div>
            </button>
        </div>

        <div class="lg:col-span-2">
            <div class="bg-white rounded-2xl shadow-lg shadow-indigo-100/50 p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    Kommende Events
                </h3>

                @if($upcomingEvents->isEmpty())
                    <div class="text-center py-8">
                        <div class="text-gray-300 mb-3">
                            <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <p class="text-gray-400 text-sm">Noch keine Events geplant</p>
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach($upcomingEvents as $event)
                            @php $color = \App\Models\Event::typeColor($event->type); @endphp
                            <div class="flex items-center justify-between p-4 rounded-xl border border-gray-100 hover:border-indigo-200 hover:shadow-md transition-all duration-200 group">
                                <div class="flex items-center space-x-4">
                                    <div class="flex-shrink-0 w-12 h-12 rounded-xl flex items-center justify-center text-lg
                                        bg-{{ $color }}-100 text-{{ $color }}-600">
                                        {!! \App\Models\Event::typeIcon($event->type) !!}
                                    </div>
                                    <div>
                                        <p class="font-semibold text-gray-800">{{ $event->title }}</p>
                                        <p class="text-sm text-gray-500">
                                            {{ $event->start_time->format('d.m.Y, H:i') }} Uhr
                                            @if($event->end_time)
                                                &mdash; {{ $event->end_time->format('d.m.Y, H:i') }} Uhr
                                            @endif
                                        </p>
                                        <div class="flex items-center gap-2 mt-0.5">
                                            <span class="text-xs {{ $event->created_by === $currentUserId ? 'text-indigo-500' : 'text-pink-500' }}">
                                                {{ $event->created_by === $currentUserId ? 'Von dir' : 'Von ' . $event->creator->name }}
                                            </span>
                                            @if($event->shared)
                                                <span class="text-xs text-purple-500 flex items-center gap-0.5">
                                                    &#128107; Gemeinsam
                                                </span>
                                            @else
                                                <span class="text-xs text-gray-400">Solo</span>
                                            @endif
                                            @if($event->isRecurring())
                                                <span class="text-xs text-indigo-400">&#128260; Serie</span>
                                            @endif
                                            @if($event->isVisitWithFlights())
                                                <span class="text-xs text-sky-400">&#9992; mit Flügen</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @if($event->created_by === $currentUserId)
                                    <div class="flex items-center space-x-2 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                        <button wire:click="editEvent({{ $event->id }})" class="p-2 rounded-lg text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </button>
                                        <button wire:click="deleteEvent({{ $event->id }})" wire:confirm="Event wirklich löschen?" class="p-2 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </div>
                                @else
                                    <div class="px-3 py-1 rounded-full bg-pink-50 text-pink-500 text-xs font-medium">
                                        {{ $event->creator->name }}
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ======================== MODAL ======================== --}}
    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4"
            x-data="{ showRecurrence: {{ $recurrence_rule ? 'true' : 'false' }} }"
            x-init="$el.querySelector('input[type=text]')?.focus()">
            <div wire:click="closeModal" class="absolute inset-0 bg-gray-900/40 backdrop-blur-sm"></div>

            <div class="relative bg-white rounded-3xl shadow-2xl shadow-indigo-200/50 w-full max-w-lg max-h-[90vh] overflow-y-auto">
                <div class="bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 rounded-t-3xl px-6 py-5">
                    <h3 class="text-lg font-bold text-white">
                        {{ $editingEventId ? 'Event bearbeiten' : 'Neues Event erstellen' }}
                    </h3>
                </div>

                <form wire:submit="save" class="p-6 space-y-5">
                    {{-- Title --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Titel</label>
                        <input type="text" wire:model="title" placeholder="z.B. Besuch in Wien"
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-400 focus:ring-4 focus:ring-indigo-100 transition-all duration-200 text-gray-700 placeholder-gray-400" />
                        @error('title') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>

                    {{-- Type --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Kategorie</label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="cursor-pointer">
                                <input type="radio" wire:model.live="type" value="visit" class="peer hidden" />
                                <div class="peer-checked:border-sky-400 peer-checked:bg-sky-50 peer-checked:text-sky-700 border-2 border-gray-200 rounded-xl p-3 text-center text-sm font-medium text-gray-500 transition-all duration-200 hover:border-gray-300">
                                    <div class="text-xl mb-1">&#9992;</div>Besuch
                                </div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" wire:model.live="type" value="virtual_date" class="peer hidden" />
                                <div class="peer-checked:border-violet-400 peer-checked:bg-violet-50 peer-checked:text-violet-700 border-2 border-gray-200 rounded-xl p-3 text-center text-sm font-medium text-gray-500 transition-all duration-200 hover:border-gray-300">
                                    <div class="text-xl mb-1">&#128187;</div>Virtuelles Date
                                </div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" wire:model.live="type" value="live_date" class="peer hidden" />
                                <div class="peer-checked:border-rose-400 peer-checked:bg-rose-50 peer-checked:text-rose-700 border-2 border-gray-200 rounded-xl p-3 text-center text-sm font-medium text-gray-500 transition-all duration-200 hover:border-gray-300">
                                    <div class="text-xl mb-1">&#10084;</div>Live Date
                                </div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" wire:model.live="type" value="anniversary" class="peer hidden" />
                                <div class="peer-checked:border-amber-400 peer-checked:bg-amber-50 peer-checked:text-amber-700 border-2 border-gray-200 rounded-xl p-3 text-center text-sm font-medium text-gray-500 transition-all duration-200 hover:border-gray-300">
                                    <div class="text-xl mb-1">&#127874;</div>Jahrestag
                                </div>
                            </label>
                        </div>
                    </div>

                    {{-- Shared toggle --}}
                    @if($partnerName)
                        <div>
                            <label class="flex items-center justify-between p-4 rounded-xl border-2 transition-all duration-200 cursor-pointer
                                {{ $shared ? 'border-purple-300 bg-purple-50' : 'border-gray-200 bg-white' }}">
                                <div class="flex items-center gap-3">
                                    <span class="text-lg">&#128107;</span>
                                    <div>
                                        <p class="text-sm font-semibold {{ $shared ? 'text-purple-700' : 'text-gray-700' }}">
                                            Gemeinsam mit {{ $partnerName }}
                                        </p>
                                        <p class="text-xs {{ $shared ? 'text-purple-500' : 'text-gray-400' }}">
                                            {{ $shared ? 'Ihr macht das zusammen' : 'Nur für dich' }}
                                        </p>
                                    </div>
                                </div>
                                <div class="relative">
                                    <input type="checkbox" wire:model.live="shared" class="sr-only peer" />
                                    <div class="w-11 h-6 bg-gray-200 peer-checked:bg-purple-500 rounded-full transition-colors duration-200"></div>
                                    <div class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow-sm transition-transform duration-200 peer-checked:translate-x-5"></div>
                                </div>
                            </label>
                        </div>
                    @endif

                    {{-- Start Time (always visible) --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Startzeit</label>
                        <input type="datetime-local" wire:model="start_time"
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-400 focus:ring-4 focus:ring-indigo-100 transition-all duration-200 text-gray-700" />
                        @error('start_time') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>

                    {{-- End Time (always visible) --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Endzeit <span class="text-gray-400 font-normal">(optional, Standard: 3 Stunden)</span></label>
                        <input type="datetime-local" wire:model="end_time"
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-400 focus:ring-4 focus:ring-indigo-100 transition-all duration-200 text-gray-700" />
                        @error('end_time') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>

                    {{-- Flight fields (only for visit type) --}}
                    @if($type === 'visit')
                        <div class="border-2 border-sky-200 bg-sky-50/50 rounded-xl p-4 space-y-4">
                            <div class="flex items-center gap-2 text-sky-700 text-sm font-semibold">
                                <span>&#9992;</span> Flugdaten <span class="text-xs font-normal text-sky-500">(optional)</span>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-sky-700 mb-1">Hinflug</label>
                                <input type="datetime-local" wire:model="outbound_flight_time"
                                    class="w-full px-3 py-2 rounded-lg border border-sky-200 focus:border-sky-400 focus:ring-2 focus:ring-sky-100 transition-all text-sm text-gray-700" />
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-sky-700 mb-1">Rückflug</label>
                                <input type="datetime-local" wire:model="return_flight_time"
                                    class="w-full px-3 py-2 rounded-lg border border-sky-200 focus:border-sky-400 focus:ring-2 focus:ring-sky-100 transition-all text-sm text-gray-700" />
                            </div>
                            <p class="text-[11px] text-sky-500">
                                Der Zeitraum zwischen Hin- und Rückflug wird automatisch als Besuchszeit eingetragen. Der Countdown wechselt bei Ankunft auf die verbleibende Besuchszeit.
                            </p>
                        </div>
                    @endif

                    {{-- Recurring (collapsible) --}}
                    <div class="border-2 rounded-xl transition-all duration-200
                        {{ $recurrence_rule ? 'border-indigo-300 bg-indigo-50/50' : 'border-gray-200' }}">
                        <button type="button"
                            @click="showRecurrence = !showRecurrence"
                            class="w-full flex items-center justify-between p-4 text-sm font-semibold transition-colors
                                {{ $recurrence_rule ? 'text-indigo-700' : 'text-gray-600' }}">
                            <div class="flex items-center gap-2">
                                <span>&#128260;</span> Serientermin <span class="text-xs font-normal {{ $recurrence_rule ? 'text-indigo-500' : 'text-gray-400' }}">(optional)</span>
                            </div>
                            <svg class="w-4 h-4 transition-transform duration-200" :class="showRecurrence ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>

                        <div x-show="showRecurrence" x-collapse class="px-4 pb-4 space-y-3">
                            <div>
                                <label class="block text-xs font-medium text-indigo-700 mb-1">Wiederholung</label>
                                <select wire:model.live="recurrence_rule"
                                    class="w-full px-3 py-2 rounded-lg border border-indigo-200 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all text-sm text-gray-700 bg-white">
                                    <option value="">Keine Wiederholung</option>
                                    <option value="weekly">Wöchentlich</option>
                                    <option value="biweekly">Alle 2 Wochen</option>
                                    <option value="monthly">Monatlich</option>
                                    <option value="yearly">Jährlich</option>
                                </select>
                            </div>

                            @if($recurrence_rule)
                                {{-- Wochentag only for weekly/biweekly --}}
                                @if(in_array($recurrence_rule, ['weekly', 'biweekly']))
                                    <div class="grid grid-cols-2 gap-3">
                                        <div>
                                            <label class="block text-xs font-medium text-indigo-700 mb-1">Wochentag</label>
                                            <select wire:model="recurrence_day"
                                                class="w-full px-3 py-2 rounded-lg border border-indigo-200 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all text-sm text-gray-700 bg-white">
                                                <option value="">Wie Startdatum</option>
                                                <option value="monday">Montag</option>
                                                <option value="tuesday">Dienstag</option>
                                                <option value="wednesday">Mittwoch</option>
                                                <option value="thursday">Donnerstag</option>
                                                <option value="friday">Freitag</option>
                                                <option value="saturday">Samstag</option>
                                                <option value="sunday">Sonntag</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-indigo-700 mb-1">Uhrzeit</label>
                                            <input type="time" wire:model="recurrence_time"
                                                class="w-full px-3 py-2 rounded-lg border border-indigo-200 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all text-sm text-gray-700" />
                                        </div>
                                    </div>
                                @else
                                    {{-- For monthly/yearly: only time field --}}
                                    <div>
                                        <label class="block text-xs font-medium text-indigo-700 mb-1">Uhrzeit</label>
                                        <input type="time" wire:model="recurrence_time"
                                            class="w-full px-3 py-2 rounded-lg border border-indigo-200 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all text-sm text-gray-700" />
                                    </div>
                                @endif

                                <div>
                                    <label class="block text-xs font-medium text-indigo-700 mb-1">Wiederholen bis <span class="text-indigo-400 font-normal">(optional)</span></label>
                                    <input type="date" wire:model="recurrence_until"
                                        class="w-full px-3 py-2 rounded-lg border border-indigo-200 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition-all text-sm text-gray-700" />
                                </div>

                                {{-- Info text about first occurrence --}}
                                <p class="text-[11px] text-indigo-500">
                                    Das erste Event findet am eingegebenen Startdatum statt. Alle weiteren Events folgen dem Serienrhythmus{{ in_array($recurrence_rule, ['weekly', 'biweekly']) && $recurrence_day ? ' (jeden ' . ['monday' => 'Montag', 'tuesday' => 'Dienstag', 'wednesday' => 'Mittwoch', 'thursday' => 'Donnerstag', 'friday' => 'Freitag', 'saturday' => 'Samstag', 'sunday' => 'Sonntag'][$recurrence_day] . ')' : '' }}.
                                </p>
                            @endif
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="flex items-center justify-end space-x-3 pt-2">
                        <button type="button" wire:click="closeModal" class="px-6 py-3 rounded-xl text-gray-600 font-medium hover:bg-gray-100 transition-all duration-200">
                            Abbrechen
                        </button>
                        <button type="submit" class="px-6 py-3 rounded-xl bg-gradient-to-r from-indigo-500 to-purple-600 text-white font-semibold shadow-lg shadow-indigo-200/50 hover:shadow-xl hover:-translate-y-0.5 transition-all duration-200">
                            {{ $editingEventId ? 'Speichern' : 'Erstellen' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
