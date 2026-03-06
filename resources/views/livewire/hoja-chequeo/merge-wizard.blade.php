<div>
    {{-- Trigger Button --}}
    @if(!$isOpen)
        <button
            wire:click="open"
            type="button"
            class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 transition-colors"
        >
            @svg('heroicon-o-document-duplicate', 'w-5 h-5')
            Fusionar versiones
        </button>
    @endif

    {{-- Full-screen Modal Overlay --}}
    @if($isOpen)
        <div class="fixed inset-0 z-50 overflow-hidden flex flex-col bg-white dark:bg-gray-950">

            {{-- Modal Header --}}
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-800 shrink-0">
                <div class="flex items-center gap-3">
                    @svg('heroicon-o-document-duplicate', 'w-6 h-6 text-indigo-600')
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Fusionar versiones</h2>
                </div>

                {{-- Step Indicator --}}
                <nav class="flex items-center gap-1" aria-label="Pasos">
                    @foreach([1 => 'Origenes', 2 => 'Columnas', 3 => 'Filas', 4 => 'Ejecutar'] as $step => $label)
                        <div class="flex items-center gap-1">
                            @if($step > 1)
                                <div class="w-8 h-px {{ $currentStep >= $step ? 'bg-indigo-400' : 'bg-gray-200 dark:bg-gray-700' }}"></div>
                            @endif
                            <button
                                type="button"
                                @if($step < $currentStep) wire:click="goToStep({{ $step }})" @endif
                                @class([
                                    'flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-medium transition-colors',
                                    'bg-indigo-600 text-white' => $currentStep === $step,
                                    'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300 cursor-pointer hover:bg-indigo-200' => $step < $currentStep,
                                    'bg-gray-100 text-gray-400 dark:bg-gray-800 dark:text-gray-500 cursor-default' => $step > $currentStep,
                                ])
                            >
                                <span>{{ $step }}</span>
                                <span class="hidden sm:inline">{{ $label }}</span>
                            </button>
                        </div>
                    @endforeach
                </nav>

                <button wire:click="close" type="button" class="rounded-full p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                    @svg('heroicon-o-x-mark', 'w-5 h-5')
                </button>
            </div>

            {{-- Error Message --}}
            @if($errorMessage)
                <div class="px-6 py-3 bg-red-50 dark:bg-red-900/20 border-b border-red-200 dark:border-red-800 text-sm text-red-700 dark:text-red-300 flex items-center gap-2">
                    @svg('heroicon-o-exclamation-triangle', 'w-4 h-4 shrink-0')
                    {{ $errorMessage }}
                </div>
            @endif

            {{-- Modal Body --}}
            <div class="flex-1 overflow-y-auto px-6 py-6">

                {{-- ─── STEP 1: Origenes ─── --}}
                @if($currentStep === 1)
                    <div class="max-w-2xl mx-auto">
                        <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-1">Selecciona las versiones a fusionar</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Selecciona al menos 2 versiones. Todas serán eliminadas y reemplazadas por una nueva v1.</p>

                        <div class="space-y-3">
                            @foreach($this->versions as $version)
                                <label class="flex items-center gap-4 rounded-xl border p-4 cursor-pointer transition-colors
                                    {{ in_array($version->id, $sourceVersionIds)
                                        ? 'border-indigo-400 bg-indigo-50 dark:bg-indigo-900/20 dark:border-indigo-600'
                                        : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600' }}">
                                    <input
                                        type="checkbox"
                                        wire:model="sourceVersionIds"
                                        value="{{ $version->id }}"
                                        class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                    >
                                    <div class="flex-1">
                                        <div class="flex items-center gap-3">
                                            <x-filament::badge color="primary">v{{ $version->version }}</x-filament::badge>
                                            @if($version->encendido)
                                                <x-filament::badge color="success">Publicada</x-filament::badge>
                                            @endif
                                        </div>
                                        <div class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                            {{ $version->chequeos_count }} {{ Str::plural('ejecución', $version->chequeos_count) }}
                                            &bull; Creada {{ $version->created_at->format('d/m/Y') }}
                                        </div>
                                    </div>
                                </label>
                            @endforeach
                        </div>

                        <div class="mt-2 text-xs text-gray-400 dark:text-gray-500">
                            {{ count($sourceVersionIds) }} de {{ $this->versions->count() }} versiones seleccionadas
                        </div>
                    </div>
                @endif

                {{-- ─── STEP 2: Columnas ─── --}}
                @if($currentStep === 2)
                    <div class="max-w-4xl mx-auto">
                        <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-1">Configura las columnas</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">
                            Elige qué columnas incluir en la versión fusionada. Puedes editar el label o seleccionar el label de una versión específica.
                        </p>

                        <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
                            <table class="min-w-full text-sm">
                                <thead class="bg-gray-50 dark:bg-gray-800">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase w-12">Incl.</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Key</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Label resultante</th>
                                        @foreach($this->versions->whereIn('id', $sourceVersionIds) as $v)
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                                v{{ $v->version }}
                                            </th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($columnDiff as $i => $col)
                                        <tr @class([
                                            'bg-white dark:bg-gray-900',
                                            'opacity-50' => !$col['included'],
                                        ])>
                                            <td class="px-4 py-3">
                                                <input
                                                    type="checkbox"
                                                    wire:model="columnDiff.{{ $i }}.included"
                                                    class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                                >
                                            </td>
                                            <td class="px-4 py-3 font-mono text-xs text-gray-500 dark:text-gray-400">{{ $col['key'] }}</td>
                                            <td class="px-4 py-3">
                                                <input
                                                    type="text"
                                                    wire:model="columnDiff.{{ $i }}.label"
                                                    class="w-full rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-1.5 text-sm text-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500"
                                                    {{ !$col['included'] ? 'disabled' : '' }}
                                                >
                                            </td>
                                            @foreach($this->versions->whereIn('id', $sourceVersionIds) as $v)
                                                <td class="px-4 py-3">
                                                    @if(isset($col['versions'][$v->id]))
                                                        <button
                                                            wire:click="useVersionLabel({{ $i }}, @js($col['versions'][$v->id]))"
                                                            type="button"
                                                            class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline"
                                                        >
                                                            {{ $col['versions'][$v->id] }}
                                                        </button>
                                                    @else
                                                        <span class="text-xs text-gray-300 dark:text-gray-600">—</span>
                                                    @endif
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                {{-- ─── STEP 3: Filas ─── --}}
                @if($currentStep === 3)
                    @php
                        $includedColKeys = collect($columnDiff)->where('included', true)->pluck('key')->toArray();
                        $includedColLabels = collect($columnDiff)->where('included', true)->pluck('label', 'key')->toArray();
                        $selectedVersions = $this->versions->whereIn('id', $sourceVersionIds);
                    @endphp

                    <div class="max-w-5xl mx-auto">
                        <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-1">Configura las filas</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">
                            Para cada posición de fila, elige si incluirla y de qué versión tomar los datos.
                        </p>

                        <div class="space-y-4">
                            @foreach($rowDiff as $i => $row)
                                <div @class([
                                    'rounded-xl border p-4 transition-colors',
                                    'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900' => $row['included'],
                                    'border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/50 opacity-60' => !$row['included'],
                                ])>
                                    <div class="flex items-center gap-4 mb-3">
                                        <input
                                            type="checkbox"
                                            wire:model="rowDiff.{{ $i }}.included"
                                            class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                        >
                                        <span class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                            Fila {{ $row['order'] + 1 }}
                                        </span>
                                        @if(!$row['included'])
                                            <span class="text-xs text-gray-400 dark:text-gray-500 italic">Excluida — sus respuestas serán eliminadas</span>
                                        @endif
                                    </div>

                                    @if($row['included'])
                                        {{-- Version selector + data comparison --}}
                                        <div class="grid gap-3" style="grid-template-columns: repeat({{ count($row['versions']) }}, 1fr);">
                                            @foreach($row['versions'] as $hojaId => $versionData)
                                                @php
                                                    $hoja = $selectedVersions->firstWhere('id', $hojaId);
                                                    $isSelected = (int)$row['source_hoja_id'] === (int)$hojaId;
                                                @endphp
                                                <label @class([
                                                    'block rounded-lg border-2 p-3 cursor-pointer transition-colors',
                                                    'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/20' => $isSelected,
                                                    'border-gray-200 dark:border-gray-700 hover:border-gray-300' => !$isSelected,
                                                ])>
                                                    <div class="flex items-center gap-2 mb-2">
                                                        <input
                                                            type="radio"
                                                            wire:model="rowDiff.{{ $i }}.source_hoja_id"
                                                            value="{{ $hojaId }}"
                                                            class="text-indigo-600 focus:ring-indigo-500"
                                                        >
                                                        <x-filament::badge color="{{ $isSelected ? 'primary' : 'gray' }}" size="sm">
                                                            v{{ $hoja?->version }}
                                                        </x-filament::badge>
                                                        <span class="text-xs text-gray-500 dark:text-gray-400">
                                                            {{ $versionData['answer_type_label'] ?? '—' }}
                                                        </span>
                                                        <span class="text-xs font-medium capitalize text-gray-600 dark:text-gray-300">
                                                            {{ $versionData['categoria'] }}
                                                        </span>
                                                    </div>
                                                    @if(count($includedColKeys))
                                                        <div class="space-y-1">
                                                            @foreach($includedColKeys as $key)
                                                                <div class="flex gap-2 text-xs">
                                                                    <span class="text-gray-400 dark:text-gray-500 shrink-0">
                                                                        {{ $includedColLabels[$key] ?? $key }}:
                                                                    </span>
                                                                    <span class="text-gray-700 dark:text-gray-300 truncate">
                                                                        {{ $versionData['valores'][$key] ?? '—' }}
                                                                    </span>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                </label>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- ─── STEP 4: Ejecutar ─── --}}
                @if($currentStep === 4)
                    @php
                        $includedColCount = collect($columnDiff)->where('included', true)->count();
                        $includedRowCount = collect($rowDiff)->where('included', true)->count();
                        $totalEjecuciones = $this->getTotalEjecuciones();
                        $selectedVersionsList = $this->versions->whereIn('id', $sourceVersionIds);
                    @endphp

                    <div class="max-w-2xl mx-auto">
                        <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-1">Confirmar fusión</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">
                            Revisa el resumen antes de ejecutar. Esta operación no se puede deshacer.
                        </p>

                        {{-- Summary Cards --}}
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
                            <div class="rounded-xl bg-indigo-50 dark:bg-indigo-900/20 p-4 text-center">
                                <div class="text-2xl font-bold text-indigo-700 dark:text-indigo-300">{{ count($sourceVersionIds) }}</div>
                                <div class="text-xs text-indigo-600 dark:text-indigo-400 mt-1">versiones → 1</div>
                            </div>
                            <div class="rounded-xl bg-blue-50 dark:bg-blue-900/20 p-4 text-center">
                                <div class="text-2xl font-bold text-blue-700 dark:text-blue-300">{{ $includedColCount }}</div>
                                <div class="text-xs text-blue-600 dark:text-blue-400 mt-1">columnas</div>
                            </div>
                            <div class="rounded-xl bg-green-50 dark:bg-green-900/20 p-4 text-center">
                                <div class="text-2xl font-bold text-green-700 dark:text-green-300">{{ $includedRowCount }}</div>
                                <div class="text-xs text-green-600 dark:text-green-400 mt-1">filas</div>
                            </div>
                            <div class="rounded-xl bg-amber-50 dark:bg-amber-900/20 p-4 text-center">
                                <div class="text-2xl font-bold text-amber-700 dark:text-amber-300">{{ $totalEjecuciones }}</div>
                                <div class="text-xs text-amber-600 dark:text-amber-400 mt-1">ejecuciones</div>
                            </div>
                        </div>

                        {{-- Versions list --}}
                        <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-4 mb-6">
                            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Versiones a eliminar:</h4>
                            <div class="space-y-2">
                                @foreach($selectedVersionsList as $v)
                                    <div class="flex items-center gap-3 text-sm">
                                        @svg('heroicon-o-trash', 'w-4 h-4 text-red-400 shrink-0')
                                        <x-filament::badge color="primary">v{{ $v->version }}</x-filament::badge>
                                        <span class="text-gray-500 dark:text-gray-400">
                                            {{ $v->chequeos_count }} {{ Str::plural('ejecución', $v->chequeos_count) }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                            <div class="mt-3 pt-3 border-t border-gray-100 dark:border-gray-800 flex items-center gap-3 text-sm font-medium text-indigo-700 dark:text-indigo-300">
                                @svg('heroicon-o-plus', 'w-4 h-4 shrink-0')
                                <span>Nueva v1 con {{ $totalEjecuciones }} {{ Str::plural('ejecución', $totalEjecuciones) }} migradas</span>
                            </div>
                        </div>

                        {{-- Danger Warning --}}
                        <div class="rounded-xl border border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-900/20 p-4 mb-6 flex gap-3">
                            @svg('heroicon-o-exclamation-triangle', 'w-5 h-5 text-red-500 shrink-0 mt-0.5')
                            <div class="text-sm text-red-700 dark:text-red-300">
                                <p class="font-semibold mb-1">Esta acción es irreversible</p>
                                <ul class="list-disc list-inside space-y-1 text-red-600 dark:text-red-400">
                                    <li>Las {{ count($sourceVersionIds) }} versiones seleccionadas serán eliminadas permanentemente.</li>
                                    <li>Las filas excluidas perderán todas sus respuestas históricas.</li>
                                    <li>Los perfiles con acceso a versiones antiguas serán actualizados a la nueva v1.</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif

            </div>

            {{-- Modal Footer --}}
            <div class="flex items-center justify-between px-6 py-4 border-t border-gray-200 dark:border-gray-800 shrink-0 bg-gray-50 dark:bg-gray-900">
                <button
                    wire:click="{{ $currentStep > 1 ? 'goToStep(' . ($currentStep - 1) . ')' : 'close' }}"
                    type="button"
                    class="inline-flex items-center gap-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                >
                    @svg('heroicon-o-chevron-left', 'w-4 h-4')
                    {{ $currentStep > 1 ? 'Atrás' : 'Cancelar' }}
                </button>

                @if($currentStep < 4)
                    <button
                        wire:click="{{ $currentStep === 1 ? 'loadSources' : 'goToStep(' . ($currentStep + 1) . ')' }}"
                        type="button"
                        class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500 transition-colors"
                    >
                        Siguiente
                        @svg('heroicon-o-chevron-right', 'w-4 h-4')
                    </button>
                @else
                    <button
                        wire:click="executeMerge"
                        wire:loading.attr="disabled"
                        type="button"
                        class="inline-flex items-center gap-2 rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-500 disabled:opacity-50 transition-colors"
                    >
                        <span wire:loading.remove wire:target="executeMerge">
                            @svg('heroicon-o-document-duplicate', 'w-4 h-4')
                            Ejecutar fusión
                        </span>
                        <span wire:loading wire:target="executeMerge">
                            @svg('heroicon-o-arrow-path', 'w-4 h-4 animate-spin')
                            Fusionando...
                        </span>
                    </button>
                @endif
            </div>

        </div>
    @endif
</div>
