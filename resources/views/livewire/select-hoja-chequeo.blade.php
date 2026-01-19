<div class="min-h-screen space-y-8">
    {{-- Filters & Search --}}
    <div class="flex flex-col md:flex-row justify-between gap-4 sticky top-4 z-10   backdrop-blur-sm py-2 -mx-2 px-2 rounded-lg">

        {{-- Area Tabs --}}
        <div class="flex gap-2 overflow-x-auto pb-2 md:pb-0 no-scrollbar">
            <button
                wire:click="$set('activeFilter', null)"
                class="px-4 py-2 rounded-full text-sm font-medium transition-all whitespace-nowrap
                {{ is_null($activeFilter)
                    ? 'bg-gray-900 text-white dark:bg-white dark:text-gray-900 shadow-md'
                    : 'bg-white text-gray-600 hover:bg-gray-100 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 border border-gray-200 dark:border-gray-700'
                }}">
                Todos
            </button>
            @foreach(\App\Area::cases() as $area)
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
                <svg class="h-5 w-5 text-gray-400 group-focus-within:text-blue-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
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

    {{-- Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        @foreach ($hojas as $hoja)
            <div
                wire:click="selectEquipo({{ $hoja->id }})"
                class="group cursor-pointer flex flex-col bg-white dark:bg-gray-800 rounded-2xl overflow-hidden border border-gray-200 dark:border-gray-700 shadow-sm hover:shadow-xl transition-all duration-300 hover:-translate-y-1"
            >
                                {{-- Card Image --}}
                                <div class="relative h-48 w-full overflow-hidden bg-gray-100 dark:bg-gray-700">
                                    @if ($hoja->equipo->foto)
                                        <img 
                                            class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110"
                                            src="{{ asset('storage/' . $hoja->equipo->foto) }}" 
                                            alt="{{ $hoja->equipo->nombre }}"
                                        >
                                    @else
                                        <div class="h-full w-full flex items-center justify-center bg-linear-to-br from-gray-100 to-gray-200 dark:from-gray-700 dark:to-gray-800">
                                            <svg class="h-16 w-16 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                    @endif
                
                                    {{-- Active Users Indicators --}}
                                    @if(isset($activeUsers[$hoja->id]) && count($activeUsers[$hoja->id]) > 0)
                                        <div class="absolute top-3 right-3 flex -space-x-2 overflow-hidden z-20">
                                            @foreach(collect($activeUsers[$hoja->id])->take(3) as $user)
                                                <img class="inline-block h-8 w-8 rounded-full ring-2 ring-white dark:ring-gray-800 object-cover" 
                                                     src="{{ $user['avatar'] ?? 'https://ui-avatars.com/api/?name='.urlencode($user['name']).'&color=7F9CF5&background=EBF4FF' }}" 
                                                     alt="{{ $user['name'] }}"
                                                     title="{{ $user['name'] }}">
                                            @endforeach
                                            @if(count($activeUsers[$hoja->id]) > 3)
                                                <div class="flex items-center justify-center h-8 w-8 rounded-full ring-2 ring-white dark:ring-gray-800 bg-gray-100 dark:bg-gray-700 text-xs font-medium text-gray-500 dark:text-gray-300 z-30">
                                                    +{{ count($activeUsers[$hoja->id]) - 3 }}
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                
                                    {{-- Area Badge --}}
                                    @php
                                        $areaColor = match($hoja->equipo->area) {
                                            \App\Area::TINTORERIA->value => 'bg-cyan-500',
                                            \App\Area::LAVANDERIA_INSTITUCIONAL->value => 'bg-indigo-500',
                                            \App\Area::CUARTO_DE_MAQUINAS->value => 'bg-slate-500',
                                            default => 'bg-gray-500'
                                        };
                                    @endphp
                                    <div class="absolute top-3 left-3">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold text-white {{ $areaColor }} shadow-lg backdrop-blur-md bg-opacity-90">
                                            {{ $hoja->equipo->area }}
                                        </span>
                                    </div>
                
                                    {{-- Tag Badge --}}
                                    <div class="absolute bottom-3 right-3">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-xs font-bold bg-white/90 dark:bg-black/70 text-gray-800 dark:text-gray-200 shadow-sm backdrop-blur-sm">
                                            {{ $hoja->equipo->nombre }}
                                        </span>
                                    </div>
                                </div>
                {{-- Card Content --}}
                <div class="flex-1 p-5 flex flex-col justify-between">
                    <div class="mb-4">
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white leading-tight line-clamp-2 mb-1 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                            {{ $hoja->equipo->tag }}
                        </h3>
                    </div>

                    <div class="pt-4 border-t border-gray-100 dark:border-gray-700">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-500 dark:text-gray-400">Último chequeo</span>
                            <span class="flex items-center font-medium {{ $hoja->latestChequeoDiario ? 'text-green-600 dark:text-green-400' : 'text-gray-400' }}">
                                @if($hoja->latestChequeoDiario)
                                    <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    {{ $hoja->latestChequeoDiario->finalizado_en->diffForHumans() }}
                                @else
                                    <span class="italic">Nunca</span>
                                @endif
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Empty State --}}
    @if($hojas->isEmpty())
        <div class="flex flex-col items-center justify-center py-12 text-center">
            <div class="w-16 h-16 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center mb-4">
                <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
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
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="font-medium">Cargando más equipos...</span>
            </div>
        </div>
    @endif
</div>

