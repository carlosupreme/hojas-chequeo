<div x-data="{
    saving: false,
    savingTimeout: null,
    init() {
        window.addEventListener('beforeunload', () => {
            // We use navigator.sendBeacon if possible for reliable 'closing'
            // But Livewire method call is async. For reliable 'close tab', we rely on Echo presence or just the cache timeout.
            // However, for navigation within the app (SPA), this works:
            $wire.markAsLeft();
        });

        // When any input updates the Livewire `form` state, show feedback + animate.
        this.$el.addEventListener('chequeo-form-updated', (e) => {
            this.saving = true;
            clearTimeout(this.savingTimeout);
            this.savingTimeout = setTimeout(() => (this.saving = false), 900);

            // Bubble a DOM event that our Vite/GSAP module can catch.
            window.dispatchEvent(new CustomEvent('chequeo-items:form-changed', { detail: e.detail }));
        });
    }
}">
    <div class="mb-3 flex justify-end min-h-6">
        <div
            data-animate="saving-indicator"
            x-show="saving"
            x-transition.opacity.duration.150ms
            class="inline-flex items-center gap-2 rounded-full border border-blue-200 bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700 shadow-sm dark:border-blue-900/40 dark:bg-blue-950/30 dark:text-blue-200"
            role="status"
            aria-live="polite"
        >
            <svg class="h-3.5 w-3.5 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v3.2a4.8 4.8 0 00-4.8 4.8H4z"></path>
            </svg>
            <span>Guardando…</span>
        </div>
    </div>

    <div class="mb-6 border rounded-lg dark:border-gray-700 ">
        <!-- Desktop View -->
        <div class="hidden lg:block">
            <table class="table-fixed border-collapse w-full">
                <thead class="bg-gray-50 dark:bg-gray-800">
                <tr>
                    @foreach($columnas as $columna)
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            {{ $columna['label'] }}
                        </th>
                    @endforeach
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        Check
                    </th>
                </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                @foreach($items as $item)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                        @foreach($columnas as $columna)
                            <td class="px-4 py-4 text-sm text-gray-900 dark:text-gray-300">
                                {{ $item['cells'][$columna['key']] ?? 'N/A' }}
                            </td>
                        @endforeach

                        <td class="px-2 py-4 w-48">
                            <x-table-inputs.input-dispatcher :item="$item" model="form.{{ $item['id'] }}"/>
                        </td>
                    </tr>
                @endforeach
                </tbody>

            </table>
        </div>

        <!-- Mobile View -->
        <div class="block lg:hidden">
            <div class="border-t dark:border-gray-700">
                @foreach($items as $item)
                    <div class="p-2 border-b dark:border-gray-700 last:border-b-0" x-data="{ open: false }"
                         wire:key="item={{$item['id']}}}">
                        <div class="grid grid-cols-2 gap-2 items-center">
                            <button
                                @click="open = !open"
                                class="text-left focus:outline-none"
                            >
                                <h3 class="font-medium text-xs text-gray-900 dark:text-gray-100">
                                    {{ $item['cells'][$columnas[0]['key']] ?? 'N/A' }}
                                </h3>
                            </button>
                            <div class="relative overflow-visible">
                                <x-table-inputs.input-dispatcher :item="$item" model="form.{{ $item['id'] }}"/>
                            </div>
                        </div>

                        <div x-show="open" x-collapse>
                            <div class="mt-4 space-y-2">
                                @foreach($columnas as $index => $header)
                                    @if($index > 0)
                                        <div class="text-xs">
                                                    <span
                                                        class="font-medium text-gray-700 dark:text-gray-300">{{ $header['label'] }}:</span>
                                            <span
                                                class="text-gray-600 dark:text-gray-400 ml-2">{{ $item['cells'][$header['key']] ?? 'N/A' }}</span>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
