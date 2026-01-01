@php
    $record = $getRecord();
    $columnas = $record->columnas->sortBy('order');
    $filas = $record->filas->sortBy('order');
@endphp

<div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
        <thead class="bg-gray-50 dark:bg-gray-800">
            <tr>
                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider border-r border-gray-200 dark:border-gray-700">
                    #
                </th>
                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider border-r border-gray-200 dark:border-gray-700">
                    Categoría
                </th>
                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider border-r border-gray-200 dark:border-gray-700">
                    Tipo de Respuesta
                </th>
                @foreach($columnas as $columna)
                    <th class="px-3 py-2 text-left text-xs font-medium uppercase tracking-wider border-r border-gray-200 dark:border-gray-700 last:border-r-0 text-gray-500 dark:text-gray-400">
                        <div class="flex items-center gap-1.5">
                            <span>{{ $columna->label }}</span>
                        </div>
                     </th>
                @endforeach
            </tr>
        </thead>
        <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
            @forelse($filas as $index => $fila)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                    <td class="px-3 py-2 whitespace-nowrap text-xs font-medium text-gray-500 dark:text-gray-400 border-r border-gray-200 dark:border-gray-700">
                        {{ $index + 1 }}
                    </td>
                    <td class="px-3 py-2 whitespace-nowrap border-r border-gray-200 dark:border-gray-700">
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ match($fila->categoria) {
                            'limpieza' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
                            'operacion' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
                            'revision' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
                            default => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400',
                        } }}">
                            {{ match($fila->categoria) {
                                'limpieza' => '🧹 Limpieza',
                                'operacion' => '⚙️ Operación',
                                'revision' => '🔍 Revisión',
                                default => $fila->categoria,
                            } }}
                        </span>
                    </td>
                    <td class="px-3 py-2 whitespace-nowrap border-r border-gray-200 dark:border-gray-700">
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400">
                            {{ $fila->answerType->label ?? 'N/A' }}
                        </span>
                    </td>
                    @foreach($columnas as $columna)
                        @php
                            $valor = $fila->valores->where('hoja_columna_id', $columna->id)->first();
                        @endphp
                        <td class="px-3 py-2 text-xs text-gray-600 dark:text-gray-300 border-r border-gray-200 dark:border-gray-700 last:border-r-0 bg-gray-50 dark:bg-gray-800/50 ">
                            @if($valor)
                                <div class="max-w-xs truncate" title="{{ $valor->valor }}">
                                    {{ $valor->valor }}
                                </div>
                            @else
                                <span class="text-gray-400 dark:text-gray-600 italic">—</span>
                            @endif
                        </td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ 3 + count($columnas) }}" class="px-3 py-8 text-center text-gray-500 dark:text-gray-400">
                        <div class="flex flex-col items-center gap-2">
                            <svg class="w-12 h-12 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <p class="font-medium">No hay filas configuradas</p>
                            <p class="text-sm">Las filas aparecerán aquí una vez configuradas</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($columnas->count() > 0 && $filas->count() > 0)
    <div class="mt-3 flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
        <span>Mostrando {{ $filas->count() }} fila(s) × {{ $columnas->count() }} columna(s)</span>
    </div>
@endif
