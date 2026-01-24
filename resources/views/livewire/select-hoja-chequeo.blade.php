@php use App\Area; @endphp

<div class="min-h-screen pb-12 space-y-10">

    {{-- 1. SEARCH & FILTERS (Sticky Header) --}}
    <div
        class="sticky top-0 z-30 -mx-4 px-4 py-4 bg-gray-50/80 dark:bg-gray-900/80 backdrop-blur-xl border-b border-gray-200 dark:border-gray-800 transition-all duration-300">
        <div class="max-w-7xl mx-auto flex flex-col md:flex-row justify-between gap-4">

            {{-- Search Input (Filament Style) --}}
            <div class="relative w-full md:w-96 group">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400 group-focus-within:text-blue-500 transition-colors" fill="none"
                         stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
                <input
                    wire:model.live.debounce.300ms="search"
                    type="search"
                    placeholder="Buscar equipo por nombre o tag..."
                    class="block w-full rounded-lg border-0 py-2.5 pl-10 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6 bg-white dark:bg-gray-900 dark:ring-gray-700 dark:text-white"
                >
            </div>

            {{-- Filter Tabs --}}
            <div class="flex gap-2 overflow-x-auto pb-1 md:pb-0 no-scrollbar items-center">
                <button
                    wire:click="$set('activeFilter', null)"
                    class="px-3 py-1.5 rounded-md text-sm font-medium transition-all whitespace-nowrap focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900
                    {{ is_null($activeFilter)
                        ? 'bg-gray-900 text-white dark:bg-white dark:text-gray-900 shadow-sm'
                        : 'text-gray-600 hover:bg-gray-200/50 dark:text-gray-400 dark:hover:bg-gray-800'
                    }}">
                    Todos
                </button>
                <div class="h-4 w-px bg-gray-300 dark:bg-gray-700 mx-1"></div>
                @foreach($areas as $area)
                    <button
                        wire:click="toggleFilter('{{ $area->value }}')"
                        class="px-3 py-1.5 rounded-md text-sm font-medium transition-all whitespace-nowrap focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900
                        {{ $activeFilter?->value === $area->value
                            ? 'bg-blue-600 text-white shadow-sm'
                            : 'text-gray-600 hover:bg-gray-200/50 dark:text-gray-400 dark:hover:bg-gray-800'
                        }}">
                        {{ $area->label() }}
                    </button>
                @endforeach
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 space-y-12">

        {{-- 2. PRIORITY: PENDING (Action Required) --}}
        @if($chequeosPendientes->count() > 0)
            <section class="animate-fade-in-up">
                <div class="flex items-center gap-2 mb-4 text-amber-600 dark:text-amber-500">
                    <div class="p-1.5 bg-amber-100 dark:bg-amber-900/30 rounded-full">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h2 class="text-lg font-bold tracking-tight">Continuar Pendientes</h2>
                    <span
                        class="ml-2 inline-flex items-center rounded-full bg-amber-50 px-2 py-1 text-xs font-medium text-amber-700 ring-1 ring-inset ring-amber-600/20 dark:bg-amber-900/30 dark:text-amber-400 dark:ring-amber-400/30">
                        {{ $chequeosPendientes->count() }}
                    </span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    @foreach ($chequeosPendientes as $chequeo)
                        <div wire:click="selectHojaEjecucion({{ $chequeo->id }})"
                             class="transform transition hover:scale-[1.01]">
                            <x-hoja-chequeo-card
                                status="pending"
                                :hoja="$chequeo->hojaChequeo"
                            />
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- 3. STANDARD: NEW ITEMS (Main List) --}}
        <section>
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2 text-gray-900 dark:text-white">
                    <div class="p-1.5 bg-gray-100 dark:bg-gray-800 rounded-full">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                    </div>
                    <h2 class="text-lg font-bold tracking-tight">Equipos Disponibles</h2>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach ($hojas as $hoja)
                    <div wire:click="selectHojaChequeo({{ $hoja->id }})">
                        <x-hoja-chequeo-card
                            status="new"
                            :hoja="$hoja"
                        />
                    </div>
                @endforeach
            </div>

            {{-- Empty State --}}
            @if($hojas->isEmpty() && $chequeosPendientes->isEmpty())
                <div
                    class="flex flex-col items-center justify-center py-16 text-center border-2 border-dashed border-gray-200 dark:border-gray-700 rounded-2xl bg-gray-50 dark:bg-gray-800/50">
                    <div
                        class="w-16 h-16 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center mb-4 ring-8 ring-gray-50 dark:ring-gray-900">
                        <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Sin resultados</h3>
                    <p class="text-gray-500 dark:text-gray-400 max-w-sm mt-1 text-sm">
                        No hay equipos que coincidan con tu búsqueda o filtros actuales.
                    </p>
                </div>
            @endif

            {{-- Infinite Scroll --}}
            @if($hasMore)
                <div
                    x-data="{
                        observe() {
                            const observer = new IntersectionObserver((entries) => {
                                entries.forEach(entry => {
                                    if (entry.isIntersecting) {
                                        $wire.loadMore();
                                    }
                                });
                            }, { rootMargin: '200px' });
                            observer.observe(this.$el);
                        }
                    }"
                    x-init="observe"
                    class="flex justify-center items-center py-12"
                >
                    <div wire:loading wire:target="loadMore" class="flex flex-col items-center gap-2 text-gray-400">
                        <svg class="animate-spin h-6 w-6 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none"
                             viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor"
                                  d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span class="text-xs font-medium uppercase tracking-wider">Cargando...</span>
                    </div>
                </div>
            @endif
        </section>

        {{-- 4. HISTORY: COMPLETED (Low Importance) --}}
        @if($user->chequeosCompletadosHoy()->count() > 0)
            <div class="relative py-4">
                <div class="absolute inset-0 flex items-center" aria-hidden="true">
                    <div class="w-full border-t border-gray-200 dark:border-gray-700"></div>
                </div>
                <div class="relative flex justify-center">
                    <span
                        class="bg-gray-50 dark:bg-gray-900 px-4 text-sm text-gray-500 dark:text-gray-400 uppercase tracking-widest font-semibold">
                        Finalizados hoy
                    </span>
                </div>
            </div>

            <section class="opacity-90 hover:opacity-100 transition-opacity duration-300">
                <div
                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 grayscale-[30%] hover:grayscale-0 transition-all duration-500">
                    @foreach ($chequeosCompletados as $chequeo)
                        <div wire:click="selectHojaEjecucion({{ $chequeo->id }})">
                            <x-hoja-chequeo-card
                                status="completed"
                                :hoja="$chequeo->hojaChequeo"
                            />
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

    </div>
</div>
