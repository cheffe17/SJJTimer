<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-bold text-xl text-gray-800 leading-tight flex items-center gap-3">
                <svg class="w-6 h-6 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-4 0a1 1 0 01-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 01-1 1"/>
                </svg>
                {{ __('Dashboard') }}
            </h2>

            @if(auth()->user()->couple_id)
                @php $partner = auth()->user()->partner(); @endphp
                @if($partner)
                    <div class="flex items-center gap-3 px-4 py-2 rounded-xl bg-gradient-to-r from-pink-50 to-rose-50 border border-pink-100">
                        <svg class="w-5 h-5 text-pink-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                        </svg>
                        <span class="text-sm font-semibold text-gray-600">Verbunden mit:</span>
                        <span class="text-sm font-extrabold bg-gradient-to-r from-pink-500 to-rose-500 bg-clip-text text-transparent">
                            {{ $partner->name }}
                        </span>
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
