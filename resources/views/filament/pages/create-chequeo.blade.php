<x-filament-panels::page>
    {{-- Top Header Section --}}
    @if($hojaChequeo && $this->hasItems())
        <div class="flex sm:flex-row flex-col gap-4 items-center w-full mb-6 mt-3 place-content-center">
            <button wire:click="resetState"
                    class="flex items-center  px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-700">
                @svg('heroicon-o-arrow-left', 'w-4 h-4 mr-4')
                Regresar
            </button>
            <div class="flex gap-4 items-center place-content-center flex-wrap">
                <h2 class="font-bold">{{ $this->hojaChequeo->equipo->nombre }}</h2>
                <x-filament::badge color="primary">{{$this->hojaChequeo->equipo->tag}}</x-filament::badge>
                <x-filament::badge color="primary">{{$user->turno->nombre}}</x-filament::badge>
                @if($this->hojaEjecucion)
                    <x-filament::badge color="primary">Reanudando</x-filament::badge>
                @endif
            </div>

            {{$this->dateForm}}
        </div>

        <livewire:chequeo-items :hoja="$hojaChequeo" :ejecucion="$hojaEjecucion"/>

        @if($this->hojaChequeo->observaciones)
            <div>
                <h2 class="font-bold">Observaciones:</h2>
                {!! $this->hojaChequeo->observaciones !!}
            </div>
        @endif

        <form>
            {{ $this->form }}
        </form>

        <div class="flex w-full gap-2 my-5 items-center place-content-end">
            <x-filament::button wire:click="create" class="mt-4">Guardar</x-filament::button>
        </div>
        <x-filament-actions::modals/>
    @else
        <div data-page="create-chequeo"
             class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
            <div class="space-y-1">
                <h1 data-animate="header-title" class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">
                    Hola, {{ $user->name }}
                </h1>
                <p data-animate="header-subtitle" class="text-gray-500 dark:text-gray-400">
                    Selecciona un equipo para comenzar el chequeo.
                </p>
            </div>


            {{-- Creative Turno Widget --}}
            <div
                data-animate="turno-card"
                class="relative group overflow-hidden rounded-2xl bg-white dark:bg-gray-800 shadow-lg border border-gray-100 dark:border-gray-700 p-1 pr-6 transition-all hover:shadow-xl">
                <div class="flex items-center gap-4">
                    {{-- Dynamic Icon Background based on Turno --}}
                    @php
                        $turno = $user->turno;
                        $startHour = \Carbon\Carbon::parse($turno->hora_inicio)->hour;
                        $isMorning = $startHour >= 5 && $startHour < 12;
                        $isAfternoon = $startHour >= 12 && $startHour < 19;

                        $iconBg = match(true) {
                            $isMorning => 'bg-amber-100 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400',
                            $isAfternoon => 'bg-orange-100 text-orange-600 dark:bg-orange-900/30 dark:text-orange-400',
                            default => 'bg-indigo-100 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400',
                        };

                        $icon = match(true) {
                            $isMorning => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />',
                            $isAfternoon => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />', // Using sun for afternoon too, maybe different style if desired
                            default => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />',
                        };
                    @endphp

                    <div class="h-14 w-14 rounded-xl flex items-center justify-center {{ $iconBg }}">
                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            {!! $icon !!}
                        </svg>
                    </div>

                    <div class="flex flex-col">
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                        Turno Actual
                    </span>
                        <span class="text-lg font-bold text-gray-900 dark:text-white">
                        {{ $turno->nombre }}
                    </span>
                        <span class="text-xs font-medium text-gray-400 dark:text-gray-500 font-mono">
                        {{ \Carbon\Carbon::parse($turno->hora_inicio)->format('H:i') }} - {{ \Carbon\Carbon::parse($turno->hora_final)->format('H:i') }}
                    </span>
                    </div>
                </div>
            </div>
        </div>
        <livewire:select-hoja-chequeo/>
    @endif
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('scroll-to-top', () => {
                window.scrollTo({top: 0, behavior: 'smooth'});
            });
        });
    </script>
</x-filament-panels::page>
