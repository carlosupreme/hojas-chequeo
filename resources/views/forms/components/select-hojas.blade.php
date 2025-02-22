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
            <div class="relative border rounded-lg p-4 hover:bg-gray-50 cursor-pointer">
                <label class="flex flex-col items-center space-y-2">
                    <input
                        type="checkbox"
                        value="{{ $hoja->id }}"
                        wire:model="{{ $getStatePath() }}"
                        class="absolute top-2 right-2 h-4 w-4 rounded border-gray-300"
                    />

                    @if($hoja->equipo->foto)
                        <img
                            src="{{ Storage::url($hoja->equipo->foto) }}"
                            alt="{{ $hoja->equipo->nombre }}"
                            class="w-24 h-24 object-cover rounded-lg"
                        />
                    @else
                        <div class="w-24 h-24 bg-gray-100 rounded-lg flex items-center justify-center">
                            <img class="h-16 w-16 rounded-full object-cover ring-2 ring-gray-300 dark:ring-gray-600"
                                 src="{{asset('placeholder.jpg')}}" alt="Sin foto" />
                        </div>
                    @endif

                    <div class="text-center">
                        <div class="font-medium text-sm">{{ $hoja->equipo->nombre }}</div>
                        <div class="text-xs text-gray-500">Tag: {{ $hoja->equipo->tag }}</div>
                        <div class="text-xs text-gray-500">Area: {{ $hoja->area }}</div>
                        <div class="text-xs text-gray-500">Version: {{ $hoja->version }}</div>
                    </div>
                </label>
            </div>
        @endforeach
    </div>
</x-dynamic-component>
