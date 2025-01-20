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
                $rows[] = $row;
            });
        }
    @endphp

    <div class="overflow-x-auto">
        @if(!$getState()->first() || !$getState()->first()->valores)
            <h1 class=" text-gray-600 dark:text-white text-center w-full italic">No tiene items agregados</h1>
        @else
            <table class="w-full border-collapse rounded-lg overflow-hidden">
                <thead>
                <tr>
                    <th class="border border-gray-300 dark:border-gray-600 px-4 py-2 bg-gray-100 dark:bg-gray-700 text-left">
                        <span class="font-bold text-gray-800 dark:text-white">N°</span>
                    </th>
                    @foreach ($columns as $index => $column)
                        <th class="border border-gray-300 dark:border-gray-600 px-4 py-2 bg-gray-100 dark:bg-gray-700 text-left">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-gray-800 dark:text-white">{{ $column }}</span>
                            </div>
                        </th>
                    @endforeach
                </tr>
                </thead>
                <tbody>
                @foreach ($rows as $rowIndex => $row)
                    <tr class="{{ $loop->even ? 'bg-gray-50 dark:bg-gray-800' : '' }}">
                        <td class="border border-gray-300 dark:border-gray-600 px-4 py-2 text-gray-800 dark:text-gray-200">
                            {{ $rowIndex + 1 }}
                        </td>
                        @foreach ($row as $colIndex => $cell)
                            <td class="border border-gray-300 dark:border-gray-600 px-4 py-2"
                                wire:key="cell-{{$rowIndex}}-{{$colIndex}}">
                                <div class="flex items-center justify-between">
                                    <span class="text-gray-800 dark:text-gray-200">{{ $cell }}</span>
                                </div>
                            </td>
                        @endforeach
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif
    </div>
</x-dynamic-component>
