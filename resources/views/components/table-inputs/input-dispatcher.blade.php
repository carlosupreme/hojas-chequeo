@props(['item', 'model' => null, 'readOnly' => false])

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
        <x-table-inputs.icon-buttons
            :options="$item['options']"
            :itemId="$item['id']"
            :value="$item['value'] ?? null"
            :readOnly="$readOnly"
        />
    @elseif($item['type_key'] === 'number')
        @if($readOnly)
            <div class="text-sm font-semibold text-gray-900 dark:text-white px-3 py-1.5 bg-gray-50 dark:bg-gray-800 rounded-lg text-right">
                <span x-text="(typeof form !== 'undefined' && form[{{ $item['id'] }}] !== undefined && form[{{ $item['id'] }}] !== null && form[{{ $item['id'] }}] !== '') ? form[{{ $item['id'] }}] : @js($item['value'] ?? '—')"></span>
            </div>
        @else
            <x-filament::input.wrapper>
                <x-filament::input
                    type="number"
                    :readonly="$readOnly"
                    x-model.number="form[{{ $item['id'] }}]"
                    @input.debounce.300ms="setAnswer({{ $item['id'] }}, form[{{ $item['id'] }}])"
                    placeholder="0"
                />
            </x-filament::input.wrapper>
        @endif
    @elseif($item['type_key'] === 'text')
        @if($readOnly)
            <div class="text-sm text-gray-900 dark:text-white px-3 py-1.5 bg-gray-50 dark:bg-gray-800 rounded-lg">
                <span x-text="(typeof form !== 'undefined' && form[{{ $item['id'] }}] !== undefined && form[{{ $item['id'] }}] !== null && form[{{ $item['id'] }}] !== '') ? form[{{ $item['id'] }}] : @js($item['value'] ?? '—')"></span>
            </div>
        @else
            <x-filament::input.wrapper>
                <x-filament::input
                    type="text"
                    :readonly="$readOnly"
                    x-model="form[{{ $item['id'] }}]"
                    @input.debounce.300ms="setAnswer({{ $item['id'] }}, form[{{ $item['id'] }}])"
                    placeholder="Escriba aquí…"
                />
            </x-filament::input.wrapper>
        @endif
    @elseif($item['type_key'] === 'boolean')
        @if($readOnly)
            <div class="text-sm font-semibold px-3 py-1.5 bg-gray-50 dark:bg-gray-800 rounded-lg text-center">
                <span x-text="((typeof form !== 'undefined' && form[{{ $item['id'] }}] !== undefined) ? form[{{ $item['id'] }}] : @js($item['value'])) ? 'SÍ' : 'NO'"></span>
            </div>
        @else
            <x-table-inputs.toggle
                :itemId="$item['id']"
                :readOnly="$readOnly"
            />
        @endif
    @endif
</div>

