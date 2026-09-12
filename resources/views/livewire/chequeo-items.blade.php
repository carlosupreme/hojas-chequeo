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
        class="w-full md:overflow-x-auto"
    >
        @php
            $totalCols = count($columnas);
            $getColClasses = function($index) use ($totalCols) {
                if ($index === 0) {
                    return 'flex-[1.4] min-w-[125px] font-semibold text-gray-900 dark:text-white';
                }
                if ($index === $totalCols - 1 && $totalCols >= 3) {
                    return 'flex-[1.3] min-w-[110px] text-gray-700 dark:text-gray-300';
                }
                if ($totalCols >= 4 && $index === 2) {
                    return 'flex-[0.8] min-w-[75px] text-gray-700 dark:text-gray-300';
                }
                return 'flex-[1.1] min-w-[105px] text-gray-700 dark:text-gray-300';
            };
            $getHeaderColClasses = function($index) use ($totalCols) {
                if ($index === 0) {
                    return 'flex-[1.4] min-w-[125px]';
                }
                if ($index === $totalCols - 1 && $totalCols >= 3) {
                    return 'flex-[1.3] min-w-[110px]';
                }
                if ($totalCols >= 4 && $index === 2) {
                    return 'flex-[0.8] min-w-[75px]';
                }
                return 'flex-[1.1] min-w-[105px]';
            };
        @endphp

        {{-- ================================================================
             SINGLE UNIFIED RESPONSIVE STRUCTURE (NO DUPLICATED INPUT NODES)
        ================================================================ --}}
        <div class="divide-y divide-gray-100 dark:divide-gray-800 min-w-full md:min-w-[700px]">

            {{-- Table header for desktop (hidden on mobile) --}}
            <div class="hidden md:flex items-center px-4 sm:px-5 py-3.5 bg-gray-50/70 dark:bg-gray-800/40 border-b border-gray-200 dark:border-gray-700 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-normal gap-4">
                <div class="flex-1 flex items-center gap-3 min-w-0">
                    @foreach($columnas as $index => $columna)
                        <div class="{{ $getHeaderColClasses($index) }} px-1.5 font-semibold whitespace-nowrap">
                            {{ $columna['label'] }}
                        </div>
                    @endforeach
                </div>
                <div class="w-52 lg:w-60 px-2 text-right font-semibold shrink-0">
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
                    {{-- Desktop layout: columns horizontally without cropping --}}
                    <div class="hidden md:flex md:flex-1 md:items-center md:gap-3 min-w-0">
                        @foreach($columnas as $index => $columna)
                            <div class="{{ $getColClasses($index) }} px-1.5 text-sm leading-snug break-words whitespace-normal" title="{{ $item['cells'][$columna['key']] ?? '' }}">
                                {{ $item['cells'][$columna['key']] ?? '—' }}
                            </div>
                        @endforeach
                    </div>

                    {{-- Mobile layout: Title and all metadata cleanly visible without cropping --}}
                    <div class="block md:hidden mb-3">
                        <p class="text-sm font-bold text-gray-900 dark:text-white leading-snug break-words">
                            {{ $item['cells'][$columnas[0]['key']] ?? '—' }}
                        </p>

                        @if(count($columnas) > 1)
                            <div class="grid grid-cols-2 gap-2 mt-2 pt-2 border-t border-gray-100 dark:border-gray-800/60 text-xs">
                                @foreach($columnas as $index => $col)
                                    @if($index > 0 && isset($item['cells'][$col['key']]) && $item['cells'][$col['key']] !== '')
                                        <div class="{{ ($loop->last && ($loop->count % 2 === 1)) ? 'col-span-2' : '' }} bg-gray-50 dark:bg-gray-800/50 rounded-lg p-2 border border-gray-100 dark:border-gray-800">
                                            <span class="block text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">
                                                {{ $col['label'] }}
                                            </span>
                                            <span class="block text-xs font-medium text-gray-800 dark:text-gray-200 break-words leading-snug mt-0.5">
                                                {{ $item['cells'][$col['key']] }}
                                            </span>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @endif
                    </div>

                    {{-- Single input container (Shared across both desktop & mobile views) --}}
                    <div class="w-full md:w-52 lg:w-60 shrink-0 flex items-center md:justify-end">
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

