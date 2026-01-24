@props(['item', 'model', 'readOnly'])

@php
    if ($item['type_key'] === 'icon_set') {
        $item['options'] = collect($item['options'])->map(function($opt) {
            $opt['icon_html'] = svg($opt['icon'], 'w-4 h-4 text-' . $opt['color'] . '-500')->toHtml();
            return $opt;
        })->toArray();
    }
@endphp

<div class="w-full">
    @if($item['type_key'] === 'icon_set')
        <x-table-inputs.icon-select :options="$item['options']" :wire:model.live="$model" :readOnly="$readOnly"/>
    @elseif($item['type_key'] === 'number')
        <x-filament::input.wrapper>
            <x-filament::input type="number" :wire:model.blur="$model" :readonly="$readOnly"/>
        </x-filament::input.wrapper>
    @elseif($item['type_key'] === 'text')
        <x-filament::input.wrapper>
            <x-filament::input type="text" :wire:model.blur="$model" :readonly="$readOnly"/>
        </x-filament::input.wrapper>
    @elseif($item['type_key'] === 'boolean')
        @if($readOnly)
            <span class="text-sm">{{$model === true? "Si" : "No"}}</span>
        @else
            <x-table-inputs.toggle :wire:model.live="$model"/>
        @endif
    @endif
</div>
