<div>
    <script>
        (function () {
            if (typeof window.chequeoClientComponent !== 'undefined') return;

            function getChequeoComponent(config) {
                return {
                    hojaId: config.hojaId,
                    ejecucionId: config.ejecucionId,
                    readOnly: Boolean(config.readOnly),
                    items: config.items || [],
                    totalCount: (config.items || []).length,
                    answeredCount: 0,
                    form: config.initialForm || {},
                    dirty: {},
                    isSyncing: false,
                    syncStatus: 'idle', // 'idle' | 'saving' | 'saved' | 'offline'
                    storageKey: '',
                    syncTimer: null,
                    isOnline: typeof navigator !== 'undefined' ? navigator.onLine : true,

                    init() {
                        this.storageKey = 'chequeo_draft_' + this.hojaId + '_' + (this.ejecucionId || 'new');
                        if (!this.readOnly) {
                            this.loadFromStorage();
                        }
                        this.recomputeProgress();

                        window.addEventListener('online', () => {
                            this.isOnline = true;
                            this.triggerSync(true);
                        });

                        window.addEventListener('offline', () => {
                            this.isOnline = false;
                            this.syncStatus = 'offline';
                            this.dispatchStatus();
                        });

                        window.addEventListener('chequeo-completed', () => {
                            this.clearStorage();
                        });

                        window.addEventListener('ppm-activated-local', (e) => {
                            this.bulkFillPpm(e.detail?.realizadoId);
                        });

                        window.addEventListener('ppm-deactivated-local', () => {
                            this.clearPpmFill();
                        });
                    },

                    loadFromStorage() {
                        try {
                            const stored = localStorage.getItem(this.storageKey);
                            if (stored) {
                                const parsed = JSON.parse(stored);
                                if (parsed && typeof parsed === 'object') {
                                    for (const k in parsed) {
                                        if (parsed[k] !== null && parsed[k] !== undefined) {
                                            this.form[k] = parsed[k];
                                            this.dirty[k] = parsed[k];
                                        }
                                    }
                                }
                            }
                        } catch (e) {
                            console.warn('No se pudo leer localStorage:', e);
                        }
                    },

                    saveToStorage() {
                        try {
                            localStorage.setItem(this.storageKey, JSON.stringify(this.form));
                        } catch (e) {
                            console.warn('No se pudo escribir en localStorage:', e);
                        }
                    },

                    clearStorage() {
                        try {
                            localStorage.removeItem(this.storageKey);
                            localStorage.removeItem('chequeo_draft_' + this.hojaId + '_new');
                        } catch (e) {}
                    },

                    setAnswer(itemId, value) {
                        if (this.readOnly) return;
                        this.form[itemId] = value;
                        this.dirty[itemId] = value;
                        this.saveToStorage();
                        this.recomputeProgress();
                        this.triggerSync();
                    },

                    recomputeProgress() {
                        let count = 0;
                        for (const id in this.form) {
                            const val = this.form[id];
                            if (val !== null && val !== undefined && val !== '') {
                                count++;
                            }
                        }
                        this.answeredCount = count;
                        window.dispatchEvent(new CustomEvent('progress-updated-local', {
                            detail: { answered: this.answeredCount, total: this.totalCount }
                        }));
                    },

                    dispatchStatus() {
                        window.dispatchEvent(new CustomEvent('sync-status-changed', {
                            detail: { status: this.syncStatus, isOnline: this.isOnline }
                        }));
                    },

                    triggerSync(immediate = false) {
                        if (this.syncTimer) clearTimeout(this.syncTimer);
                        if (immediate) {
                            this.syncDirty();
                        } else {
                            this.syncStatus = 'saving';
                            this.dispatchStatus();
                            this.syncTimer = setTimeout(() => this.syncDirty(), 3500);
                        }
                    },

                    async syncDirty() {
                        const dirtyKeys = Object.keys(this.dirty);
                        if (dirtyKeys.length === 0 || this.isSyncing) {
                            if (dirtyKeys.length === 0 && this.syncStatus === 'saving') {
                                this.syncStatus = 'idle';
                                this.dispatchStatus();
                            }
                            return;
                        }

                        if (!navigator.onLine) {
                            this.syncStatus = 'offline';
                            this.dispatchStatus();
                            return;
                        }

                        this.isSyncing = true;
                        this.syncStatus = 'saving';
                        this.dispatchStatus();

                        const batch = {};
                        for (const k of dirtyKeys) {
                            batch[k] = this.dirty[k];
                        }

                        try {
                            const response = await this.$wire.syncBatch(batch);
                            if (response && response.ejecucion_id) {
                                this.ejecucionId = response.ejecucion_id;
                                const newKey = 'chequeo_draft_' + this.hojaId + '_' + this.ejecucionId;
                                if (newKey !== this.storageKey) {
                                    this.storageKey = newKey;
                                    this.saveToStorage();
                                }
                            }
                            for (const k of dirtyKeys) {
                                if (this.dirty[k] === batch[k]) {
                                    delete this.dirty[k];
                                }
                            }
                            this.syncStatus = 'saved';
                            this.dispatchStatus();
                            setTimeout(() => {
                                if (this.syncStatus === 'saved') {
                                    this.syncStatus = 'idle';
                                    this.dispatchStatus();
                                }
                            }, 2500);
                        } catch (err) {
                            console.warn('Error en sincronización en segundo plano:', err);
                            this.syncStatus = 'offline';
                            this.dispatchStatus();
                        } finally {
                            this.isSyncing = false;
                        }
                    },

                    bulkFillPpm(realizadoId) {
                        for (const item of this.items) {
                            const val = item.type_key === 'icon_set' ? realizadoId : (item.type_key === 'number' ? 0 : (item.type_key === 'text' ? ' ' : true));
                            this.form[item.id] = val;
                            this.dirty[item.id] = val;
                        }
                        this.saveToStorage();
                        this.recomputeProgress();
                        this.triggerSync(true);
                    },

                    clearPpmFill() {
                        for (const item of this.items) {
                            this.form[item.id] = null;
                            this.dirty[item.id] = null;
                        }
                        this.saveToStorage();
                        this.recomputeProgress();
                        this.triggerSync(true);
                    }
                };
            }

            window.chequeoClientComponent = getChequeoComponent;

            if (window.Alpine) {
                Alpine.data('chequeoClientComponent', getChequeoComponent);
            } else {
                document.addEventListener('alpine:init', () => {
                    Alpine.data('chequeoClientComponent', getChequeoComponent);
                });
            }
        })();
    </script>

    <div
        x-data="chequeoClientComponent({
            hojaId: {{ $hojaId }},
            ejecucionId: {{ $ejecucionId ? (int)$ejecucionId : 'null' }},
            readOnly: {{ $readOnly ? 'true' : 'false' }},
            items: @js($items),
            initialForm: @js($form),
        })"
        class="w-full"
    >
        {{-- ================================================================
             SINGLE UNIFIED RESPONSIVE STRUCTURE (NO DUPLICATED INPUT NODES)
        ================================================================ --}}
        <div class="divide-y divide-gray-100 dark:divide-gray-800">

            {{-- Table header for desktop (hidden on mobile) --}}
            <div class="hidden md:flex items-center px-5 py-3.5 bg-gray-50/70 dark:bg-gray-800/40 border-b border-gray-200 dark:border-gray-700 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                <div class="flex-1 flex items-center gap-4">
                    @foreach($columnas as $columna)
                        <div class="flex-1 px-2 font-semibold">
                            {{ $columna['label'] }}
                        </div>
                    @endforeach
                </div>
                <div class="w-72 px-3 text-right font-semibold shrink-0">
                    Estado / Valor
                </div>
            </div>

            {{-- List of items (Rendered ONCE per row; adapts smoothly via CSS) --}}
            @foreach($items as $item)
                <div
                    wire:key="item-{{ $item['id'] }}"
                    class="p-4 md:px-5 md:py-3.5 transition-colors duration-150 md:flex md:items-center md:justify-between gap-4"
                    :class="(form[{{ $item['id'] }}] !== null && form[{{ $item['id'] }}] !== undefined && form[{{ $item['id'] }}] !== '')
                        ? 'bg-green-50/40 dark:bg-green-900/10 hover:bg-green-50/60 dark:hover:bg-green-900/20'
                        : 'bg-white dark:bg-gray-900 hover:bg-gray-50/60 dark:hover:bg-gray-800/40'"
                >
                    {{-- Desktop layout: columns horizontally --}}
                    <div class="hidden md:flex md:flex-1 md:items-center md:gap-4 min-w-0">
                        @foreach($columnas as $columna)
                            <div class="flex-1 px-2 text-sm text-gray-700 dark:text-gray-300 leading-snug truncate" title="{{ $item['cells'][$columna['key']] ?? '' }}">
                                {{ $item['cells'][$columna['key']] ?? '—' }}
                            </div>
                        @endforeach
                    </div>

                    {{-- Mobile layout: Title and Collapsible Details --}}
                    <div class="block md:hidden mb-3">
                        <p class="text-sm font-semibold text-gray-900 dark:text-white leading-snug">
                            {{ $item['cells'][$columnas[0]['key']] ?? '—' }}
                        </p>

                        @if(count($columnas) > 1)
                            <div x-data="{ open: false }" class="mt-2 pt-2 border-t border-gray-100 dark:border-gray-800">
                                <button
                                    type="button"
                                    @click="open = !open"
                                    class="flex items-center gap-1 text-xs text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors py-0.5"
                                >
                                    <svg class="w-3.5 h-3.5 transition-transform duration-150" :class="open && 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                    <span x-text="open ? 'Ocultar detalles' : 'Ver detalles'"></span>
                                </button>
                                <div x-show="open" x-collapse class="mt-2 space-y-1.5 pl-1">
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

                    {{-- Single input container (Shared across both desktop & mobile views) --}}
                    <div class="w-full md:w-72 shrink-0 flex items-center md:justify-end">
                        <x-table-inputs.input-dispatcher
                            :item="$item"
                            :readOnly="$readOnly"
                        />
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

