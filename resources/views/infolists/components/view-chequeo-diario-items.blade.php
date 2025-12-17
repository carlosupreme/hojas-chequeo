<x-dynamic-component :component="$getEntryWrapperView()" :entry="$entry">
    @php
        $columns = [];
        $items = [];

        if ($getState()->isNotEmpty()) {
            $item = $getState()->first()->item;

            if (isset($item->valores)) {
                $columns = array_filter(array_keys($item->valores), function($col) {
                    return $col !== 'id';
                });
            }

            $getState()->each(function ($item) use (&$items) {

                $valores = $item->item->valores;
                if ($valores) {
                    $items[] = [
                        'valores'    => $valores,
                        'simbologia' => $item->simbologia_id,
                        'valor'      => $item->valor,
                    ];
                }
            });
        }
    @endphp
    <div class="space-y-8 -mt-4 overflow-x-auto">
        @if(empty($items))
            <h1 class="text-gray-600 dark:text-white text-center w-full italic">No tiene items agregados</h1>
        @else
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
                                @foreach($columns as $column)
                                    <th class="border-b border-gray-200 dark:border-gray-700 px-6 py-3 bg-gray-50 dark:bg-gray-900 text-left min-w-[150px]">
                                        <span
                                            class="font-semibold text-gray-900 dark:text-white text-sm uppercase tracking-wider truncate">{{ $column }}</span>
                                    </th>
                                @endforeach
                                <th class="border-b border-gray-200 dark:border-gray-700 px-6 py-3 bg-gray-50 dark:bg-gray-900 text-left left-0 sticky">
                                    <span
                                        class="font-semibold text-gray-900 dark:text-white text-sm uppercase tracking-wider">Chequeo</span>
                                </th>
                            </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($items as $index => $item)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-200">
                                    <td class="border-r border-gray-200 dark:border-gray-700 px-6 py-4 text-gray-900 dark:text-gray-100 left-0 sticky bg-inherit font-medium">{{ $index + 1 }}</td>
                                    @foreach($columns as $column)
                                        <td class="px-6 py-4">
                                            <div class="flex items-center min-w-max">
                                                <span
                                                    class="text-gray-700 dark:text-gray-300 wrap-break-word max-w-full text-sm">{{ $item['valores'][$column] }}</span>
                                            </div>
                                        </td>
                                    @endforeach
                                    <td class="px-6 py-4">
                                        <div class="flex items-center min-w-max">
                                            @if(!is_null($item['simbologia']))
                                                @php
                                                    $simbologia = \App\Models\Simbologia::find($item['simbologia']);
                                                @endphp
                                                <x-dynamic-component
                                                    :component="$simbologia->icono"
                                                    class="w-6 h-6"
                                                    style="color: {{ $simbologia->color }}"
                                                />
                                            @else
                                                <span
                                                    class="text-gray-700 dark:text-gray-300 wrap-break-word max-w-full text-sm">{{$item['valor']}}</span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-dynamic-component>
