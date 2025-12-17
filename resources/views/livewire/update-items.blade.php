<div>
    <div class="overflow-x-auto">
        <table class="w-full border-collapse rounded-lg overflow-hidden">
            <thead>
            <tr>
                <th class="border border-gray-300 dark:border-gray-600 px-4 py-2 bg-gray-100 dark:bg-gray-700 text-left">
                    <span class="font-bold text-gray-800 dark:text-white">N°</span>
                </th>
                <th class="border border-gray-300 dark:border-gray-600 px-8 py-2 bg-gray-100 dark:bg-gray-700 text-left">
                    <span class="font-bold text-gray-800 dark:text-white">Categoría</span>
                </th>
                @foreach ($columns as $index => $column)
                    <th class="border border-gray-300 dark:border-gray-600 px-4 py-2 bg-gray-100 dark:bg-gray-700 text-left">
                        <div class="flex items-center justify-between">
                            <input type="text" wire:model.live="columns.{{ $index }}"
                                   class="bg-transparent border-none font-bold text-gray-800 dark:text-white focus:outline-hidden focus:ring-2 focus:ring-blue-500 rounded-sm transition-colors duration-200"
                                   aria-label="Column name"
                            />
                            <x-filament::icon-button
                                tooltip="Eliminar columna"
                                wire:loading.attr="disabled"
                                wire:click.prevent="removeColumn({{ $index }})"
                                icon="heroicon-o-trash"
                                color="danger"
                                wire:confirm="Seguro de eliminar esta columna?"
                            />
                        </div>
                    </th>
                @endforeach
                <th class="border border-gray-300 dark:border-gray-600 px-4 py-2 bg-gray-100 dark:bg-gray-700">
                    <x-filament::icon-button
                        wire:click.prevent="addColumn"
                        wire:loading.attr="disabled"
                        tooltip="Agregar columna"
                        color="custom"
                        icon="heroicon-o-plus"
                    />
                </th>
            </tr>
            </thead>
            <tbody>
            @foreach ($rows as $rowIndex => $row)
                <tr class="{{ $loop->even ? 'bg-gray-50 dark:bg-gray-800' : '' }}">
                    <td class="border border-gray-300 dark:border-gray-600 px-4 py-2 text-gray-800 dark:text-gray-200">
                        {{ $rowIndex + 1 }}
                    </td>
                    <td class="border border-gray-300 dark:border-gray-600 px-4 py-2">
                        <select
                            wire:model.live="categories.{{ $rowIndex }}"
                            class="w-full bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-xs px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 focus:outline-hidden focus:ring-2 focus:ring-blue-500"
                        >
                            <option value="limpieza">Limpieza</option>
                            <option value="operacion">Operación</option>
                            <option value="revision">Revisión</option>
                        </select>
                    </td>
                    @foreach ($row as $colIndex => $cell)
                        <td class="border border-gray-300 dark:border-gray-600 px-4 py-2"
                            wire:key="cell-{{$rowIndex}}-{{$colIndex}}">
                            <div class="flex items-center justify-between">
                                @if($editingCell && $editingCell['rowIndex'] === $rowIndex && $editingCell['colIndex'] === $colIndex)
                                    <input type="text"
                                           wire:model.live="rows.{{$rowIndex}}.{{$colIndex}}"
                                           class="grow bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-sm px-2 py-1 text-gray-800 dark:text-white focus:outline-hidden focus:ring-2 focus:ring-blue-500 transition-colors duration-200"
                                           aria-label="Edit cell content"
                                    />
                                @else
                                    <span class="text-gray-800 dark:text-gray-200">{{ $cell }}</span>
                                    <x-filament::icon-button wire:click="startEditing({{ $rowIndex }}, {{ $colIndex }})"
                                                             tooltip="Editar"
                                                             icon="heroicon-o-pencil"
                                                             wire:loading.attr="disabled"
                                    >
                                    </x-filament::icon-button>
                                @endif
                            </div>
                        </td>
                    @endforeach
                    <td class="border-r border-r-gray-300 h-full dark:border-r-gray-600 px-4 py-2 flex items-center gap-4">
                        <x-filament::icon-button
                            wire:click.prevent="removeRow({{ $rowIndex }})"
                            tooltip="Eliminar fila"
                            color="danger"
                            icon="heroicon-o-trash"
                            wire:loading.attr="disabled"
                            class="h-full"
                        />
                        <x-filament::modal id="addAlertModal"
                                           icon="heroicon-o-information-circle"
                                           icon-color="primary"
                                           width="xl"
                                           :close-by-clicking-away="false"
                                           :close-by-escaping="false"
                        >
                            <x-slot name="trigger">
                                <x-filament::icon-button
                                    tooltip="Agregar alerta"
                                    color="warning"
                                    icon="heroicon-o-exclamation-circle"
                                    wire:loading.attr="disabled"
                                />
                            </x-slot>
                            <x-slot name="heading">Agregar un disparador de alerta para este item</x-slot>
                            <x-slot name="description">Disparar alerta cuando</x-slot>

                            <div class="flex flex-col justify-center gap-4" x-data="{
                                                showCustomInput: false,
                                                selectedName: '',
                                                open: false,
                                                customText: '',
                                                operador: null,
                                                selectedStatus: null,
                                                setCustom() {this.selectedName = 'Personalizado'; this.selectedStatus = null; this.open = false; this.showCustomInput = true;},
                                                choose(id, name) {this.selectedName = name; this.selectedStatus = id; this.open = false; this.customText = ''; this.showCustomInput = false;}
                                           }">
                                <div class="space-y-2">
                                    <legend class="text-sm font-medium text-gray-700 mb-2">Este item sea</legend>

                                    <div class="flex flex-col sm:flex-row gap-4">
                                        <div class="flex items-center">
                                            <input
                                                type="radio"
                                                id="igual"
                                                name="comparison"
                                                value="="
                                                class="h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500 peer"
                                                x-model="operador"
                                                required
                                            >
                                            <label
                                                for="igual"
                                                class="ml-2 block text-sm text-gray-700 peer-checked:text-blue-600 peer-hover:text-blue-500 transition-colors"
                                            >
                                                Igual (=)
                                            </label>
                                        </div>

                                        <div class="flex items-center">
                                            <input
                                                type="radio"
                                                id="menor"
                                                x-model="operador"
                                                name="comparison"
                                                value="<"
                                                class="h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500 peer"
                                            >
                                            <label
                                                for="menor"
                                                class="ml-2 block text-sm text-gray-700 peer-checked:text-blue-600 peer-hover:text-blue-500 transition-colors"
                                            >
                                                Menor (&lt;)
                                            </label>
                                        </div>

                                        <div class="flex items-center">
                                            <input
                                                type="radio"
                                                id="mayor"
                                                x-model="operador"
                                                name="comparison"
                                                value=">"
                                                class="h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500 peer"
                                            >
                                            <label
                                                for="mayor"
                                                class="ml-2 block text-sm text-gray-700 peer-checked:text-blue-600 peer-hover:text-blue-500 transition-colors"
                                            >
                                                Mayor (&gt;)
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <p>Que el valor:</p>

                                <div class="relative">
                                    <button
                                        @click="open = !open"
                                        type="button"
                                        class="w-full bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-xs px-4 py-2 inline-flex justify-between items-center text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-hidden focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                    >
                                        <span x-text="selectedName"></span>
                                        @svg('heroicon-c-chevron-down', 'w-6 h-6')
                                    </button>

                                    <div
                                        x-show="open"
                                        @click.away="open = !open"
                                        x-transition:enter="transition ease-out duration-100"
                                        x-transition:enter-start="transform opacity-0 scale-95"
                                        x-transition:enter-end="transform opacity-100 scale-100"
                                        x-transition:leave="transition ease-in duration-75"
                                        x-transition:leave-start="transform opacity-100 scale-100"
                                        x-transition:leave-end="transform opacity-0 scale-95"
                                        class="absolute z-10 mt-1 w-full flex flex-col gap-2 bg-white dark:bg-gray-700 shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-hidden sm:text-sm"
                                    >
                                        @foreach ($statuses as $status)
                                            <button @click="choose({{ $status->id }}, '{{ $status->nombre }}')"
                                                    class="cursor-pointer select-none relative py-2 px-4 hover:bg-gray-100 dark:hover:bg-gray-600">
                                                <div class="flex items-center">
                                                    <span class="mr-3" style="color: {{ $status->color }}">
                                                        <x-dynamic-component :component="$status->icono"
                                                                             class="h-5 w-5"/>
                                                    </span>
                                                    <span class="font-normal block truncate min-w-fit">{{ $status->nombre }}</span>
                                                </div>
                                            </button>
                                        @endforeach

                                        <div
                                            @click="setCustom()"
                                            class="cursor-pointer select-none relative py-2 px-4 hover:bg-gray-100 dark:hover:bg-gray-600"
                                        >
                                            <div class="flex items-center">
                                                <span class="text-gray-600 dark:text-gray-400 mr-3">
                                                    <x-heroicon-o-pencil class="h-5 w-5"/>
                                                </span>
                                                <span class="font-normal block truncate">
                                                    Personalizado
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <div x-show="showCustomInput" class="mt-2">
                                        <x-filament::input.wrapper>
                                            <x-filament::input
                                                x-model="customText"
                                                placeholder="Escribe el estado"
                                                class="w-full"
                                            />
                                        </x-filament::input.wrapper>
                                    </div>
                                </div>

                                <x-filament::button
                                    x-show="customText.length || selectedStatus != null"
                                    @click.prevent="$wire.addAlert({{ $rowIndex }}, selectedStatus, customText, operador)">
                                    Establecer
                                </x-filament::button>
                            </div>
                        </x-filament::modal>
                    </td>
                </tr>
            @endforeach
            <tr>
                <td class="border border-gray-300 dark:border-gray-600 px-4 py-2 bg-gray-50 dark:bg-gray-700"></td>
                <td class="border border-gray-300 dark:border-gray-600 px-4 py-2 bg-gray-50 dark:bg-gray-700"></td>
                @foreach ($columns as $index => $column)
                    <td class="border border-gray-300 dark:border-gray-600 px-4 py-2 bg-gray-50 dark:bg-gray-700"></td>
                @endforeach
                <td class="border border-gray-300 dark:border-gray-600 px-4 py-2 bg-gray-50 dark:bg-gray-700">
                    <x-filament::icon-button
                        wire:click.prevent="addRow"
                        wire:loading.attr="disabled"
                        tooltip="Agregar fila"
                        color="custom"
                        icon="heroicon-o-plus"
                    />
                </td>
            </tr>
            </tbody>
        </table>
        <br>
    </div>

    <div class="mt-6 text-sm text-gray-600 dark:text-gray-400 flex items-center">
        <x-heroicon-o-information-circle class="w-6 h-6"/>
        <span>Tip: Haz clic en el lapiz de la celda para modificar su contenido. Usa los íconos '+' para agregar filas o columnas.</span>
    </div>
</div>
