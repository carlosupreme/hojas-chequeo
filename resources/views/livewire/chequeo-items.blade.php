<div>
    {{-- ================================================================
         TABLET / DESKTOP TABLE (≥ 768px)
    ================================================================ --}}
    <div class="hidden md:block overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50/60 dark:bg-gray-800/40">
                    @foreach($columnas as $columna)
                        <th class="px-6 py-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            {{ $columna['label'] }}
                        </th>
                    @endforeach
                    <th class="px-6 py-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        Estado / Valor
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @foreach($items as $item)
                    @php $answered = ! is_null($form[$item['id']] ?? null); @endphp
                    <tr wire:key="row-{{ $item['id'] }}"
                        class="transition-colors {{ $answered
                            ? 'bg-green-50/40 dark:bg-green-900/10 hover:bg-green-50/60 dark:hover:bg-green-900/20'
                            : 'bg-white dark:bg-gray-900 hover:bg-gray-50/60 dark:hover:bg-gray-800/40' }}">

                        @foreach($columnas as $columna)
                            <td class="px-6 py-5 text-sm text-gray-700 dark:text-gray-300 leading-snug">
                                {{ $item['cells'][$columna['key']] ?? '—' }}
                            </td>
                        @endforeach

                        {{-- Input cell --}}
                        <td class="px-6 py-4">
                            <x-table-inputs.input-dispatcher
                                :item="$item"
                                model="form.{{ $item['id'] }}"
                                :readOnly="$readOnly"
                            />
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- ================================================================
         MOBILE CARDS (< 768px)
    ================================================================ --}}
    <div class="block md:hidden divide-y divide-gray-100 dark:divide-gray-800">
        @foreach($items as $item)
            @php $answered = ! is_null($form[$item['id']] ?? null); @endphp
            <div wire:key="card-{{ $item['id'] }}"
                 class="p-4 {{ $answered ? 'bg-green-50/40 dark:bg-green-900/10' : 'bg-white dark:bg-gray-900' }}">

                {{-- Item name --}}
                <p class="text-sm font-medium text-gray-800 dark:text-white mb-3 leading-snug">
                    {{ $item['cells'][$columnas[0]['key']] ?? '—' }}
                </p>

                {{-- Input --}}
                <div class="mb-3">
                    <x-table-inputs.input-dispatcher
                        :item="$item"
                        model="form.{{ $item['id'] }}"
                        :readOnly="$readOnly"
                    />
                </div>

                {{-- Collapsible details --}}
                @if(count($columnas) > 1)
                    <div x-data="{ open: false }" class="border-t border-gray-100 dark:border-gray-800 pt-2">
                        <button @click="open = !open"
                            class="flex items-center gap-1 text-xs text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors py-1">
                            <svg class="w-3.5 h-3.5 transition-transform" :class="open && 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                            </svg>
                            Ver detalles
                        </button>
                        <div x-show="open" x-collapse class="mt-2 space-y-1.5">
                            @foreach($columnas as $index => $col)
                                @if($index > 0)
                                    <div class="flex gap-2 text-xs">
                                        <span class="w-24 shrink-0 font-medium text-gray-500 dark:text-gray-400">{{ $col['label'] }}</span>
                                        <span class="text-gray-700 dark:text-gray-300">{{ $item['cells'][$col['key']] ?? '—' }}</span>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        @endforeach
    </div>
</div>
