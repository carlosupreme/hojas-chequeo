<div class="relative flex flex-col h-screen">

    {{-- Loading overlay --}}
    <div wire:loading.delay wire:target="selectHojaChequeo, selectHojaEjecucion, loadMore"
        class="absolute inset-0 z-50 flex items-center justify-center bg-white/70 dark:bg-gray-900/80 backdrop-blur-sm">
        <div class="flex flex-col items-center gap-3 p-5 rounded-2xl bg-white dark:bg-gray-800 shadow-xl border border-gray-100 dark:border-gray-700">
            <div class="relative w-10 h-10">
                <div class="absolute inset-0 rounded-full border-[3px] border-gray-100 dark:border-gray-700"></div>
                <div class="absolute inset-0 rounded-full border-[3px] border-t-blue-600 border-r-transparent border-b-transparent border-l-transparent animate-spin"></div>
            </div>
            <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Cargando equipo…</span>
        </div>
    </div>

    {{-- ── HEADER ── --}}
    <div class="flex-none px-4 sm:px-5 py-4 border-b border-gray-200 dark:border-gray-700">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">

            {{-- Welcome --}}
            <div>
                <h1 class="text-xl font-black text-gray-900 dark:text-white tracking-tight">
                    Hola, {{ explode(' ', $user->name)[0] }}
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                    Selecciona un equipo para comenzar el chequeo.
                </p>
            </div>

            {{-- Turno selector  --}}
            <div class="flex items-center gap-3 bg-gray-50 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 px-3 py-2 w-full sm:w-auto">
                <div class="flex-1 sm:w-52 z-30">
                    {{ $this->form }}
                    <x-filament-actions::modals />
                </div>
                <div class="shrink-0 text-xs font-semibold text-gray-400 dark:text-gray-500 border-l border-gray-200 dark:border-gray-600 pl-3 tabular-nums">
                    {{ now()->format('H:i') }}
                </div>
            </div>

        </div>
    </div>

    {{-- ── FILTERS ── --}}
    <div class="flex-none px-4 sm:px-5 py-3 border-b border-gray-100 dark:border-gray-700 z-20">
        <div class="flex flex-col sm:flex-row gap-2">

            {{-- Search --}}
            <div class="relative shrink-0 sm:w-60">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <input wire:model.live.debounce.300ms="search" type="search" placeholder="Buscar equipo..."
                    class="block w-full rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 py-2 pl-9 pr-3 text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none">
            </div>

            {{-- Area tabs --}}
            <div class="flex items-center gap-1 overflow-x-auto no-scrollbar">
                <button wire:click="$set('activeFilter', null)"
                    class="px-3 py-1.5 rounded-lg text-xs font-bold transition-colors whitespace-nowrap
                        {{ is_null($activeFilter) ? 'bg-gray-900 text-white dark:bg-white dark:text-gray-900' : 'text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800' }}">
                    Todos
                </button>
                @foreach($areas as $area)
                    <button wire:click="toggleFilter('{{ $area }}')"
                        class="px-3 py-1.5 rounded-lg text-xs font-bold transition-colors whitespace-nowrap uppercase
                            {{ $activeFilter === $area ? 'bg-gray-900 text-white dark:bg-white dark:text-gray-900' : 'text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800' }}">
                        {{ $area }}
                    </button>
                @endforeach
            </div>

        </div>
    </div>

    {{-- ── KANBAN ── --}}
    <div class="flex-1 overflow-hidden">
        <div class="h-full px-4 sm:px-5 py-4
            flex flex-row gap-4 overflow-x-auto snap-x snap-mandatory
            lg:grid lg:grid-cols-3 lg:overflow-hidden lg:gap-5">

            {{-- COLUMN: Por Iniciar --}}
            <div class="min-w-[82vw] sm:min-w-[380px] lg:min-w-0 snap-center h-full flex flex-col bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between sticky top-0 bg-white dark:bg-gray-900 z-10">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-gray-400 dark:bg-gray-500"></span>
                        <span class="text-xs font-bold text-gray-600 dark:text-gray-300 uppercase tracking-wider">Por Iniciar</span>
                    </div>
                    <span class="text-xs font-bold text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-800 px-2 py-0.5 rounded-md">
                        {{ $hojas->count() }}
                    </span>
                </div>
                <div class="flex-1 overflow-y-auto p-3 space-y-2.5 bg-gray-50/60 dark:bg-gray-950/50">
                    @foreach ($hojas as $hoja)
                        <div wire:loading.class="opacity-50 scale-95" wire:target="selectHojaChequeo({{ $hoja->id }})"
                            class="transition-all duration-200" wire:click="selectHojaChequeo({{ $hoja->id }})">
                            <x-kanban-card :version="$hoja->version" status="new" :equipo="$hoja->equipo"
                                :date="$hoja->latestChequeoDiario?->finalizado_en"
                                :capacidad="$hoja->equipo->capacidad()" />
                        </div>
                    @endforeach
                    @if($hasMore)
                        <div x-intersect="$wire.loadMore()" class="py-4 flex justify-center">
                            <svg class="animate-spin h-4 w-4 text-blue-500" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                            </svg>
                        </div>
                    @endif
                    @if($hojas->isEmpty())
                        <div class="py-10 flex flex-col items-center gap-2 text-gray-400 dark:text-gray-600">
                            <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            <span class="text-xs font-medium">Sin equipos</span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- COLUMN: Pendientes --}}
            <div class="min-w-[82vw] sm:min-w-[380px] lg:min-w-0 snap-center h-full flex flex-col bg-amber-50 dark:bg-amber-950/20 rounded-xl border border-amber-100 dark:border-amber-900/40 overflow-hidden">
                <div class="px-4 py-3 border-b border-amber-100 dark:border-amber-900/40 flex items-center justify-between sticky top-0 bg-amber-50 dark:bg-amber-950/30 z-10">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                        <span class="text-xs font-bold text-amber-700 dark:text-amber-400 uppercase tracking-wider">Pendientes</span>
                    </div>
                    <span class="text-xs font-bold text-amber-600 dark:text-amber-400 bg-amber-100 dark:bg-amber-900/40 px-2 py-0.5 rounded-md">
                        {{ $chequeosPendientes->count() }}
                    </span>
                </div>
                <div class="flex-1 overflow-y-auto p-3 space-y-2.5">
                    @foreach ($chequeosPendientes as $chequeo)
                        <div wire:click="selectHojaEjecucion({{ $chequeo->id }})" class="transition-all duration-200">
                            <x-kanban-card :version="$chequeo->hojaChequeo->version" status="pending"
                                :equipo="$chequeo->hojaChequeo->equipo" :date="$chequeo->updated_at"
                                :capacidad="$chequeo->hojaChequeo->equipo->capacidad()" />
                        </div>
                    @endforeach
                    @if($chequeosPendientes->isEmpty())
                        <div class="py-10 flex flex-col items-center gap-2 text-amber-300 dark:text-amber-800">
                            <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span class="text-xs font-medium">Sin pendientes</span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- COLUMN: Finalizados Hoy --}}
            <div class="min-w-[82vw] sm:min-w-[380px] lg:min-w-0 snap-center h-full flex flex-col bg-emerald-50 dark:bg-emerald-950/20 rounded-xl border border-emerald-100 dark:border-emerald-900/40 overflow-hidden">
                <div class="px-4 py-3 border-b border-emerald-100 dark:border-emerald-900/40 flex items-center justify-between sticky top-0 bg-emerald-50 dark:bg-emerald-950/30 z-10">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        <span class="text-xs font-bold text-emerald-700 dark:text-emerald-400 uppercase tracking-wider">Finalizados Hoy</span>
                    </div>
                    <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400 bg-emerald-100 dark:bg-emerald-900/40 px-2 py-0.5 rounded-md">
                        {{ $chequeosCompletados->count() }}
                    </span>
                </div>
                <div class="flex-1 overflow-y-auto p-3 space-y-2.5">
                    @foreach ($chequeosCompletados as $chequeo)
                        <div wire:click="selectHojaEjecucion({{ $chequeo->id }})" class="transition-all duration-200">
                            <x-kanban-card :version="$chequeo->hojaChequeo->version" status="completed"
                                :equipo="$chequeo->hojaChequeo->equipo" :date="$chequeo->finalizado_en"
                                :capacidad="$chequeo->hojaChequeo->equipo->capacidad()" />
                        </div>
                    @endforeach
                    @if($chequeosCompletados->isEmpty())
                        <div class="py-10 flex flex-col items-center gap-2 text-emerald-300 dark:text-emerald-800">
                            <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0"/>
                            </svg>
                            <span class="text-xs font-medium">Ninguno aún</span>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>

</div>
