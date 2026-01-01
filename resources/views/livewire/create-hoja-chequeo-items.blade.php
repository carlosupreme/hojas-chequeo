<div class="space-y-6" x-data="{
    hoveredColumn: null,
    hoveredRow: null,
    showColumnTooltip: false,
    showRowTooltip: false
}">
    {{-- Header Section --}}
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Configurar Columnas y Filas</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Define las columnas y filas de la hoja de chequeo</p>
        </div>
    </div>

    {{-- Table Container --}}
    <div class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                {{-- Table Header - Columns --}}
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        {{-- Fixed header cells --}}
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider whitespace-nowrap">
                            Categoría
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider whitespace-nowrap">
                            Tipo de Respuesta
                        </th>

                        {{-- Dynamic Columns --}}
                        @foreach($columnas as $columnId => $columna)
                            <th
                                class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider min-w-48 relative group"
                                x-bind:class="{
                                    'bg-amber-50 dark:bg-amber-900/20': {{ $columna['is_fixed'] ? 'true' : 'false' }},
                                    'bg-blue-50 dark:bg-blue-900/20': {{ $columna['is_fixed'] ? 'false' : 'true' }}
                                }"
                                x-on:mouseenter="hoveredColumn = '{{ $columnId }}'"
                                x-on:mouseleave="hoveredColumn = null"
                            >
                                {{-- Column Label Input --}}
                                <input
                                    type="text"
                                    wire:model.live="columnas.{{ $columnId }}.label"
                                    placeholder="Nombre de columna"
                                    class="w-full px-3 py-2 text-sm font-medium border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    {{ $columna['is_fixed'] ? 'readonly' : '' }}
                                />

                                {{-- Delete Column Button --}}
                                <button
                                    type="button"
                                    wire:click="removeColumna('{{ $columnId }}')"
                                    x-show="hoveredColumn === '{{ $columnId }}'"
                                    x-transition
                                    class="absolute top-2 right-2 p-1.5 bg-red-500 hover:bg-red-600 text-white rounded-lg opacity-0 group-hover:opacity-100 transition-opacity shadow-lg"
                                    title="Eliminar columna"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </th>
                        @endforeach

                        {{-- Add Column Button --}}
                        <th class="px-4 py-3">
                            <button
                                type="button"
                                wire:click="addColumna"
                                class="flex items-center gap-2 px-3 py-2 bg-blue-500 hover:bg-blue-600 text-white text-sm font-medium rounded-lg transition-colors"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                Columna
                            </button>
                        </th>
                    </tr>
                </thead>

                {{-- Table Body - Rows --}}
                <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($filas as $filaId => $fila)
                        <tr
                            class="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors group"
                            x-on:mouseenter="hoveredRow = '{{ $filaId }}'"
                            x-on:mouseleave="hoveredRow = null"
                        >
                            {{-- Categoría Select --}}
                            <td class="px-4 py-3 whitespace-nowrap">
                                <select
                                    wire:model.live="filas.{{ $filaId }}.categoria"
                                    class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                >
                                    <option value="limpieza">🧹 Limpieza</option>
                                    <option value="operacion">⚙️ Operación</option>
                                    <option value="revision">🔍 Revisión</option>
                                </select>
                            </td>

                            {{-- Answer Type Select --}}
                            <td class="px-4 py-3 whitespace-nowrap">
                                <select
                                    wire:model.live="filas.{{ $filaId }}.answer_type_id"
                                    class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    required
                                >
                                    <option value="">Seleccionar tipo...</option>
                                    @foreach($answerTypes as $answerType)
                                        <option value="{{ $answerType['id'] }}">
                                            {{ $answerType['label'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>

                            {{-- Valor Cells --}}
                            @foreach($columnas as $columnId => $columna)
                                <td
                                    class="px-4 py-3 bg-gray-50 dark:bg-gray-800/50 relative"
                                    x-bind:class="{
                                        'ring-2 ring-blue-400 ring-inset': hoveredColumn === '{{ $columnId }}' || hoveredRow === '{{ $filaId }}'
                                    }"
                                >
                                    <input
                                        type="text"
                                        wire:model.blur="valores.{{ $filaId }}.{{ $columnId }}"
                                        placeholder="Valor..."
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-900 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                                    />
                                </td>
                            @endforeach

                            {{-- Delete Row Button --}}
                            <td class="px-4 py-3">
                                <button
                                    type="button"
                                    wire:click="removeFila('{{ $filaId }}')"
                                    x-show="hoveredRow === '{{ $filaId }}'"
                                    x-transition
                                    class="p-2 bg-red-500 hover:bg-red-600 text-white rounded-lg opacity-0 group-hover:opacity-100 transition-opacity"
                                    title="Eliminar fila"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Add Row Button --}}
    <div class="flex justify-start">
        <button
            type="button"
            wire:click="addFila"
            class="flex items-center gap-2 px-4 py-2 bg-green-500 hover:bg-green-600 text-white font-medium rounded-lg transition-colors shadow-sm hover:shadow-md"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Fila
        </button>
    </div>

    {{-- Summary Stats --}}
    <div class="flex gap-4 text-sm text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
        <div class="flex items-center gap-2">
            <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
            </svg>
            <span class="font-medium">{{ count($columnas) }}</span> columna(s)
        </div>
        <div class="flex items-center gap-2">
            <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
            </svg>
            <span class="font-medium">{{ count($filas) }}</span> fila(s)
        </div>
        <div class="flex items-center gap-2">
            <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>
            </svg>
            <span class="font-medium">{{ count($columnas) * count($filas) }}</span> celda(s) total
        </div>
    </div>
</div>
