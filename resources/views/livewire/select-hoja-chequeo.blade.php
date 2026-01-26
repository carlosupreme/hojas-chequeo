@php use App\Area; @endphp

<div
    class="relative flex flex-col h-[calc(100vh-1rem)] sm:h-[calc(100vh-2rem)] space-y-4 font-sans">

    {{-- ✨ BEAUTIFUL LOADING OVERLAY ✨ --}}
    {{-- This only shows when 'selectHojaChequeo' or 'selectHojaEjecucion' are running --}}
    <div
        wire:loading.delay
        wire:target="selectHojaChequeo, selectHojaEjecucion, loadMore"
        class="absolute inset-0 z-50 flex items-center justify-center bg-white/60 dark:bg-gray-900/70 backdrop-blur-[3px] transition-all duration-300"
    >
        <div class="flex flex-col items-center gap-3 p-4 rounded-2xl bg-white dark:bg-gray-800 shadow-xl border border-gray-100 dark:border-gray-700 transform scale-100 animate-in fade-in zoom-in-95 duration-200">

            {{-- Modern Spinner --}}
            <div class="relative w-12 h-12">
                {{-- Outer Ring --}}
                <div class="absolute inset-0 rounded-full border-[3px] border-gray-100 dark:border-gray-700"></div>
                {{-- Spinning Inner Ring --}}
                <div class="absolute inset-0 rounded-full border-[3px] border-t-blue-600 border-r-transparent border-b-transparent border-l-transparent animate-spin"></div>
            </div>

            {{-- Loading Text --}}
            <div class="flex flex-col items-center">
                <span class="text-sm font-bold text-gray-900 dark:text-white tracking-wide">
                    Cargando equipo
                </span>
                <span class="text-xs text-gray-500 dark:text-gray-400">
                    Por favor espere...
                </span>
            </div>
        </div>
    </div>

    {{-- 1. HEADER SECTION --}}
    <div class="flex-none px-4 sm:px-6 pt-2">
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">

            {{-- Welcome Text --}}
            <div>
                <h1 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight">
                    Hola, {{ explode(' ', $user->name)[0] }}
                </h1>
                <p class="text-gray-500 dark:text-gray-400 text-sm mt-0.5">
                    Selecciona un equipo para comenzar el chequeo.
                </p>
            </div>

            {{-- Turno Widget (Right Side) --}}
            <div data-animate="turno-card"
                 class="w-full lg:w-auto bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-2 pr-4 flex items-center gap-3">
                <div
                    class="h-10 w-10 rounded-lg bg-orange-50 dark:bg-orange-900/20 flex items-center justify-center text-orange-500">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </div>
                <div>
                    <div class="text-[10px] uppercase tracking-wider font-bold text-gray-400">Turno Actual</div>
                    <div class="font-bold text-gray-900 dark:text-white leading-none">
                        {{ $turno->nombre ?? 'General' }}
                    </div>
                </div>
                <div
                    class="ml-auto text-xs font-medium text-gray-400 border-l border-gray-200 dark:border-gray-600 pl-3">
                    {{ now()->format('H:i') }}
                </div>
            </div>
        </div>
    </div>

    {{-- 2. FILTERS SECTION --}}
    <div class="flex-none px-4 sm:px-6 z-20">
        <div
            class="bg-white dark:bg-gray-900 p-2 rounded-xl shadow-sm border border-gray-200 dark:border-gray-800 flex flex-col md:flex-row gap-3">

            {{-- Search Input --}}
            <div class="relative w-full md:w-64 shrink-0">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
                <input
                    wire:model.live.debounce.300ms="search"
                    type="search"
                    placeholder="Filtrar equipos..."
                    class="block w-full rounded-lg border-gray-200 bg-gray-50 py-2 pl-9 text-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                >
            </div>

            {{-- Filter Tabs (Scrollable) --}}
            <div class="flex-1 flex items-center gap-1 overflow-x-auto no-scrollbar md:mask-linear-fade">
                <button
                    wire:click="$set('activeFilter', null)"
                    class="px-4 py-1.5 rounded-lg text-xs font-bold transition-colors whitespace-nowrap
                    {{ is_null($activeFilter)
                        ? 'bg-gray-900 text-white dark:bg-white dark:text-gray-900'
                        : 'text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800'
                    }}">
                    TODOS
                </button>
                @foreach($areas as $area)
                    <button
                        wire:click="toggleFilter('{{ $area->value }}')"
                        class="px-4 py-1.5 rounded-lg text-xs font-bold transition-colors whitespace-nowrap uppercase
                        {{ $activeFilter?->value === $area->value
                            ? 'bg-gray-900 text-white dark:bg-white dark:text-gray-900'
                            : 'text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800'
                        }}">
                        {{ $area->label() }}
                    </button>
                @endforeach
            </div>
        </div>
    </div>

    {{-- 3. KANBAN BOARD (Responsive) --}}
    <div class="flex-1 overflow-hidden">
        {{--
           Layout Logic:
           - Mobile/Tablet (< lg): Flex Row with Horizontal Scroll + Snap.
           - Desktop (lg+): Grid 3 columns.
        --}}
        <div class="h-full w-full px-4 sm:px-6 pb-4
                    flex flex-row overflow-x-auto snap-x snap-mandatory gap-4
                    lg:grid lg:grid-cols-3 lg:overflow-hidden lg:gap-6">

            {{-- COLUMN 1: NEW --}}
            <div
                class="min-w-[85vw] sm:min-w-[400px] lg:min-w-0 snap-center h-full flex flex-col bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-[0_2px_8px_rgba(0,0,0,0.04)] overflow-hidden">
                {{-- Header --}}
                <div
                    class="p-4 border-b border-gray-100 dark:border-gray-800 flex justify-between items-center bg-white dark:bg-gray-900 z-10 sticky top-0">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-gray-300"></span>
                        <h3 class="font-black text-gray-700 dark:text-gray-200 text-xs uppercase tracking-widest">Por
                            Iniciar</h3>
                    </div>
                    <span
                        class="bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 px-2 py-0.5 rounded-md text-xs font-bold">
                        {{ $hojas->count() }}
                    </span>
                </div>
                {{-- List --}}
                <div class="flex-1 overflow-y-auto p-3 space-y-3 bg-gray-50/50 dark:bg-gray-950/50 custom-scrollbar">
                    @foreach ($hojas as $hoja)
                        <div
                            wire:loading.class="opacity-50 scale-95"
                            wire:target="selectHojaChequeo({{ $hoja->id }})"
                            class="transition-all duration-200"
                            wire:click="selectHojaChequeo({{ $hoja->id }})">
                            <x-kanban-card
                                :version="$hoja->version"
                                status="new"
                                :equipo="$hoja->equipo"
                                :date="$hoja->latestChequeoDiario?->finalizado_en"
                                :capacidad="$hoja->equipo->capacidad()"

                            />
                        </div>
                    @endforeach
                    @if($hasMore)
                        <div x-intersect="$wire.loadMore()" class="py-4 flex justify-center text-blue-500">
                            <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none"
                                 viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                        stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                      d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </div>
                    @endif
                </div>
            </div>

            {{-- COLUMN 2: PENDING --}}
            <div
                class="min-w-[85vw] sm:min-w-[400px] lg:min-w-0 snap-center h-full flex flex-col bg-amber-50 dark:bg-amber-950/20 rounded-2xl border border-amber-100 dark:border-amber-900/50 shadow-[0_2px_8px_rgba(251,191,36,0.05)] overflow-hidden">
                <div
                    class="p-4 border-b border-amber-100 dark:border-amber-900/50 flex justify-between items-center bg-amber-50 dark:bg-amber-900/20 z-10 sticky top-0">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                        <h3 class="font-black text-amber-700 dark:text-amber-500 text-xs uppercase tracking-widest">
                            Pendientes</h3>
                    </div>
                    <span
                        class="bg-white/60 dark:bg-amber-900/40 text-amber-700 dark:text-amber-400 px-2 py-0.5 rounded-md text-xs font-bold">
                        {{ $chequeosPendientes->count() }}
                    </span>
                </div>
                <div class="flex-1 overflow-y-auto p-3 space-y-3 custom-scrollbar">
                    @foreach ($chequeosPendientes as $chequeo)
                        <div wire:click="selectHojaEjecucion({{ $chequeo->id }})">
                            <x-kanban-card
                                :version="$chequeo->hojaChequeo->version"
                                status="pending"
                                :equipo="$chequeo->hojaChequeo->equipo"
                                :date="$chequeo->updated_at"
                                :capacidad="$hoja->equipo->capacidad()"

                            />
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- COLUMN 3: COMPLETED --}}
            <div
                class="min-w-[85vw] sm:min-w-[400px] lg:min-w-0 snap-center h-full flex flex-col bg-emerald-50 dark:bg-emerald-950/20 rounded-2xl border border-emerald-100 dark:border-emerald-900/50 shadow-[0_2px_8px_rgba(16,185,129,0.05)] overflow-hidden">
                <div
                    class="p-4 border-b border-emerald-100 dark:border-emerald-900/50 flex justify-between items-center bg-emerald-50 dark:bg-emerald-900/20 z-10 sticky top-0">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                        <h3 class="font-black text-emerald-700 dark:text-emerald-500 text-xs uppercase tracking-widest">
                            Finalizados Hoy</h3>
                    </div>
                    <span
                        class="bg-white/60 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-400 px-2 py-0.5 rounded-md text-xs font-bold">
                        {{ $chequeosCompletados->count() }}
                    </span>
                </div>
                <div class="flex-1 overflow-y-auto p-3 space-y-3 custom-scrollbar">
                    @foreach ($chequeosCompletados as $chequeo)
                        <div wire:click="selectHojaEjecucion({{ $chequeo->id }})">
                            <x-kanban-card
                                :version="$chequeo->hojaChequeo->version"
                                status="completed"
                                :equipo="$chequeo->hojaChequeo->equipo"
                                :date="$chequeo->finalizado_en"
                                :capacidad="$hoja->equipo->capacidad()"
                            />
                        </div>
                    @endforeach
                </div>
            </div>

        </div>
    </div>
</div>
