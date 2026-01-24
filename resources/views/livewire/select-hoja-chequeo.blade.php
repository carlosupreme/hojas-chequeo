@php use App\Area; @endphp
<div class="min-h-screen space-y-8">
    {{-- Filters & Search --}}
    <div
        class="flex flex-col md:flex-row justify-between gap-4 sticky top-4 z-10   backdrop-blur-sm py-2 -mx-2 px-2 rounded-lg">

        {{-- Area Tabs --}}
        <div class="flex gap-2 overflow-x-auto pb-2 md:pb-0 no-scrollbar">
            <button
                wire:click="$set('activeFilter', null)"
                @disabled(!$activeFilter)
                class="px-2 md:px-4 md:py-2 rounded-full text-xs md:text-sm font-medium transition-all whitespace-nowrap
                {{ is_null($activeFilter)
                    ? 'bg-gray-900 text-white dark:bg-white dark:text-gray-900 shadow-md'
                    : 'bg-white text-gray-600 hover:bg-gray-100 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 border border-gray-200 dark:border-gray-700'
                }}">
                Todos
            </button>
            @foreach($areas as $area)
                <button
                    wire:click="toggleFilter('{{ $area->value }}')"
                    class="px-4 py-2 rounded-full text-sm font-medium transition-all whitespace-nowrap
                    {{ $activeFilter?->value === $area->value
                        ? 'bg-blue-600 text-white shadow-md shadow-blue-500/30'
                        : 'bg-white text-gray-600 hover:bg-gray-100 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 border border-gray-200 dark:border-gray-700'
                    }}">
                    {{ $area->label() }}
                </button>
            @endforeach
        </div>

        {{-- Search --}}
        <div class="relative w-full md:w-80 group">
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
                placeholder="Buscar equipo..."
                class="w-full pl-10 pr-4 py-2.5 rounded-full border border-gray-200 bg-zinc-50 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition-all shadow-sm dark:bg-zinc-900 dark:border-gray-700 dark:text-white dark:placeholder-gray-500"
            >
        </div>
    </div>

    @if($user->chequeosPendientes()->count() > 0)
        <div>
            <h2>Pendientes por finalizar: </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach ($chequeosPendientes as $chequeo)
                    @php
                        $hoja = $chequeo->hojaChequeo;
                    @endphp
                    <div
                        wire:click="selectHojaEjecucion({{ $chequeo }})">
                        <x-hoja-chequeo-card
                            status="pending"
                            :hoja="$hoja"/>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
    <hr>

    @if($user->chequeosCompletadosHoy()->count() > 0)
        <div>
            <h2>Completados hoy: </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach ($user->chequeosCompletadosHoy as $chequeo)
                    @php
                        $hoja = $chequeo->hojaChequeo;
                    @endphp
                    <div
                        wire:click="selectHojaEjecucion({{ $chequeo }})">
                        <x-hoja-chequeo-card
                            status="completed"
                            :hoja="$hoja"/>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
    <hr>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        @foreach ($hojas as $hoja)
            <div wire:click="selectHojaChequeo({{ $hoja->id }})">
                <x-hoja-chequeo-card
                    status="new"
                    :hoja="$hoja"/>
            </div>
        @endforeach
    </div>


    {{-- Empty State --}}
    @if($hojas->isEmpty())
        <div class="flex flex-col items-center justify-center py-12 text-center">
            <div class="w-16 h-16 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center mb-4">
                <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">No se encontraron equipos</h3>
            <p class="text-gray-500 dark:text-gray-400 max-w-sm mt-1">
                Intenta ajustar tu búsqueda o los filtros seleccionados.
            </p>
        </div>
    @endif

    {{-- Infinite Scroll Trigger --}}
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
                    }, {
                        rootMargin: '100px'
                    });
                    observer.observe(this.$el);
                }
            }"
            x-init="observe"
            class="flex justify-center items-center py-8">
            <div wire:loading wire:target="loadMore" class="flex items-center gap-2 text-blue-600 dark:text-blue-400">
                <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                          d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="font-medium">Cargando más equipos...</span>
            </div>
        </div>
    @endif
</div>

