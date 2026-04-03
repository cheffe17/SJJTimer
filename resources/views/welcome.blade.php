<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>SSJTimer</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-black text-white min-h-screen">

        <div class="relative min-h-screen flex flex-col items-center justify-center selection:bg-indigo-500 selection:text-white">

            {{-- Navigation oben rechts --}}
            @if (Route::has('login'))
                <nav class="absolute top-0 right-0 p-6 flex items-center gap-4">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="rounded-md px-4 py-2 text-sm font-medium text-white/70 hover:text-white transition">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="rounded-md px-4 py-2 text-sm font-medium text-white/70 hover:text-white transition">
                            Anmelden
                        </a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="rounded-md px-4 py-2 text-sm font-medium text-white/70 hover:text-white transition">
                                Registrieren
                            </a>
                        @endif
                    @endauth
                </nav>
            @endif

            {{-- Zentrierter Block --}}
            <div class="text-center px-6">
                <img src="{{ asset('images/logo/logowhite.png') }}" alt="SSJTimer" class="h-32 sm:h-44 w-auto mx-auto mb-10" />

                <h1 class="text-3xl sm:text-4xl font-bold text-white mb-4">
                    Welcome to SSJTimer
                </h1>

                <p class="text-lg text-white/50 max-w-md mx-auto">
                    Der Timer und mehr für deine Fernbeziehung.
                </p>
            </div>

            {{-- Footer --}}
            <footer class="absolute bottom-0 w-full py-6 text-center text-sm text-white/20">
                SSJTimer v1.0
            </footer>
        </div>

    </body>
</html>
