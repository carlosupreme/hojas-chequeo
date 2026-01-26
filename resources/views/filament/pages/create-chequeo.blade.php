<x-filament-panels::page class="p-0! max-w-none!">
    {{-- We use a full-width container to break out of default Filament padding if needed --}}

    @if($hojaChequeo && $this->hasItems())
        <div data-animate="chequeo-items" class="min-h-screen bg-gray-50/50 dark:bg-gray-950 pb-20">

            {{-- 1. STICKY HEADER BAR --}}
            <div
                class="sticky top-0 z-30 bg-white/80 dark:bg-gray-900/80 backdrop-blur-md border-b border-gray-200 dark:border-gray-800 shadow-sm transition-all rounded-b-lg">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 py-4">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">

                        {{-- Left: Back & Title --}}
                        <div class="flex items-center gap-4">
                            <button
                                wire:click="resetState"
                                class="group flex items-center justify-center h-10 w-10 rounded-full bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white transition-all focus:outline-none focus:ring-2 focus:ring-blue-500"
                                title="Volver al listado"
                            >
                                @svg('heroicon-o-arrow-left', 'w-5 h-5 transform group-hover:-translate-x-0.5 transition-transform')
                            </button>

                            <div>
                                <div class="flex items-center gap-2 mb-1">
                                    <h1 class="text-xl font-bold text-gray-900 dark:text-white tracking-tight leading-none">
                                        {{ $this->hojaChequeo->equipo->nombre }}
                                    </h1>
                                    <span
                                        class="inline-flex items-center rounded-md bg-blue-50 px-2 py-0.5 text-xs font-bold text-blue-700 ring-1 ring-inset ring-blue-700/10 dark:bg-blue-900/30 dark:text-blue-400 dark:ring-blue-400/30 uppercase tracking-wide">
                                        {{ $this->hojaChequeo->equipo->tag }}
                                    </span>
                                </div>
                                <div
                                    class="flex items-center gap-3 text-xs text-gray-500 dark:text-gray-400 font-medium">
                                    <span class="flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path
                                                stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        {{ $user->turno->nombre }}
                                    </span>
                                    @if($this->hojaEjecucion)
                                        <span class="w-1 h-1 rounded-full bg-gray-300 dark:bg-gray-600"></span>
                                        <span class="text-amber-600 dark:text-amber-500 flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                                                 stroke="currentColor"><path stroke-linecap="round"
                                                                             stroke-linejoin="round" stroke-width="2"
                                                                             d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                            Reanudando
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Right: Date Picker (Compact) --}}
                        <div class="w-full sm:w-auto">
                            {{ $this->dateForm }}
                        </div>
                    </div>
                </div>
            </div>

            {{-- 2. MAIN CONTENT --}}
            <div class="max-w-7xl mx-auto px-0 py-8 space-y-8">

                {{-- Section A: Checklist Items (The Table) --}}
                <div
                    class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-800 overflow-hidden">
                    <div
                        class="px-6 py-4 border-b border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-800/50 flex justify-between items-center">
                        <h3 class="text-base font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                            <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                            </svg>
                            Items de Control
                        </h3>

                        {{-- Live Saving Indicator (Moved here for better visibility) --}}
                        <div
                            x-data="{ saving: false, init() { window.addEventListener('chequeo-form-updated', () => { this.saving = true; setTimeout(() => this.saving = false, 1000); }) } }"
                            class="h-6">
                            <span x-show="saving" x-transition
                                  class="text-xs font-medium text-blue-600 dark:text-blue-400 flex items-center gap-1.5 animate-pulse">
                                <svg class="w-3 h-3 animate-spin" fill="none" viewBox="0 0 24 24"><circle
                                        class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                        stroke-width="4"></circle><path class="opacity-75" fill="currentColor"
                                                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                Guardando...
                            </span>
                        </div>
                    </div>

                    <livewire:chequeo-items :hoja="$hojaChequeo" :ejecucion="$hojaEjecucion"/>
                </div>

                {{-- Section B: Finalization (Observations & Signature) --}}
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                    {{-- Left Column: Reference Info (Only renders if observations exist) --}}
                    @if($this->hojaChequeo->observaciones)
                        <div class="lg:col-span-1 space-y-6 animate-in slide-in-from-left-4 duration-500">
                            <div
                                class="bg-amber-50 dark:bg-amber-900/20 rounded-xl p-5 border border-amber-100 dark:border-amber-800/50 sticky top-24">
                                <h4 class="text-sm font-bold text-amber-800 dark:text-amber-500 uppercase tracking-wide mb-3 flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Instrucciones
                                </h4>
                                <div
                                    class="prose prose-sm prose-amber dark:prose-invert text-gray-700 dark:text-gray-300 max-w-none">
                                    {!! $this->hojaChequeo->observaciones !!}
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Right Column: Form Inputs --}}
                    {{-- LOGIC: If observations exist, take 2 columns. If not, take full width (3 columns) --}}
                    <div
                        class="{{ $this->hojaChequeo->observaciones ? 'lg:col-span-2' : 'lg:col-span-3' }} bg-white dark:bg-gray-900 rounded-xl p-6 md:p-8 shadow-sm border border-gray-200 dark:border-gray-800 transition-all duration-300">

                        <div
                            class="flex items-center justify-between mb-6 border-b border-gray-100 dark:border-gray-800 pb-4">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                    Cierre del Chequeo
                                </h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                    Por favor complete los datos finales para registrar la ejecución.
                                </p>
                            </div>
                            @if(!$this->hojaChequeo->observaciones)
                                {{-- Visual filler if no instructions are present --}}
                                <div class="hidden sm:block p-2 bg-gray-50 dark:bg-gray-800 rounded-lg text-gray-400">
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                            @endif
                        </div>

                        {{-- Filament Form --}}
                        <form wire:submit.prevent="create" class="space-y-6">

                            {{ $this->form }}

                            <div
                                class="pt-6 mt-6 border-t border-gray-100 dark:border-gray-800 flex flex-col sm:flex-row items-center justify-end gap-4">

                                {{-- Status Message (Optional) --}}
                                <div x-data="{ shown: false }"
                                     x-init="@this.on('chequeo-saved', () => { shown = true; setTimeout(() => shown = false, 2000) })"
                                     x-show="shown" x-transition
                                     class="text-green-600 dark:text-green-400 text-sm font-medium flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Guardado correctamente
                                </div>

                                <button type="submit"
                                        wire:loading.attr="disabled"
                                        class="w-full sm:w-auto inline-flex items-center justify-center rounded-lg bg-blue-600 px-8 py-3 text-sm font-semibold text-white shadow-md shadow-blue-500/20 hover:bg-blue-500 hover:shadow-lg hover:shadow-blue-500/30 focus-visible:outline focus-visible:outline-offset-2 focus-visible:outline-blue-600 transition-all hover:-translate-y-0.5 active:translate-y-0 disabled:opacity-70 disabled:cursor-not-allowed disabled:transform-none">

                                    {{-- Loading State --}}
                                    <span wire:loading class="flex items-center gap-2">
                                        <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle
                                                class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                                stroke-width="4"></circle><path class="opacity-75" fill="currentColor"
                                                                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                        Procesando...
                                    </span>

                                    {{-- Default State --}}
                                    <span wire:loading.remove class="flex items-center gap-2">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path
                                                stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                                        Finalizar y Guardar
                                    </span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @else
        <livewire:select-hoja-chequeo/>
    @endif
</x-filament-panels::page>
