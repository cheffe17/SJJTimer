<?php

use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $email = '';

    /**
     * Send a password reset link to the provided email address.
     */
    public function sendPasswordResetLink(): void
    {
        $this->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        $status = Password::sendResetLink(
            $this->only('email')
        );

        if ($status != Password::RESET_LINK_SENT) {
            $this->addError('email', __($status));

            return;
        }

        $this->reset('email');

        session()->flash('status', __($status));
    }
}; ?>

<div>
    <h2 class="text-2xl font-bold text-white mb-4 text-center">Passwort vergessen?</h2>

    <p class="mb-6 text-sm text-white/50 text-center">
        Kein Problem. Gib deine E-Mail-Adresse ein und wir senden dir einen Link zum Zurücksetzen.
    </p>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form wire:submit="sendPasswordResetLink" class="space-y-5">
        <!-- Email Address -->
        <div>
            <label for="email" class="block text-sm font-medium text-white/70 mb-1.5">E-Mail</label>
            <input wire:model="email" id="email" type="email" name="email" required autofocus
                class="w-full px-4 py-3 rounded-xl bg-white/10 border border-white/20 text-white placeholder-white/30 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-400/30 transition-all duration-200" />
            <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
        </div>

        <button type="submit" class="w-full py-3 rounded-xl text-white font-semibold bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-400 hover:to-purple-500 shadow-lg shadow-indigo-500/25 hover:shadow-xl hover:-translate-y-0.5 transition-all duration-200">
            Link senden
        </button>

        <p class="text-center text-sm text-white/40">
            <a href="{{ route('login') }}" class="text-indigo-300 hover:text-indigo-200 font-medium transition-colors" wire:navigate>
                Zurück zur Anmeldung
            </a>
        </p>
    </form>
</div>
