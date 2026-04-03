<div class="bg-white rounded-2xl shadow-lg shadow-indigo-100/50 overflow-hidden">
    <div class="bg-gradient-to-r from-pink-500 via-rose-500 to-orange-400 px-6 py-5">
        <h2 class="text-lg font-bold text-white flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
            </svg>
            Partner verbinden
        </h2>
    </div>

    <div class="p-6">
        @if($isConnected)
            {{-- Connected State --}}
            <div class="text-center space-y-3">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gradient-to-br from-emerald-100 to-teal-100">
                    <svg class="w-8 h-8 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <p class="text-sm text-gray-500">Verbunden mit</p>
                <p class="text-2xl font-extrabold bg-gradient-to-r from-pink-500 to-rose-500 bg-clip-text text-transparent">
                    {{ $partner?->name ?? 'Partner' }}
                </p>
            </div>
        @else
            {{-- Invite via Email --}}
            <div class="space-y-4">
                <p class="text-sm text-gray-600">Lade deinen Partner per E-Mail ein:</p>
                <form wire:submit="sendInvitation" class="space-y-3">
                    <input
                        type="email"
                        wire:model="partnerEmail"
                        placeholder="partner@email.com"
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-pink-400 focus:ring-4 focus:ring-pink-100 transition-all duration-200 text-sm text-gray-700"
                    />
                    @error('partnerEmail') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    <button type="submit" class="w-full py-3 rounded-xl bg-gradient-to-r from-pink-500 to-rose-500 text-white font-semibold shadow-lg shadow-pink-200/50 hover:shadow-xl hover:-translate-y-0.5 transition-all duration-200 flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        Einladung senden
                    </button>
                </form>

                @if($statusMessage)
                    <div class="p-3 rounded-xl text-sm font-medium
                        {{ $statusType === 'success' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : '' }}
                        {{ $statusType === 'error' ? 'bg-red-50 text-red-700 border border-red-200' : '' }}
                        {{ $statusType === 'warning' ? 'bg-amber-50 text-amber-700 border border-amber-200' : '' }}">
                        {{ $statusMessage }}
                    </div>
                @endif
            </div>
        @endif
    </div>
</div>
