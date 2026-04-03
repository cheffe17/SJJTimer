<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'SSJTimer') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gradient-to-br from-slate-900 via-indigo-950 to-purple-950 relative overflow-hidden">
            {{-- Background decorations --}}
            <div class="absolute top-20 left-1/4 w-96 h-96 bg-indigo-500/15 rounded-full blur-3xl"></div>
            <div class="absolute bottom-20 right-1/4 w-80 h-80 bg-purple-500/15 rounded-full blur-3xl"></div>

            <div class="relative z-10">
                <a href="/" wire:navigate class="flex justify-center mb-2">
                    <img src="{{ asset('images/logo/logowhite.png') }}" alt="SSJTimer" class="h-16 w-auto drop-shadow-2xl" />
                </a>
                <p class="text-center text-white/40 text-sm mb-6">Euer gemeinsamer Countdown</p>
            </div>

            <div class="relative z-10 w-full sm:max-w-md px-6 py-8 bg-white/10 backdrop-blur-xl border border-white/20 shadow-2xl shadow-black/20 sm:rounded-3xl">
                {{ $slot }}
            </div>

            <p class="relative z-10 mt-8 text-white/20 text-xs">&copy; {{ date('Y') }} SSJTimer</p>
        </div>
    </body>
</html>
