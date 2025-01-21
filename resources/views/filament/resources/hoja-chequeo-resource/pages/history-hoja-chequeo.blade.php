<x-filament-panels::page>
    <div class="flex items-center gap-4">
        <x-filament::badge class="max-w-fit" color="warning"> Version: {{$record->version}}</x-filament::badge>
        <x-filament::badge class="max-w-fit"> Area: {{$record->equipo->area}}</x-filament::badge>
        <x-filament::badge class="max-w-fit"> Tag: {{$record->equipo->tag}}</x-filament::badge>
        <x-filament::badge class="max-w-fit"> Equipo: {{$record->equipo->nombre}}</x-filament::badge>
    </div>

    <div class="bg-white dark:bg-gray-900 rounded-lg p-4">
        <div class="flex items-center gap-4 mb-5">
            <form>{{$this->form}}</form>
        </div>

        @if(count($this->tableData) > 0)
            <div class="overflow-x-auto relative">
                <table class="min-w-full border-collapse border border-gray-200 dark:border-gray-700">
                    <!-- Header Row with Dates -->
                    <thead>
                    <tr>
                        <!-- Left side headers -->
                        @foreach($this->headers as $header)
                            <th class="border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 p-2 text-left whitespace-nowrap text-xs font-medium text-gray-500 dark:text-gray-400">
                                {{ $header }}
                            </th>
                        @endforeach

                        <!-- Date columns -->
                        @foreach($this->availableDates as $day)
                            <th wire:key="header-{{ $day }}"
                                class="border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 p-2 text-center text-xs font-medium text-gray-500 dark:text-gray-400">
                                {{ Carbon\Carbon::parse($day)->format('d/m/Y') }}
                            </th>
                        @endforeach
                    </tr>
                    </thead>

                    <!-- Table Body -->
                    <tbody>
                    @foreach($this->tableData['items'] as $index => $item)
                        <tr>
                            <!-- Item data columns -->
                            @foreach($this->headers as $header)
                                <td class="border border-gray-200 dark:border-gray-700 p-2 text-xs dark:text-gray-300">
                                    {{ $item[$header] }}
                                </td>
                            @endforeach

                            <!-- Check status columns for each date -->
                            @foreach($this->availableDates as $day)
                                <td wire:key="check-{{ $day }}-{{ $index }}"
                                    class="border border-gray-200 dark:border-gray-700 p-2 text-center">
                                    <div class="flex justify-center items-center">
                                        @if(isset($this->tableData['checks'][$day][$index]))
                                            @if($this->tableData['checks'][$day][$index]['icon'] && $this->tableData['checks'][$day][$index]['color'])
                                                <x-dynamic-component
                                                    :component="$this->tableData['checks'][$day][$index]['icon']"
                                                    class="w-6 h-6"
                                                    style="color: {{ $this->tableData['checks'][$day][$index]['color'] }}"
                                                />
                                            @else
                                                <span
                                                    class="text-xs">{{$this->tableData['checks'][$day][$index]['text']}}</span>
                                            @endif
                                        @else
                                            <span
                                                class="w-6 h-6 rounded-full border-2 border-gray-300 dark:border-gray-600 flex items-center justify-center text-xs">○</span>
                                        @endif
                                    </div>
                                </td>
                            @endforeach
                        </tr>
                    @endforeach

                    <!-- Operator Name Row -->
                    <tr>
                        <td colspan="{{ count($this->headers) }}"
                            class="border border-gray-200 dark:border-gray-700 p-2 font-medium text-xs text-right  ">
                            Nombre del Operador
                        </td>
                        @foreach($this->availableDates as $day)
                            <td wire:key="operator-name-{{ $day }}"
                                class="border border-gray-200 dark:border-gray-700 p-2 text-center">
                                <span class="text-xs">{{ $this->tableData['operatorNames'][$day] }}</span>
                            </td>
                        @endforeach
                    </tr>

                    <!-- Operator Signature Row -->
                    <tr>
                        <td colspan="{{ count($this->headers) }}"
                            class="border border-gray-200 dark:border-gray-700 p-2 font-medium text-xs text-right ">
                            Firma del Operador
                        </td>
                        @foreach($this->availableDates as $day)
                            <td wire:key="operator-signature-{{ $day }}"
                                class="border border-gray-200 dark:border-gray-700 p-2 text-center">
                                @if(isset($this->tableData['operatorSignatures'][$day]))
                                    <img src="{{ $this->tableData['operatorSignatures'][$day] }}"
                                         alt="Firma del Operador"
                                         class="mx-auto max-w-[100px] max-h-[50px]">
                                @else
                                    <span
                                        class="w-6 h-6 rounded-full border-2 border-gray-300 dark:border-gray-600 flex items-center justify-center text-xs">○</span>
                                @endif
                            </td>
                        @endforeach
                    </tr>

                    <!-- Supervisor Signature Row -->
                    <tr>
                        <td colspan="{{ count($this->headers) }}"
                            class="border border-gray-200 dark:border-gray-700 p-2 font-medium text-xs text-right ">
                            Firma del Supervisor
                        </td>
                        @foreach($this->availableDates as $day)
                            <td wire:key="supervisor-signature-{{ $day }}"
                                class="border border-gray-200 dark:border-gray-700 p-2 text-center">
                                @if(isset($this->tableData['supervisorSignatures'][$day]))
                                    <img src="{{ $this->tableData['supervisorSignatures'][$day] }}"
                                         alt="Firma del Supervisor"
                                         class="mx-auto max-w-[100px] max-h-[50px]">

                                @endif
                            </td>
                        @endforeach
                    </tr>
                    </tbody>
                </table>
            </div>
        @endif
    </div>
    <x-filament-actions::modals/>
</x-filament-panels::page>
