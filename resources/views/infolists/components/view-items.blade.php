<x-dynamic-component :component="$getEntryWrapperView()" :entry="$entry">
    @php
        $columns = [];
        $limpieza = [];
        $operacion = [];
        $revision = [];

        if ($getState()->isNotEmpty() && $getState()->first()->valores) {
            $columns = array_keys($getState()->first()->valores);
            $columns[] = 'Alerta'; // Add Alerta column

            $getState()->each(function ($item) use ($columns, &$limpieza, &$operacion, &$revision) {
                $row = [];

                // Process valores columns
                foreach (array_slice($columns, 0, -1) as $col) { // Exclude Alerta column
                    $row[] = $item->valores[$col] ?? '';
                }

                // Process Alerta
                if ($item->alerta) {
                    $alertaValue = $item->alerta->simbologia
                        ? $item->alerta->simbologia->icono
                        : 'Cuando tenga el valor: ' . $item->alerta->valor;
                    $alertaValue .= '`' . $item->alerta->contador;
                } else {
                    $alertaValue = 'No tiene`';
                }
                $row[] = $alertaValue;

                // Sort into categories
                switch ($item->categoria) {
                    case 'limpieza':
                        $limpieza[] = $row;
                        break;
                    case 'operacion':
                        $operacion[] = $row;
                        break;
                    case 'revision':
                        $revision[] = $row;
                        break;
                }
            });
        }

        $categories = [
            'limpieza' => ['rows' => $limpieza, 'label' => 'Limpieza'],
            'operacion' => ['rows' => $operacion, 'label' => 'Operación'],
            'revision' => ['rows' => $revision, 'label' => 'Revisión'],
        ];
    @endphp

    <div class="space-y-8 -mt-4 overflow-x-auto">
        @if(empty($limpieza) && empty($operacion) && empty($revision))
            <h1 class="text-gray-600 dark:text-white text-center w-full italic">No tiene items agregados</h1>
        @else
            @foreach($categories as $category)
                @if(!empty($category['rows']))
                    <div>
                        <h2 class="text-lg font-semibold my-4 dark:text-white flex items-center gap-2">
                            <span>{{ $category['label'] }} </span> <span>
                                @if($category['label'] === 'Limpieza')
                                    <x-heroicon-o-trash class="w-6 h-6 text-gray-600 dark:text-gray-400"/>
                                @elseif($category['label'] === 'Operación')
                                    <x-heroicon-o-cog-6-tooth class="w-6 h-6 text-blue-600 dark:text-blue-400"/>
                                @elseif($category['label'] === 'Revisión')
                                    <x-heroicon-o-eye class="w-6 h-6 text-green-600 dark:text-green-400"/>
                                @endif
                            </span>
                        </h2>
                        <div class="min-w-full">
                            <div class="overflow-x-auto pb-2">
                                <div class="min-w-[600px]">
                                    <table
                                        class="w-full border-collapse bg-white dark:bg-gray-800 shadow-xs rounded-lg overflow-hidden">
                                        <thead>
                                        <tr>
                                            <th class="border-b border-gray-200 dark:border-gray-700 px-6 py-3 bg-gray-50 dark:bg-gray-900 text-left left-0 sticky">
                                                <span
                                                    class="font-semibold text-gray-900 dark:text-white text-sm uppercase tracking-wider">N°</span>
                                            </th>
                                            @foreach ($columns as $column)
                                                <th class="border-b border-gray-200 dark:border-gray-700 px-6 py-3 bg-gray-50 dark:bg-gray-900 text-left min-w-[150px]">
                                                    <div class="flex items-center justify-between">
                                                        <span
                                                            class="font-semibold text-gray-900 dark:text-white text-sm uppercase tracking-wider truncate">{{ $column }}</span>
                                                    </div>
                                                </th>
                                            @endforeach
                                        </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                        @foreach ($category['rows'] as $rowIndex => $row)
                                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-200">
                                                <td class="border-r border-gray-200 dark:border-gray-700 px-6 py-4 text-gray-900 dark:text-gray-100 left-0 sticky bg-inherit font-medium">
                                                    {{ $rowIndex + 1 }}
                                                </td>
                                                @foreach ($row as $colIndex => $cell)
                                                    <td class="px-6 py-4 group relative">
                                                        <div class="flex items-center min-w-max">
                                                        <span
                                                            class="text-gray-700 dark:text-gray-300 wrap-break-word max-w-full text-sm">
                                                            @if($loop->last)
                                                                @if(!str_contains($cell, 'Cuando tenga el valor') && !str_contains($cell, 'No tiene'))
                                                                    <div class="flex items-center gap-3">
                                                                        @svg(explode('`', $cell)[0], 'w-5 h-5 shrink-0 text-gray-500 dark:text-gray-400')
                                                                        <x-filament::badge
                                                                            size="sm"
                                                                            color="warning"
                                                                            class="whitespace-normal max-w-full wrap-break-word font-medium">
                                                                            {{ explode('`', $cell)[1] }}
                                                                        </x-filament::badge>
                                                                    </div>
                                                                @else
                                                                    <span class="flex items-center gap-3">
                                                                        <x-filament::badge
                                                                            size="sm"
                                                                            class="whitespace-normal max-w-full wrap-break-word font-medium">
                                                                            {{ explode('`', $cell)[0] }}
                                                                        </x-filament::badge>
                                                                        @if(!empty(explode('`', $cell)[1]))
                                                                            <x-filament::badge
                                                                                size="sm"
                                                                                color="warning"
                                                                                class="whitespace-normal max-w-full wrap-break-word font-medium">
                                                                                {{ explode('`', $cell)[1] }}
                                                                            </x-filament::badge>
                                                                        @endif
                                                                    </span>
                                                                @endif
                                                            @else
                                                                <span class="inline-block max-w-full break-all">
                                                                    {{ $cell }}
                                                                </span>
                                                            @endif
                                                        </span>
                                                        </div>
                                                    </td>
                                                @endforeach
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach
        @endif
    </div>
</x-dynamic-component>
