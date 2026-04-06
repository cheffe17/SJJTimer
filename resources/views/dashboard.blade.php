<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-bold text-xl text-gray-800 leading-tight flex items-center gap-3">
                    Willkommen zurück{{ auth()->user()->name ? ', ' . Str::before(auth()->user()->name, ' ') : '' }}
                </h2>
                <p class="text-sm text-gray-400 mt-0.5 font-medium">
                    {{ now()->locale('de')->isoFormat('dddd, D. MMMM YYYY') }}
                </p>
            </div>

            @if(auth()->user()->couple_id)
                @php $partner = auth()->user()->partner(); @endphp
                @if($partner)
                    <div class="flex items-center gap-3 px-4 py-2.5 rounded-xl bg-gradient-to-r from-pink-50 to-rose-50 border border-pink-100/80 shadow-sm shadow-pink-100/30">
                        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-pink-400 to-rose-500 flex items-center justify-center text-white text-xs font-bold shadow-sm">
                            {{ Str::upper(Str::substr($partner->name, 0, 1)) }}
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 font-medium leading-none">Verbunden mit</p>
                            <p class="text-sm font-extrabold bg-gradient-to-r from-pink-500 to-rose-500 bg-clip-text text-transparent leading-tight">
                                {{ $partner->name }}
                            </p>
                        </div>
                    </div>
                @endif
            @endif
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            {{-- Top Row: Timer + Partner Connect --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2">
                    <livewire:countdown-timer />
                </div>
                <div class="lg:col-span-1">
                    <livewire:partner-connect />
                </div>
            </div>

            {{-- Calendar --}}
            <livewire:calendar />
        </div>
    </div>
</x-app-layout>
