<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Partner-Verbindung') }}
        </h2>
        <p class="mt-1 text-sm text-gray-600">
            {{ __('Verwalte die Verbindung mit deinem Partner.') }}
        </p>
    </header>

    @if($isConnected && $partner)
        <div class="mt-6 space-y-4">
            <div class="flex items-center gap-4 p-4 rounded-xl bg-gradient-to-r from-pink-50 to-rose-50 border border-pink-100">
                <div class="flex-shrink-0 w-12 h-12 rounded-full bg-gradient-to-br from-pink-200 to-rose-200 flex items-center justify-center">
                    <svg class="w-6 h-6 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Verbunden mit</p>
                    <p class="text-lg font-bold bg-gradient-to-r from-pink-500 to-rose-500 bg-clip-text text-transparent">
                        {{ $partner->name }}
                    </p>
                    <p class="text-xs text-gray-400">{{ $partner->email }}</p>
                </div>
            </div>

            <div class="p-4 rounded-xl bg-red-50 border border-red-100">
                <p class="text-sm text-red-600 mb-3">
                    Wenn du die Verbindung trennst, teilt ihr keinen Kalender mehr. Eure Events bleiben erhalten, sind aber nicht mehr füreinander sichtbar.
                </p>
                <button
                    wire:click="disconnect"
                    wire:confirm="Verbindung wirklich trennen?"
                    class="px-5 py-2.5 rounded-xl text-sm font-semibold text-white bg-red-500 hover:bg-red-600 shadow-sm transition-all duration-200"
                >
                    Verbindung trennen
                </button>
            </div>
        </div>
    @else
        <div class="mt-6 p-4 rounded-xl bg-gray-50 border border-gray-100">
            <p class="text-sm text-gray-500">
                Du bist aktuell mit niemandem verbunden. Gehe zum
                <a href="{{ route('dashboard') }}" class="text-indigo-500 font-medium hover:underline" wire:navigate>Dashboard</a>,
                um eine Einladung zu senden.
            </p>
        </div>
    @endif
</section>
