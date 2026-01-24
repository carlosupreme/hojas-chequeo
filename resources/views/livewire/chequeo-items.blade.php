<div x-data="{
    notifySave() {
        this.$el.dispatchEvent(new CustomEvent('chequeo-form-updated', { bubbles: true }));
    }
}">
    {{--
        DESKTOP VIEW (> lg)
        Classic Table for high density data
    --}}
    <div class="hidden lg:block overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
            <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/30">
                @foreach($columnas as $columna)
                    <th class="px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        {{ $columna['label'] }}
                    </th>
                @endforeach
                <th class="px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider text-right">
                    Estado / Valor
                </th>
            </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800 bg-white dark:bg-gray-900">
            @foreach($items as $item)
                <tr class="group hover:bg-blue-50/30 dark:hover:bg-blue-900/10 transition-colors">
                    @foreach($columnas as $columna)
                        <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">
                            {{ $item['cells'][$columna['key']] ?? '—' }}
                        </td>
                    @endforeach

                    {{-- Input Cell --}}
                    <td class="px-6 py-3 w-64" @change="notifySave()">
                        <div class="relative">
                            <x-table-inputs.input-dispatcher
                                :item="$item"
                                model="form.{{ $item['id'] }}"
                                :readOnly="$readOnly"
                            />
                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    {{--
        MOBILE VIEW (< lg)
        Card Layout for better touch targets
    --}}
    <div class="block lg:hidden divide-y divide-gray-100 dark:divide-gray-800">
        @foreach($items as $item)
            <div class="p-4 bg-white dark:bg-gray-900" wire:key="mobile-item-{{$item['id']}}">

                {{-- Header: Item Name --}}
                <div class="mb-3">
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wide">Item</span>
                    <p class="text-sm font-semibold text-gray-900 dark:text-white leading-tight">
                        {{ $item['cells'][$columnas[0]['key']] ?? 'N/A' }}
                    </p>
                </div>

                {{-- Input Area (Prominent) --}}
                <div class="mb-4" @change="notifySave()">
                    <x-table-inputs.input-dispatcher
                        :item="$item"
                        model="form.{{ $item['id'] }}"
                        :readOnly="$readOnly"
                    />
                </div>

                {{-- Details Toggle (Accordion) --}}
                <div x-data="{ expanded: false }" class="border-t border-gray-100 dark:border-gray-800 pt-2">
                    <button
                        @click="expanded = !expanded"
                        class="flex items-center justify-between w-full text-xs text-gray-500 dark:text-gray-400 py-1"
                    >
                        <span>Ver detalles (Frecuencia, Método...)</span>
                        <svg class="w-4 h-4 transition-transform duration-200" :class="expanded ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                    </button>

                    <div x-show="expanded" x-collapse class="mt-2 space-y-2 pb-2">
                        @foreach($columnas as $index => $header)
                            @if($index > 0)
                                <div class="grid grid-cols-3 gap-2 text-xs">
                                    <span class="font-medium text-gray-500 dark:text-gray-400">{{ $header['label'] }}</span>
                                    <span class="col-span-2 text-gray-700 dark:text-gray-300">
                                        {{ $item['cells'][$header['key']] ?? '—' }}
                                    </span>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
