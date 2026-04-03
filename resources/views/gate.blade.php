<!DOCTYPE html>
<html lang="de">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>SSJTimer</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-black text-white min-h-screen">

        <div
            x-data="{ shake: false }"
            class="relative min-h-screen flex flex-col items-center justify-center selection:bg-indigo-500 selection:text-white"
        >
            <div class="text-center px-6 w-full max-w-md">
                <img src="{{ asset('images/logo/logowhite.png') }}" alt="SSJTimer" class="h-24 sm:h-32 w-auto mx-auto mb-10" />

                <p class="text-white/40 text-sm mb-8">Gib das Passwort ein, um fortzufahren.</p>

                <form
                    method="POST"
                    action="/gate"
                    x-on:submit="if(document.querySelector('#password').value.trim() === '') { shake = true; setTimeout(() => shake = false, 500); $event.preventDefault(); }"
                >
                    @csrf
                    <div
                        :class="shake ? 'animate-[shake_0.5s_ease-in-out]' : ''"
                    >
                        <input
                            type="text"
                            id="password"
                            name="password"
                            autocomplete="off"
                            autofocus
                            placeholder="Passwort"
                            class="w-full px-5 py-4 rounded-2xl bg-white/10 border border-white/20 text-white text-center text-lg placeholder-white/30 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-400/30 focus:outline-none transition-all duration-200"
                        />
                    </div>

                    @error('password')
                        <p class="mt-3 text-sm text-rose-400">{{ $message }}</p>
                    @enderror

                    <button
                        type="submit"
                        class="mt-6 w-full py-4 rounded-2xl text-white font-semibold bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-400 hover:to-purple-500 shadow-lg shadow-indigo-500/25 hover:shadow-xl hover:-translate-y-0.5 transition-all duration-200"
                    >
                        Weiter
                    </button>
                </form>
            </div>
        </div>

        <style>
            @keyframes shake {
                0%, 100% { transform: translateX(0); }
                20% { transform: translateX(-8px); }
                40% { transform: translateX(8px); }
                60% { transform: translateX(-6px); }
                80% { transform: translateX(6px); }
            }
        </style>

    </body>
</html>
