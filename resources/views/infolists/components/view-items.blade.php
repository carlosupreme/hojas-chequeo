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
                            <div class="overflow-x-auto pb-2">  <!-- Added pb-2 for scrollbar spacing -->
                                <div class="min-w-[600px]">  <!-- Or your minimum required width -->
                                    <table class="w-full border-collapse ">
                                        <thead>
                                        <tr>
                                            <th class="border border-gray-300 dark:border-gray-600 px-4 py-2 bg-gray-100 dark:bg-gray-700 text-left left-0">
                                                <span class="font-bold text-gray-800 dark:text-white">N°</span>
                                            </th>
                                            @foreach ($columns as $column)
                                                <th class="border border-gray-300 dark:border-gray-600 px-4 py-2 bg-gray-100 dark:bg-gray-700 text-left min-w-[150px]">
                                                    <div class="flex items-center justify-between">
                                                        <span
                                                            class="font-bold text-gray-800 dark:text-white truncate">{{ $column }}</span>
                                                    </div>
                                                </th>
                                            @endforeach
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach ($category['rows'] as $rowIndex => $row)
                                            <tr class="{{ $loop->even ? 'bg-gray-50 dark:bg-gray-800' : '' }}">
                                                <td class="border border-gray-300 dark:border-gray-600 px-4 py-2 text-gray-800 dark:text-gray-200 left-0 bg-inherit">
                                                    {{ $rowIndex + 1 }}
                                                </td>
                                                @foreach ($row as $colIndex => $cell)
                                                    <td class="border border-gray-300 dark:border-gray-600 px-4 py-2 group relative">
                                                        <div class="flex items-center min-w-max">
                                                        <span
                                                            class="text-gray-800 dark:text-gray-200 break-words max-w-full">
                                                            @if($loop->last)
                                                                @if(!str_contains($cell, 'Cuando tenga el valor') && !str_contains($cell, 'No tiene'))
                                                                    <div class="flex items-center gap-2">
                                                                        @svg(explode('`', $cell)[0], 'w-6 h-6 shrink-0')
                                                                        <x-filament::badge
                                                                            size="sm"
                                                                            color="warning"
                                                                            class="whitespace-normal max-w-full break-words">
                                                                            {{ explode('`', $cell)[1] }}
                                                                        </x-filament::badge>
                                                                    </div>
                                                                @else
                                                                    <span class="flex items-center gap-2">
                                                                        <x-filament::badge
                                                                            size="sm"
                                                                            class="whitespace-normal max-w-full break-words">
                                                                            {{ explode('`', $cell)[0] }}
                                                                        </x-filament::badge>
                                                                        @if(!empty(explode('`', $cell)[1]))
                                                                            <x-filament::badge
                                                                                size="sm"
                                                                                color="warning"
                                                                                class="whitespace-normal max-w-full break-words">
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
