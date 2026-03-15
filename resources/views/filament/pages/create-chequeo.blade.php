<x-filament-panels::page class="p-0! max-w-none!">
    @if($hojaChequeo && $this->hasItems())

        {{-- ================================================================
             FINALIZAR MODAL
        ================================================================ --}}
        <div
            x-data="{ open: false }"
            @keydown.escape.window="open = false"
            @open-finalizar-modal.window="open = true"
        >
            <div
                x-show="open"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                style="display:none"
                class="fixed inset-0 z-40 bg-black/50 backdrop-blur-sm"
                @click="open = false"
            ></div>

            <div
                x-show="open"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-4 scale-98"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 scale-98"
                style="display:none"
                class="fixed inset-0 z-50 flex items-center justify-center p-4"
            >
                <div class="w-full max-w-md bg-white dark:bg-gray-900 rounded-2xl shadow-2xl border border-gray-100 dark:border-gray-800 overflow-hidden">
                    <div class="flex items-center justify-between px-6 pt-5 pb-4 border-b border-gray-100 dark:border-gray-800">
                        <div>
                            <h2 class="text-lg font-bold text-gray-900 dark:text-white">Finalizar hoja</h2>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Firme para confirmar el chequeo</p>
                        </div>
                        <button type="button" @click="open = false"
                            class="p-2 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                    <form wire:submit.prevent="create" class="px-6 py-5 space-y-5">
                        {{ $this->signatureForm }}
                        <div class="flex gap-3 pt-2">
                            <button type="button" @click="open = false"
                                class="flex-1 px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                                Cancelar
                            </button>
                            <button type="submit" wire:loading.attr="disabled"
                                class="flex-1 px-4 py-2.5 rounded-lg bg-green-600 hover:bg-green-500 text-sm font-semibold text-white transition-colors disabled:opacity-60 flex items-center justify-center gap-2">
                                <span wire:loading wire:target="create">
                                    <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                    </svg>
                                </span>
                                <span wire:loading.remove wire:target="create">Confirmar y guardar</span>
                                <span wire:loading wire:target="create">Guardando…</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- ================================================================
             STICKY HEADER
        ================================================================ --}}
        <div
            x-data="{
                barLeft: 0,
                _sync() { const s = document.querySelector('.fi-sidebar'); this.barLeft = s ? Math.round(s.getBoundingClientRect().right) : 0; },
                init() { this._sync(); const s = document.querySelector('.fi-sidebar'); if (s) new ResizeObserver(() => this._sync()).observe(s); window.addEventListener('resize', () => this._sync()); }
            }"
            :style="{ left: barLeft + 'px' }"
            class="fixed top-0 right-0 z-30 bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700 shadow-sm">
            <div class="px-4 sm:px-5 h-14 flex items-center gap-3">

                {{-- Back --}}
                <button wire:click="resetState"
                    class="shrink-0 flex items-center justify-center h-9 w-9 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-500 dark:text-gray-400 transition-colors shadow-sm"
                    title="Volver">
                    @svg('heroicon-o-arrow-left', 'w-4 h-4')
                </button>

                {{-- Divider --}}
                <div class="h-6 w-px bg-gray-200 dark:bg-gray-700 shrink-0"></div>

                {{-- Title + badges --}}
                <div class="flex items-center gap-2.5 min-w-0">
                    <span class="font-bold text-gray-900 dark:text-white text-base truncate">
                        {{ $this->hojaChequeo->equipo->nombre }}
                    </span>
                    <span class="shrink-0 text-xs font-bold text-blue-700 dark:text-blue-300 bg-blue-50 dark:bg-blue-900/40 ring-1 ring-blue-600/20 dark:ring-blue-400/30 px-2 py-0.5 rounded-md uppercase tracking-wide">
                        {{ $this->hojaChequeo->equipo->tag }}
                    </span>
                    @if($this->hojaEjecucion)
                        <span class="shrink-0 text-xs font-medium text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-900/30 ring-1 ring-amber-500/20 px-2 py-0.5 rounded-md hidden sm:inline">
                            Reanudando
                        </span>
                    @endif
                    @if($esPpm)
                        <span class="shrink-0 flex items-center gap-1 text-xs font-bold text-amber-700 dark:text-amber-300 bg-amber-100 dark:bg-amber-900/40 ring-1 ring-amber-500/30 px-2 py-0.5 rounded-md hidden sm:inline-flex">
                            <span class="inline-block w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                            PPM
                        </span>
                    @endif
                </div>

                <div class="flex-1"></div>

                {{-- Autosave indicator --}}
                <div x-data="{
                        state: 'idle',
                        init() {
                            $wire.on('chequeo-autosave-saving', () => this.state = 'saving');
                            $wire.on('chequeo-autosave-saved',  () => {
                                this.state = 'saved';
                                setTimeout(() => this.state = 'idle', 2500);
                            });
                        }
                     }" class="h-5 flex items-center shrink-0">
                    <span x-show="state === 'saving'" x-transition class="text-xs text-blue-500 flex items-center gap-1.5">
                        <svg class="w-3 h-3 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        Guardando…
                    </span>
                    <span x-show="state === 'saved'" x-transition class="text-xs text-green-600 dark:text-green-400 flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                        Guardado
                    </span>
                </div>

                {{-- Date picker --}}
                <div class="shrink-0">
                    {{ $this->dateForm }}
                </div>

            </div>
        </div>

        {{-- ================================================================
             MAIN CONTENT
        ================================================================ --}}
        <div class="px-4 sm:px-5 pt-[calc(56px+1.25rem)] pb-24 space-y-5">

            {{-- PPM active banner --}}
            @if($esPpm)
                <div class="flex items-center justify-between gap-3 px-4 py-3 bg-amber-50 dark:bg-amber-950/40 rounded-xl border border-amber-200 dark:border-amber-800/60">
                    <div class="flex items-center gap-3">
                        <div class="shrink-0 w-8 h-8 rounded-lg bg-amber-100 dark:bg-amber-900/50 flex items-center justify-center">
                            @svg('heroicon-o-wrench-screwdriver', 'w-4 h-4 text-amber-600 dark:text-amber-400')
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-amber-800 dark:text-amber-300">Parada por mantenimiento activa</p>
                            <p class="text-xs text-amber-600 dark:text-amber-500 mt-0.5">Todos los ítems han sido marcados como realizados automáticamente.</p>
                        </div>
                    </div>
                    <button wire:click="deactivatePpm"
                        class="shrink-0 flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-amber-100 dark:bg-amber-900/50 hover:bg-amber-200 dark:hover:bg-amber-800/60 text-xs font-semibold text-amber-700 dark:text-amber-300 transition-colors border border-amber-200 dark:border-amber-700">
                        Desactivar PPM
                    </button>
                </div>
            @endif

            {{-- Items table card --}}
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm overflow-hidden">
                <div class="flex items-center justify-between px-5 sm:px-6 py-4 border-b border-gray-100 dark:border-gray-800 bg-gray-50/80 dark:bg-gray-800/50">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200 flex items-center gap-2">
                        @svg('heroicon-o-clipboard-document-check', 'w-4 h-4 text-gray-400 dark:text-gray-500')
                        Ítems de control
                    </h3>
                </div>

                <livewire:chequeo-items :hoja="$hojaChequeo" :ejecucion="$hojaEjecucion" />
            </div>

            {{-- Operator info + Observations --}}
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm p-5 sm:p-6">
                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200 mb-4">Datos del operador</h3>
                {{ $this->form }}
            </div>

        </div>

        {{-- ================================================================
             STICKY BOTTOM BAR  — sidebar-aware via ResizeObserver
        ================================================================ --}}
        <div
            x-data="{
                answered: 0,
                total: 0,
                barLeft: 0,
                _sync() {
                    const s = document.querySelector('.fi-sidebar');
                    this.barLeft = s ? Math.round(s.getBoundingClientRect().right) : 0;
                },
                init() {
                    $wire.on('progress-updated', ({ answered, total }) => {
                        this.answered = answered;
                        this.total = total;
                    });
                    this._sync();
                    const s = document.querySelector('.fi-sidebar');
                    if (s) new ResizeObserver(() => this._sync()).observe(s);
                    window.addEventListener('resize', () => this._sync());
                }
            }"
            :style="{ left: barLeft + 'px' }"
            class="fixed bottom-0 right-0 z-20 bg-white/95 dark:bg-gray-900/95 backdrop-blur-sm shadow-[0_-1px_0_0_rgba(0,0,0,0.06),0_-4px_16px_rgba(0,0,0,0.07)] dark:shadow-[0_-1px_0_0_rgba(255,255,255,0.06),0_-4px_16px_rgba(0,0,0,0.3)] {{ $esPpm ? 'border-t-2 border-t-amber-400 dark:border-t-amber-500' : 'border-t border-gray-200 dark:border-gray-700' }}"
        >
            <div class="px-4 sm:px-5 h-14 flex items-center gap-2 sm:gap-3">

                {{-- Left actions: PPM + Report --}}
                <div class="flex items-center gap-2 shrink-0">

                    {{-- PPM button --}}
                    @if($esPpm)
                        <button wire:click="deactivatePpm"
                            class="relative flex items-center gap-1.5 px-3 py-2 rounded-lg bg-amber-100 dark:bg-amber-900/50 hover:bg-amber-200 dark:hover:bg-amber-800/60 text-amber-700 dark:text-amber-300 text-sm font-semibold transition-colors border border-amber-300 dark:border-amber-700">
                            {{-- Pulsing dot --}}
                            <span class="relative flex h-2 w-2">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-amber-500"></span>
                            </span>
                            @svg('heroicon-o-wrench-screwdriver', 'w-4 h-4')
                            <span class="hidden sm:inline">PPM activo</span>
                        </button>
                    @else
                        <button wire:click="activatePpm"
                            class="flex items-center gap-1.5 px-3 py-2 rounded-lg bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-500 dark:text-gray-400 text-sm font-medium transition-colors border border-gray-200 dark:border-gray-700"
                            title="Parada por Mantenimiento — marcar todo como realizado">
                            @svg('heroicon-o-wrench-screwdriver', 'w-4 h-4')
                            <span class="hidden sm:inline">PPM</span>
                        </button>
                    @endif

                    {{-- Report action --}}
                    <div class="shrink-0 [&>button]:!text-xs [&>button]:!px-3 [&>button]:!py-2 [&>button]:!rounded-lg [&>button]:!border [&>button]:!border-gray-200 dark:[&>button]:!border-gray-700 [&>button]:!bg-gray-100 dark:[&>button]:!bg-gray-800 [&>button]:!text-gray-600 dark:[&>button]:!text-gray-400 [&>button]:hover:!bg-gray-200 dark:[&>button]:hover:!bg-gray-700 [&>button]:!shadow-none [&>button]:!font-medium">
                        {{ $this->reportAction }}
                    </div>

                </div>

                <div class="flex-1"></div>

                {{-- Progress --}}
                <div class="flex items-center gap-3 shrink-0">
                    <div class="hidden sm:block w-28 h-2 bg-gray-100 dark:bg-gray-800 rounded-full overflow-hidden">
                        <div
                            class="h-full rounded-full transition-all duration-500 ease-out {{ $esPpm ? 'bg-amber-400' : 'bg-green-500' }}"
                            :style="'width: ' + (total > 0 ? Math.round((answered / total) * 100) : 0) + '%'"
                        ></div>
                    </div>
                    <span class="text-sm font-semibold tabular-nums {{ $esPpm ? 'text-amber-600 dark:text-amber-400' : 'text-gray-700 dark:text-gray-300' }}">
                        <span x-text="answered">0</span><span class="text-gray-400 dark:text-gray-600 font-normal">/</span><span x-text="total">0</span>
                    </span>
                </div>

                {{-- Finalizar --}}
                <button
                    type="button"
                    @click="$dispatch('open-finalizar-modal')"
                    class="shrink-0 flex items-center gap-2 px-5 py-2.5 rounded-xl bg-green-600 hover:bg-green-500 active:bg-green-700 text-sm font-semibold text-white transition-colors shadow-sm shadow-green-600/20">
                    @svg('heroicon-o-check-circle', 'w-4 h-4')
                    <span class="hidden sm:inline">Finalizar hoja</span>
                    <span class="sm:hidden">Finalizar</span>
                </button>

            </div>
        </div>

    @else
        <livewire:select-hoja-chequeo />
    @endif
</x-filament-panels::page>
