<x-dynamic-component :component="$getEntryWrapperView()" :entry="$entry">
    @php
        $columns = [];
        $rows = [];

        if($getState()->first() && !is_null($getState()->first()->valores)) {
            $columns = array_keys($getState()->first()->valores);

            $getState()->each(function ($item) use ($columns, &$rows) {
                $row = [];
                array_map(function ($value) use ($item, $columns, &$row) {
                    if(is_null($item->valores))
                        $row[] = '';
                    else
                        $row[] = $item->valores[$value];
                }, $columns);

                if($item->alerta) {
                    $row[] = ($item->alerta->simbologia
                    ? $item->alerta->simbologia->icono
                    : 'Cuando tenga el valor: ' . $item->alerta->valor) . '`' . $item->alerta->contador;
                } else {
                  $row[] = 'No tiene`';
                }
                $rows[] = $row;
            });
        }

        $columns[] = 'Alerta';
    @endphp

    <div class="overflow-x-auto">
        @if(!$getState()->first() || !$getState()->first()->valores)
            <h1 class="text-gray-600 dark:text-white text-center w-full italic">No tiene items agregados</h1>
        @else
            <div class="min-w-full"> <!-- Contenedor para forzar el ancho mínimo -->
                <table class="w-full border-collapse">
                    <thead>
                    <tr>
                        <th class="border border-gray-300 dark:border-gray-600 px-4 py-2 bg-gray-100 dark:bg-gray-700 text-left left-0">
                            <span class="font-bold text-gray-800 dark:text-white">N°</span>
                        </th>
                        @foreach ($columns as $index => $column)
                            <th class="border border-gray-300 dark:border-gray-600 px-4 py-2 bg-gray-100 dark:bg-gray-700 text-left min-w-[150px]">
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-gray-800 dark:text-white truncate">{{ $column }}</span>
                                </div>
                            </th>
                        @endforeach
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($rows as $rowIndex => $row)
                        <tr class="{{ $loop->even ? 'bg-gray-50 dark:bg-gray-800' : '' }}">
                            <td class="border border-gray-300 dark:border-gray-600 px-4 py-2 text-gray-800 dark:text-gray-200 left-0 bg-inherit">
                                {{ $rowIndex + 1 }}
                            </td>
                            @foreach ($row as $colIndex => $cell)
                                <td class="border border-gray-300 dark:border-gray-600 px-4 py-2 group relative">
                                    <div class="flex items-center min-w-max">
                                        <!-- Contenedor para contenido de celda -->
                                        <span class="text-gray-800 dark:text-gray-200 break-words max-w-full">
                                            @if($loop->last)
                                                @if(!str_contains($cell,'Cuando tenga el valor') && !str_contains($cell,'No tiene'))
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
        @endif
    </div>
</x-dynamic-component>
