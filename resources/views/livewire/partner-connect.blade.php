<div class="bg-white rounded-2xl shadow-lg shadow-indigo-100/50 overflow-hidden border border-gray-100/50 h-full">
    <div class="bg-gradient-to-r from-pink-500 via-rose-500 to-orange-400 px-6 py-5">
        <h2 class="text-lg font-bold text-white flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-lg bg-white/20 backdrop-blur-sm flex items-center justify-center">
                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                </svg>
            </div>
            Partner verbinden
        </h2>
    </div>

    <div class="p-6">
        @if($isConnected)
            {{-- Connected State --}}
            <div class="text-center space-y-4">
                <div class="inline-flex items-center justify-center w-20 h-20 rounded-2xl bg-gradient-to-br from-emerald-50 to-teal-50 border-2 border-emerald-100">
                    <svg class="w-10 h-10 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-gray-400 font-medium uppercase tracking-wider">Verbunden mit</p>
                    <p class="text-2xl font-extrabold bg-gradient-to-r from-pink-500 to-rose-500 bg-clip-text text-transparent mt-1">
                        {{ $partner?->name ?? 'Partner' }}
                    </p>
                </div>
                <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-emerald-50 text-emerald-600 text-xs font-medium">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                    </span>
                    Synchronisiert
                </div>
            </div>
        @else
            {{-- Invite via Email --}}
            <div class="space-y-4">
                <p class="text-sm text-gray-600 font-medium">Lade deinen Partner per E-Mail ein:</p>
                <form wire:submit="sendInvitation" class="space-y-3">
                    <input
                        type="email"
                        wire:model="partnerEmail"
                        placeholder="partner@email.com"
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-pink-400 focus:ring-4 focus:ring-pink-100 transition-all duration-200 text-sm text-gray-700 placeholder-gray-400"
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
