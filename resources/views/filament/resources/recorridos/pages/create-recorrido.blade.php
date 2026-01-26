<x-filament-panels::page class="relative">
    {{-- Fixed Progress Bar - Subtle --}}
    @if($this->formulario)
        <div class="fixed top-0 left-0 right-0 z-50 h-1 bg-gray-200 dark:bg-gray-800"
             x-data="{
                get progress() {
                    const total = {{ collect($formulario->categorias)->sum(fn($cat) => $cat->items->count()) }};
                    const filled = Object.values($wire.respuestas).filter(r => r.estado || r.valor_numerico || r.valor_texto).length;
                    return Math.round((filled / total) * 100);
                }
             }">
            <div
                class="h-full bg-linear-to-r from-blue-500 to-cyan-500 transition-all duration-300"
                :style="'width: ' + progress + '%'"
            ></div>
        </div>
    @endif

    <div class="min-h-screen">
        @if($this->formulario)
            {{-- Form View --}}
            <div class="mx-auto max-w-7xl">
                {{-- Header --}}
                <div class="px-4 py-4 sm:px-6 lg:px-8">
                    <div class="flex items-center gap-3">
                        <button
                            wire:click="resetState"
                            type="button"
                            class="shrink-0 inline-flex items-center justify-center w-10 h-10 rounded-lg text-gray-600 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800 transition-colors active:scale-95"
                            aria-label="Volver"
                        >
                            @svg('heroicon-o-arrow-left', 'h-5 w-5')
                        </button>

                        <div class="flex-1 items-center flex gap-4 min-w-0">
                            <h1 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-white truncate">
                                {{ $formulario->nombre }}
                            </h1>
                            <x-filament::badge color="warning">EDITANDO EXISTENTE</x-filament::badge>
                        </div>

                        <div class="shrink-0 text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400"
                             x-data="{
                                    get progress() {
                                        const total = {{ collect($formulario->categorias)->sum(fn($cat) => $cat->items->count()) }};
                                        const filled = Object.values($wire.respuestas).filter(r => r.estado || r.valor_numerico || r.valor_texto).length;
                                        return filled + '/' + total;
                                    }
                                 }"
                             x-text="progress">
                        </div>
                    </div>

                    {{-- Date & Shift --}}
                    <div class="mt-4">
                        {{ $this->form }}
                    </div>
                </div>

                {{-- Form Content --}}
                <div class="px-4 py-6 sm:px-6 lg:px-8">
                    <div class="space-y-6">
                        @foreach($formulario->categorias as $categoria)
                            <section
                                class="bg-gray-50 dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-800 overflow-hidden">
                                {{-- Category Header --}}
                                <div
                                    class="px-4 py-3 sticky bg-gray-200  dark:bg-gray-700 border-b border-gray-200 dark:border-gray-700">
                                    <h2 class="text-sm font-semibold text-gray-900 dark:text-white uppercase tracking-wide">
                                        {{ $categoria->nombre }}
                                    </h2>
                                </div>

                                {{-- Items --}}
                                <div class="divide-y divide-gray-100 dark:divide-gray-800">
                                    @foreach($categoria->items as $item)
                                        <div class="px-4 py-5 sm:px-6">
                                            <div class="space-y-3">
                                                {{-- Item Label --}}
                                                <div class="flex items-start gap-3">
                                                    <span
                                                        class="shrink-0 inline-flex items-center justify-center w-6 h-6 rounded-full bg-gray-100 dark:bg-gray-800 text-xs font-medium text-gray-600 dark:text-gray-400">
                                                        {{ $loop->parent->iteration }}.{{ $loop->iteration }}
                                                    </span>
                                                    <p class="flex-1 text-sm font-medium text-gray-900 dark:text-white leading-relaxed">
                                                        {{ $item->nombre }}
                                                    </p>
                                                </div>

                                                {{-- Input Area --}}
                                                @if($item->isTipoEstado())
                                                    <div class="grid grid-cols-2 gap-2 sm:gap-3">
                                                        @php
                                                            $estados = \App\Models\ItemRecorrido::estados();
                                                        @endphp

                                                        @foreach($estados as $estado)
                                                            @php
                                                                $isSelected = ($respuestas[$item->id]['estado'] ?? '') === $estado['value'];
                                                            @endphp
                                                            <button
                                                                type="button"
                                                                wire:click="$set('respuestas.{{ $item->id }}.estado', '{{ $estado['value'] }}')"
                                                                class="group relative flex flex-col items-center justify-center h-16 sm:h-18 rounded-lg border-2 transition-all active:scale-[0.98] {{
                                                                    $isSelected
                                                                        ? 'border-' . $estado['color'] . '-500 bg-' . $estado['color'] . '-50 dark:bg-' . $estado['color'] . '-900/20 shadow-sm'
                                                                        : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800/50 hover:border-gray-300 dark:hover:border-gray-600'
                                                                }}"
                                                            >
                                                                <span
                                                                    class="text-xl sm:text-2xl mb-1 {{ $isSelected ? 'scale-110' : '' }} transition-transform">
                                                                    {{ $estado['emoji'] }}
                                                                </span>
                                                                <span class="text-[10px] sm:text-xs font-semibold text-center leading-tight px-1 {{
                                                                    $isSelected
                                                                        ? 'text-' . $estado['color'] . '-700 dark:text-' . $estado['color'] . '-300'
                                                                        : 'text-gray-600 dark:text-gray-400'
                                                                }}">
                                                                    {{ $estado['label'] }}
                                                                </span>
                                                            </button>
                                                        @endforeach
                                                    </div>
                                                @elseif($item->isTipoTexto())
                                                    <x-filament::input.wrapper>
                                                        <x-filament::input
                                                            type="text"
                                                            class="w-full"
                                                            wire:model.live="respuestas.{{ $item->id }}.valor_texto"/>
                                                    </x-filament::input.wrapper>
                                                @else
                                                    <x-filament::input.wrapper>
                                                        <x-filament::input
                                                            type="number"
                                                            class="w-full"
                                                            placeholder="0.00"
                                                            wire:model.live="respuestas.{{ $item->id }}.valor_numerico"/>
                                                    </x-filament::input.wrapper>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </section>
                        @endforeach
                    </div>
                </div>

                {{-- Fixed Bottom Action --}}
                <div class="mx-auto max-w-7xl px-4 py-4 sm:px-6 lg:px-8">
                    <button
                        type="button"
                        wire:click="guardar"
                        wire:loading.attr="disabled"
                        wire:target="guardar"
                        class="relative w-full h-12 sm:h-14 rounded-lg bg-blue-500 text-white font-semibold text-sm sm:text-base shadow-lg transition-all active:scale-[0.99] disabled:opacity-60 disabled:cursor-not-allowed"
                    >
                        <span class="flex items-center justify-center gap-2">
                            <span wire:loading.remove wire:target="guardar">
                                @svg('heroicon-o-check-circle', 'h-5 w-5')
                            </span>

                            <span wire:loading.remove wire:target="guardar">Finalizar</span>

                            <span wire:loading wire:target="guardar">
                                <svg class="h-5 w-5 animate-spin" viewBox="0 0 24 24">
                                    <circle
                                        class="opacity-25"
                                        cx="12"
                                        cy="12"
                                        r="10"
                                        stroke="currentColor"
                                        stroke-width="4"
                                        fill="none"
                                    />
                                    <path
                                        class="opacity-75"
                                        fill="currentColor"
                                        d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"
                                    />
                                </svg>
                            </span>

                            <span wire:loading wire:target="guardar">Guardando...</span>
                        </span>
                    </button>
                </div>
            </div>
        @else
            {{-- Selection View --}}
            <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                <div class="text-center mb-8">
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white mb-2">
                        Selecciona un Recorrido
                    </h1>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Elige el recorrido que deseas completar
                    </p>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    @php
                        $colors = ['rose', 'orange', 'emerald', 'blue'];
                        $emojis = ['🏭', '⚙️', '🔍', '📊'];
                    @endphp

                    @foreach($formularios as $index => $formulario)
                        @php
                            $color = $colors[$index % 4];
                            $emoji = $emojis[$index % 4];
                        @endphp

                        <button
                            wire:click="selectFormulario({{ $formulario->id }})"
                            type="button"
                            class="group relative overflow-hidden rounded-xl bg-white dark:bg-gray-900 border-2 border-gray-200 dark:border-gray-700 p-6 text-left transition-all active:scale-[0.98] hover:border-{{ $color }}-300 dark:hover:border-{{ $color }}-700 shadow-sm hover:shadow-md"
                        >
                            <div
                                class="absolute top-0 right-0 w-32 h-32 bg-{{ $color }}-500/5 dark:bg-{{ $color }}-500/10 rounded-full -mr-16 -mt-16 transition-transform group-hover:scale-110"></div>

                            <div class="relative flex items-center gap-4">
                                <div
                                    class="shrink-0 w-14 h-14 sm:w-16 sm:h-16 rounded-xl bg-linear-to-br from-{{ $color }}-500 to-{{ $color }}-600 flex items-center justify-center text-2xl sm:text-3xl shadow-md transition-transform group-active:rotate-6">
                                    {{ $emoji }}
                                </div>

                                <div class="flex-1 min-w-0">
                                    <h3 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-white mb-1 leading-snug">
                                        {{ $formulario->nombre }}
                                    </h3>

                                    @if($formulario->categorias_count ?? $formulario->categorias->count())
                                        <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 flex items-center gap-1.5">
                                            <span class="text-{{ $color }}-500 dark:text-{{ $color }}-400">●</span>
                                            {{ $formulario->categorias_count ?? $formulario->categorias->count() }}
                                            {{ Str::plural('categoría', $formulario->categorias_count ?? $formulario->categorias->count()) }}
                                        </p>
                                    @endif
                                </div>

                                <div class="shrink-0">
                                    @svg('heroicon-o-chevron-right', 'h-5 w-5 sm:h-6 sm:w-6 text-gray-400 dark:text-gray-500 transition-transform group-hover:translate-x-1')
                                </div>
                            </div>

                            <div
                                class="absolute bottom-0 left-0 right-0 h-1 bg-linear-to-r from-{{ $color }}-500 to-{{ $color }}-600 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                        </button>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
