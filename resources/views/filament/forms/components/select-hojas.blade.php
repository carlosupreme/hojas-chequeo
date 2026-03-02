<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    @php
        $hojasByArea = $getHojas();
        $totalCount = $getHojasCount();
    @endphp

    <div
        x-data="{
            state: $wire.$entangle(@js($getStatePath())),
            search: '',
            matchesSearch(nombre, tag) {
                if (!this.search) return true;
                const term = this.search.toLowerCase();
                return nombre.toLowerCase().includes(term) || tag.toLowerCase().includes(term);
            },
            selectAll() {
                const checkboxes = this.$refs.grid.querySelectorAll('input[type=checkbox]');
                checkboxes.forEach(cb => {
                    if (cb.closest('[x-show]')?.style.display !== 'none') {
                        if (!this.state.includes(cb.value)) {
                            this.state.push(cb.value);
                        }
                    }
                });
            },
            deselectAll() {
                this.state = [];
            },
            get selectedCount() {
                return this.state ? this.state.length : 0;
            }
        }"
        {{ $getExtraAttributeBag() }}
    >
        {{-- Toolbar --}}
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 mb-4">
            {{-- Search --}}
            <div class="relative flex-1 w-full">
                <x-filament::input.wrapper>
                    <x-filament::input
                    type="search"
                    x-model.debounce.200ms="search"
                    placeholder="Buscar por nombre o tag…"
                    />
                </x-filament::input.wrapper>
                <button
                    x-show="search.length > 0"
                    x-on:click="search = ''"
                    type="button"
                    class="absolute inset-y-0 end-0 flex items-center pe-3 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                >
                    <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
                    </svg>
                </button>
            </div>

            {{-- Quick actions & counter --}}
            <div class="flex items-center gap-2 shrink-0">
                <span class="inline-flex items-center gap-1.5 rounded-full bg-primary-50 px-3 py-1 text-xs font-semibold text-primary-700 ring-1 ring-inset ring-primary-200 dark:bg-primary-900/20 dark:text-primary-400 dark:ring-primary-800">
                    <svg class="h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" />
                    </svg>
                    <span x-text="selectedCount"></span> / {{ $totalCount }}
                </span>
                <button
                    type="button"
                    x-on:click="selectAll()"
                    class="inline-flex items-center gap-1 rounded-lg border border-gray-300 bg-white px-2.5 py-1.5 text-xs font-medium text-gray-700 shadow-sm transition hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                >
                    Todas
                </button>
                <button
                    type="button"
                    x-on:click="deselectAll()"
                    class="inline-flex items-center gap-1 rounded-lg border border-gray-300 bg-white px-2.5 py-1.5 text-xs font-medium text-gray-700 shadow-sm transition hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                >
                    Ninguna
                </button>
            </div>
        </div>

        {{-- Hojas Grid grouped by Area --}}
        <div x-ref="grid" class="space-y-6">
            @forelse($hojasByArea as $area => $hojas)
                <div x-data="{
                    hasVisibleItems: true,
                    checkVisibility() {
                        this.hasVisibleItems = Array.from(this.$el.querySelectorAll('[data-hoja-card]'))
                            .some(el => el.style.display !== 'none');
                    }
                }"
                x-effect="search; $nextTick(() => checkVisibility())"
                x-show="hasVisibleItems"
                x-transition
                >
                    {{-- Area header --}}
                    <div class="flex items-center gap-3 mb-3">
                        <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wide">
                            {{ $area }}
                        </h4>
                        <div class="flex-1 border-t border-gray-200 dark:border-gray-700"></div>
                        <span class="text-xs text-gray-400 dark:text-gray-500">{{ $hojas->count() }} hojas</span>
                    </div>

                    {{-- Cards --}}
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3">
                        @foreach($hojas as $hoja)
                            <div
                                data-hoja-card
                                x-show="matchesSearch(@js($hoja->equipo->nombre ?? ''), @js($hoja->equipo->tag ?? ''))"
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 scale-95"
                                x-transition:enter-end="opacity-100 scale-100"
                                class="group relative rounded-xl border bg-white p-3 shadow-sm transition-all duration-150 cursor-pointer dark:bg-gray-900"
                                :class="state && state.includes('{{ $hoja->id }}')
                                    ? 'border-primary-400 ring-2 ring-primary-500/30 bg-primary-50/60 dark:bg-primary-950/20 dark:border-primary-600'
                                    : 'border-gray-200 hover:border-gray-300 hover:shadow-md dark:border-gray-700 dark:hover:border-gray-600'"
                                x-on:click="
                                    if (state.includes('{{ $hoja->id }}')) {
                                        state = state.filter(id => id !== '{{ $hoja->id }}');
                                    } else {
                                        state.push('{{ $hoja->id }}');
                                    }
                                "
                            >
                                {{-- Checkbox indicator --}}
                                <div class="absolute top-2 end-2">
                                    <input
                                        type="checkbox"
                                        value="{{ $hoja->id }}"
                                        x-model="state"
                                        x-on:click.stop
                                        class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-800"
                                    />
                                </div>

                                {{-- Image --}}
                                <div class="flex justify-center mb-2">
                                    @if($hoja->equipo->foto)
                                        <img
                                            src="{{ Storage::url($hoja->equipo->foto) }}"
                                            alt="{{ $hoja->equipo->nombre }}"
                                            class="w-16 h-16 object-cover rounded-lg ring-1 ring-gray-200 dark:ring-gray-700"
                                            loading="lazy"
                                        />
                                    @else
                                        <div class="w-16 h-16 rounded-lg bg-gray-100 dark:bg-gray-800 flex items-center justify-center">
                                            <svg class="w-8 h-8 text-gray-300 dark:text-gray-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0 0 22.5 18.75V5.25A2.25 2.25 0 0 0 20.25 3H3.75A2.25 2.25 0 0 0 1.5 5.25v13.5A2.25 2.25 0 0 0 3.75 21Z" />
                                            </svg>
                                        </div>
                                    @endif
                                </div>

                                {{-- Info --}}
                                <div class="text-center space-y-0.5">
                                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-100 truncate" title="{{ $hoja->equipo->nombre }}">
                                        {{ $hoja->equipo->nombre }}
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 font-mono">
                                        {{ $hoja->equipo->tag }}
                                    </p>
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-[10px] font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-400">
                                        v{{ $hoja->version }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="flex flex-col items-center justify-center rounded-xl border-2 border-dashed border-gray-300 bg-gray-50 px-6 py-12 dark:border-gray-700 dark:bg-gray-900/50">
                    <svg class="h-12 w-12 text-gray-300 dark:text-gray-600 mb-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m6.75 12H9.75m3 0H9.75m0 0v3m0-3v-3m-3.375-3H6.375a1.875 1.875 0 0 0-1.875 1.875v11.25c0 1.035.84 1.875 1.875 1.875h11.25c1.035 0 1.875-.84 1.875-1.875V11.25a1.875 1.875 0 0 0-1.875-1.875Z" />
                    </svg>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">No hay hojas de chequeo activas</p>
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Activa hojas de chequeo para poder asignarlas a este perfil</p>
                </div>
            @endforelse
        </div>
    </div>
</x-dynamic-component>
