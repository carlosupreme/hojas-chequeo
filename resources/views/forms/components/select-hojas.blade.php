<x-dynamic-component
    :component="$getFieldWrapperView()"
    :id="$getId()"
    :label="$getLabel()"
    :label-sr-only="$isLabelHidden()"
    :helper-text="$getHelperText()"
    :hint="$getHint()"
    :hint-icon="$getHintIcon()"
    :required="$isRequired()"
    :state-path="$getStatePath()"
>
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
        @foreach($getHojas() as $hoja)
            <div class="relative border dark:border-gray-700 rounded-lg p-4 hover:bg-gray-50 dark:hover:bg-gray-800 cursor-pointer transition-colors">
                <label class="flex flex-col items-center space-y-2">
                    <input
                        type="checkbox"
                        value="{{ $hoja->id }}"
                        wire:model="{{ $getStatePath() }}"
                        class="absolute top-2 right-2 h-4 w-4 rounded-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:checked:bg-primary-500"
                    />

                    @if($hoja->equipo->foto)
                        <img
                            src="{{ Storage::url($hoja->equipo->foto) }}"
                            alt="{{ $hoja->equipo->nombre }}"
                            class="w-24 h-24 object-cover rounded-lg ring-1 ring-gray-200 dark:ring-gray-700"
                        />
                    @else
                        <div class="w-24 h-24 bg-gray-100 dark:bg-gray-800 rounded-lg flex items-center justify-center">
                            <img class="h-16 w-16 rounded-full object-cover ring-2 ring-gray-300 dark:ring-gray-600"
                                 src="{{asset('placeholder.jpg')}}" alt="Sin foto" />
                        </div>
                    @endif

                    <div class="text-center">
                        <div class="font-medium text-sm text-gray-900 dark:text-gray-100">{{ $hoja->equipo->nombre }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">Tag: {{ $hoja->equipo->tag }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">Area: {{ $hoja->area }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">Version: {{ $hoja->version }}</div>
                    </div>
                </label>
            </div>
        @endforeach
    </div>
</x-dynamic-component>
