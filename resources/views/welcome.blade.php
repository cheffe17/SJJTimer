<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>SSJTimer - Euer gemeinsamer Countdown</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-gradient-to-br from-slate-900 via-indigo-950 to-purple-950 text-white min-h-screen">

        {{-- Navigation --}}
        <nav class="fixed top-0 w-full z-50 bg-white/5 backdrop-blur-xl border-b border-white/10">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <div class="flex items-center gap-3">
                        <img src="{{ asset('images/logo/logowhite.png') }}" alt="SSJTimer" class="h-8 w-auto" />
                    </div>
                    <div class="flex items-center gap-3">
                        @if (Route::has('login'))
                            @auth
                                <a href="{{ url('/dashboard') }}" class="px-5 py-2.5 rounded-xl text-sm font-semibold text-white bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-400 hover:to-purple-500 shadow-lg shadow-indigo-500/25 transition-all duration-200">
                                    Dashboard
                                </a>
                            @else
                                <a href="{{ route('login') }}" class="px-5 py-2.5 rounded-xl text-sm font-medium text-white/80 hover:text-white hover:bg-white/10 transition-all duration-200">
                                    Anmelden
                                </a>
                                @if (Route::has('register'))
                                    <a href="{{ route('register') }}" class="px-5 py-2.5 rounded-xl text-sm font-semibold text-white bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-400 hover:to-purple-500 shadow-lg shadow-indigo-500/25 transition-all duration-200">
                                        Registrieren
                                    </a>
                                @endif
                            @endauth
                        @endif
                    </div>
                </div>
            </div>
        </nav>

        {{-- Hero Section --}}
        <section class="relative pt-32 pb-20 px-4 sm:px-6 lg:px-8 overflow-hidden">
            {{-- Background decorations --}}
            <div class="absolute top-20 left-1/4 w-96 h-96 bg-indigo-500/20 rounded-full blur-3xl"></div>
            <div class="absolute bottom-0 right-1/4 w-80 h-80 bg-purple-500/20 rounded-full blur-3xl"></div>
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-pink-500/10 rounded-full blur-3xl"></div>

            <div class="relative max-w-5xl mx-auto text-center">
                {{-- Logo --}}
                <div class="mb-8 flex justify-center">
                    <img src="{{ asset('images/logo/logowhite.png') }}" alt="SSJTimer" class="h-20 sm:h-28 w-auto drop-shadow-2xl" />
                </div>

                {{-- Tagline --}}
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold leading-tight mb-6">
                    <span class="bg-gradient-to-r from-white via-indigo-200 to-purple-200 bg-clip-text text-transparent">
                        Euer gemeinsamer
                    </span>
                    <br />
                    <span class="bg-gradient-to-r from-indigo-400 via-purple-400 to-pink-400 bg-clip-text text-transparent">
                        Countdown
                    </span>
                </h1>

                <p class="text-lg sm:text-xl text-white/60 max-w-2xl mx-auto mb-10 leading-relaxed">
                    Egal wie viele Kilometer zwischen euch liegen &mdash; SSJTimer bringt euch näher zusammen.
                    Plant gemeinsam Flüge, Besuche und Dates und zählt die Tage bis zum nächsten Wiedersehen.
                </p>

                {{-- CTA Buttons --}}
                <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="w-full sm:w-auto px-8 py-4 rounded-2xl text-lg font-bold text-white bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 hover:from-indigo-400 hover:via-purple-400 hover:to-pink-400 shadow-2xl shadow-indigo-500/30 hover:shadow-indigo-500/50 hover:-translate-y-0.5 transition-all duration-300">
                            Jetzt kostenlos starten
                        </a>
                    @endif
                    @if (Route::has('login'))
                        <a href="{{ route('login') }}" class="w-full sm:w-auto px-8 py-4 rounded-2xl text-lg font-semibold text-white/90 border-2 border-white/20 hover:border-white/40 hover:bg-white/5 transition-all duration-300">
                            Ich habe bereits ein Konto
                        </a>
                    @endif
                </div>
            </div>
        </section>

        {{-- Features Section --}}
        <section class="py-20 px-4 sm:px-6 lg:px-8 relative">
            <div class="max-w-6xl mx-auto">
                <h2 class="text-3xl sm:text-4xl font-bold text-center mb-4">
                    <span class="bg-gradient-to-r from-indigo-300 to-purple-300 bg-clip-text text-transparent">
                        Alles, was ihr braucht
                    </span>
                </h2>
                <p class="text-center text-white/50 mb-16 max-w-xl mx-auto">
                    SSJTimer wurde für Paare in Fernbeziehungen entwickelt, die die Zeit bis zum nächsten Treffen überbrücken wollen.
                </p>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    {{-- Feature 1: Timer --}}
                    <div class="group bg-white/5 backdrop-blur-sm border border-white/10 rounded-3xl p-8 hover:bg-white/10 hover:border-white/20 transition-all duration-300">
                        <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-violet-500 to-indigo-500 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-white mb-3">Live Countdown</h3>
                        <p class="text-white/50 leading-relaxed">
                            Zählt gemeinsam die Sekunden bis zu eurem nächsten Treffen herunter. In Echtzeit, auf die Sekunde genau.
                        </p>
                    </div>

                    {{-- Feature 2: Calendar --}}
                    <div class="group bg-white/5 backdrop-blur-sm border border-white/10 rounded-3xl p-8 hover:bg-white/10 hover:border-white/20 transition-all duration-300">
                        <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-pink-500 to-rose-500 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-white mb-3">Gemeinsamer Kalender</h3>
                        <p class="text-white/50 leading-relaxed">
                            Tragt Flüge, Besuche und Dates ein. Beide Partner sehen alle Events live und können gemeinsam planen.
                        </p>
                    </div>

                    {{-- Feature 3: Partner --}}
                    <div class="group bg-white/5 backdrop-blur-sm border border-white/10 rounded-3xl p-8 hover:bg-white/10 hover:border-white/20 transition-all duration-300">
                        <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-500 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-white mb-3">Partner verbinden</h3>
                        <p class="text-white/50 leading-relaxed">
                            Lade deinen Partner per E-Mail ein und verbindet euch mit einem Klick. Einfach, sicher und privat.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        {{-- How it works --}}
        <section class="py-20 px-4 sm:px-6 lg:px-8 relative">
            <div class="max-w-4xl mx-auto">
                <h2 class="text-3xl sm:text-4xl font-bold text-center mb-16">
                    <span class="bg-gradient-to-r from-indigo-300 to-purple-300 bg-clip-text text-transparent">
                        So funktioniert's
                    </span>
                </h2>

                <div class="space-y-12">
                    <div class="flex items-start gap-6">
                        <div class="flex-shrink-0 w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-500 flex items-center justify-center text-white font-bold text-lg shadow-lg shadow-indigo-500/30">
                            1
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-white mb-2">Account erstellen</h3>
                            <p class="text-white/50">Registriere dich kostenlos in wenigen Sekunden mit deiner E-Mail-Adresse.</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-6">
                        <div class="flex-shrink-0 w-12 h-12 rounded-2xl bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center text-white font-bold text-lg shadow-lg shadow-purple-500/30">
                            2
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-white mb-2">Partner einladen</h3>
                            <p class="text-white/50">Sende deinem Partner eine Einladung per E-Mail. Ein Klick genügt, um sich zu verbinden.</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-6">
                        <div class="flex-shrink-0 w-12 h-12 rounded-2xl bg-gradient-to-br from-pink-500 to-rose-500 flex items-center justify-center text-white font-bold text-lg shadow-lg shadow-pink-500/30">
                            3
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-white mb-2">Gemeinsam planen</h3>
                            <p class="text-white/50">Tragt eure Flüge, Besuche und Dates ein und schaut zusammen dem Countdown zu.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- CTA Section --}}
        <section class="py-20 px-4 sm:px-6 lg:px-8 relative">
            <div class="max-w-3xl mx-auto text-center">
                <div class="bg-gradient-to-br from-white/10 to-white/5 backdrop-blur-sm border border-white/10 rounded-3xl p-12">
                    <h2 class="text-3xl sm:text-4xl font-bold mb-4">
                        <span class="bg-gradient-to-r from-white to-indigo-200 bg-clip-text text-transparent">
                            Bereit für euren Countdown?
                        </span>
                    </h2>
                    <p class="text-white/50 mb-8 text-lg">
                        Startet jetzt und überbrückt die Distanz gemeinsam.
                    </p>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="inline-block px-10 py-4 rounded-2xl text-lg font-bold text-white bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 hover:from-indigo-400 hover:via-purple-400 hover:to-pink-400 shadow-2xl shadow-indigo-500/30 hover:shadow-indigo-500/50 hover:-translate-y-0.5 transition-all duration-300">
                            Kostenlos registrieren
                        </a>
                    @endif
                </div>
            </div>
        </section>

        {{-- Footer --}}
        <footer class="py-8 px-4 border-t border-white/10">
            <div class="max-w-7xl mx-auto text-center">
                <img src="{{ asset('images/logo/logowhite.png') }}" alt="SSJTimer" class="h-6 w-auto mx-auto mb-3 opacity-50" />
                <p class="text-white/30 text-sm">&copy; {{ date('Y') }} SSJTimer. Mit Liebe gebaut.</p>
            </div>
        </footer>

    </body>
</html>
