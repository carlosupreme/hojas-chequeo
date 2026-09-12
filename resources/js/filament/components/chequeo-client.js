export function getChequeoComponent(config) {
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
            // Only restore draft from localStorage for editable sessions
            if (!this.readOnly) {
                this.loadFromStorage();
            }
            this.recomputeProgress();

            window.addEventListener('online', () => {
                this.isOnline = true;
                if (!this.readOnly) this.triggerSync(true);
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
                if (!this.readOnly) this.bulkFillPpm(e.detail?.realizadoId);
            });

            window.addEventListener('ppm-deactivated-local', () => {
                if (!this.readOnly) this.clearPpmFill();
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
            if (this.readOnly) return;
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
            if (this.readOnly) return;
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
            if (this.readOnly) return;
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
            if (this.readOnly) return;
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
            if (this.readOnly) return;
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

// Global registrations
window.chequeoClientComponent = getChequeoComponent;

window.scrollModalToTop = function(el) {
    if (el) {
        el.scrollTop = 0;
        const parent = el.closest('.fi-modal-window') || el;
        parent.scrollTop = 0;
    }
};

function registerAlpine() {
    if (typeof window !== 'undefined' && window.Alpine) {
        window.Alpine.data('chequeoClientComponent', getChequeoComponent);
    }
}

registerAlpine();
if (typeof document !== 'undefined') {
    document.addEventListener('alpine:init', registerAlpine);
    document.addEventListener('livewire:init', registerAlpine);
}
