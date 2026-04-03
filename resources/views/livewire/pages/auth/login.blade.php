<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public LoginForm $form;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        Session::regenerate();

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <h2 class="text-2xl font-bold text-white mb-6 text-center">Willkommen zurück</h2>

    <form wire:submit="login" class="space-y-5">
        <!-- Email Address -->
        <div>
            <label for="email" class="block text-sm font-medium text-white/70 mb-1.5">E-Mail</label>
            <input wire:model="form.email" id="email" type="email" name="email" required autofocus autocomplete="username"
                class="w-full px-4 py-3 rounded-xl bg-white/10 border border-white/20 text-white placeholder-white/30 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-400/30 transition-all duration-200" />
            <x-input-error :messages="$errors->get('form.email')" class="mt-1.5" />
        </div>

        <!-- Password -->
        <div>
            <label for="password" class="block text-sm font-medium text-white/70 mb-1.5">Passwort</label>
            <input wire:model="form.password" id="password" type="password" name="password" required autocomplete="current-password"
                class="w-full px-4 py-3 rounded-xl bg-white/10 border border-white/20 text-white placeholder-white/30 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-400/30 transition-all duration-200" />
            <x-input-error :messages="$errors->get('form.password')" class="mt-1.5" />
        </div>

        <!-- Remember Me -->
        <div class="flex items-center justify-between">
            <label for="remember" class="inline-flex items-center">
                <input wire:model="form.remember" id="remember" type="checkbox" name="remember"
                    class="rounded border-white/30 bg-white/10 text-indigo-500 shadow-sm focus:ring-indigo-500">
                <span class="ms-2 text-sm text-white/60">Angemeldet bleiben</span>
            </label>

            @if (Route::has('password.request'))
                <a class="text-sm text-indigo-300 hover:text-indigo-200 transition-colors" href="{{ route('password.request') }}" wire:navigate>
                    Passwort vergessen?
                </a>
            @endif
        </div>

        <button type="submit" class="w-full py-3 rounded-xl text-white font-semibold bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-400 hover:to-purple-500 shadow-lg shadow-indigo-500/25 hover:shadow-xl hover:-translate-y-0.5 transition-all duration-200">
            Anmelden
        </button>

        <p class="text-center text-sm text-white/40">
            Noch kein Konto?
            <a href="{{ route('register') }}" class="text-indigo-300 hover:text-indigo-200 font-medium transition-colors" wire:navigate>
                Jetzt registrieren
            </a>
        </p>
    </form>
</div>
