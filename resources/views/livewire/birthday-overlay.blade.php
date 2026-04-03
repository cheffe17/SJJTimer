<div>
@if($show)
<div
    x-data="{
        visible: false,
        fadeOut: false,
        init() {
            setTimeout(() => this.visible = true, 100);
            setTimeout(() => {
                this.fadeOut = true;
                setTimeout(() => $wire.dismiss(), 800);
            }, 5000);
        }
    }"
    x-show="visible && !fadeOut"
    x-transition:enter="transition ease-out duration-700"
    x-transition:enter-start="opacity-0 scale-95"
    x-transition:enter-end="opacity-100 scale-100"
    x-transition:leave="transition ease-in duration-700"
    x-transition:leave-start="opacity-100 scale-100"
    x-transition:leave-end="opacity-0 scale-95"
    class="fixed inset-0 z-[9999] flex items-center justify-center p-4 sm:p-6"
    style="backdrop-filter: blur(8px); background: rgba(0,0,0,0.75);"
>
    <div class="relative w-full max-w-xs sm:max-w-sm md:max-w-md text-center">
        {{-- Bild --}}
        <div class="relative rounded-2xl sm:rounded-3xl overflow-hidden shadow-2xl shadow-pink-500/30 mb-4 sm:mb-6">
            <img
                src="{{ asset('images/anyaimages/anyaimages.jpg') }}"
                alt="Happy Birthday"
                class="w-full h-auto max-h-[50vh] sm:max-h-[60vh] object-cover"
            />
            <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent"></div>
        </div>

        {{-- Text --}}
        <h1
            x-show="visible"
            x-transition:enter="transition ease-out duration-1000 delay-300"
            x-transition:enter-start="opacity-0 -translate-y-4"
            x-transition:enter-end="opacity-100 translate-y-0"
            class="text-3xl sm:text-5xl md:text-6xl font-extrabold bg-gradient-to-r from-pink-400 via-rose-400 to-amber-400 bg-clip-text text-transparent drop-shadow-lg animate-pulse"
        >
            Happy Birthday
        </h1>

        <p
            x-show="visible"
            x-transition:enter="transition ease-out duration-1000 delay-700"
            x-transition:enter-start="opacity-0 translate-y-4"
            x-transition:enter-end="opacity-100 translate-y-0"
            class="mt-2 sm:mt-3 text-sm sm:text-lg text-white/70"
        >
            Alles Liebe zum Geburtstag!
        </p>
    </div>
</div>
@endif
</div>
