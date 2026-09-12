@props(['options', 'readOnly' => false, 'itemId' => null, 'value' => null])

<div
    wire:ignore
    x-data="{
        itemId: {{ $itemId ? (int)$itemId : 'null' }},
        @if($itemId)
        value: {{ json_encode($value) }},
        @else
        value: @entangle($attributes->wire('model')),
        @endif
        readOnly: {{ $readOnly ? 'true' : 'false' }},
        selectedLabel: '',
        _opts: @js($options),

        colorSelected: {
            green:  'border-green-500 bg-green-50 text-green-600 dark:bg-green-900/30 dark:border-green-400 dark:text-green-400',
            red:    'border-red-500 bg-red-50 text-red-700 dark:bg-red-900/30 dark:border-red-400 dark:text-red-400',
            yellow: 'border-yellow-400 bg-yellow-50 text-yellow-600 dark:bg-yellow-900/30 dark:border-yellow-400 dark:text-yellow-400',
            gray:   'border-gray-400 bg-gray-100 text-gray-600 dark:bg-gray-700 dark:border-gray-400 dark:text-gray-300',
        },
        colorUnselected: 'border-gray-200 bg-white text-gray-300 hover:border-gray-400 hover:text-gray-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-600 dark:hover:border-gray-400',

        // Cached DOM refs — queried once after mount, reused on every animation call
        _btns: null, _gaps: null, _label: null,

        init() {
            this._btns  = [...this.$el.querySelectorAll('.icon-btn')];
            this._gaps  = [...this.$el.querySelectorAll('.icon-gap')];
            this._label = this.$el.querySelector('.icon-label');

            if (this.itemId && typeof this.form !== 'undefined' && this.form[this.itemId] !== undefined && this.form[this.itemId] !== null) {
                this.value = this.form[this.itemId];
            }

            // Initial state without animation (resumed chequeo or view mode)
            if (this.value !== null && this.value !== undefined && this.value !== '') {
                const opt = this._opts.find(o => String(o.id) === String(this.value));
                if (opt) this.selectedLabel = opt.label;
                this._anim(false);
            }

            // React to parent form state updates (e.g. PPM bulk-fill or localStorage load)
            if (this.itemId && typeof this.form !== 'undefined') {
                this.$watch('form[' + this.itemId + ']', (v) => {
                    if (this.value != v) {
                        this.value = v;
                        const opt = this._opts.find(o => String(o.id) === String(v));
                        this.selectedLabel = opt ? opt.label : '';
                        this._anim(true);
                    }
                });
            } else {
                this.$watch('value', (v) => {
                    const opt = this._opts.find(o => String(o.id) === String(v));
                    this.selectedLabel = opt ? opt.label : '';
                    this._anim(true);
                });
            }
        },

        toggle(optId) {
            if (this.readOnly) return;
            const nextVal = (String(this.value) === String(optId)) ? null : optId;
            this.value = nextVal;
            const opt = this._opts.find(o => String(o.id) === String(nextVal));
            this.selectedLabel = opt ? opt.label : '';
            this._anim(true);

            if (this.itemId && typeof this.setAnswer === 'function') {
                this.setAnswer(this.itemId, nextVal);
            }
        },

        // Single method handles both select and deselect based on current this.value
        _anim(withAnimation) {
            if (!window.gsap || !this._btns) return;

            const btns  = this._btns;
            const gaps  = this._gaps;
            const label = this._label;
            const v     = this.value;
            const hasValue = v !== null && v !== undefined && v !== '';

            if (hasValue) {
                // Keep selected wrapper visible, collapse others, show label
                const sel    = btns.find(b => String(b.dataset.id) === String(v));
                const others = btns.filter(b => String(b.dataset.id) !== String(v));
                if (withAnimation) {
                    // Timeline batches all tweens into one RAF tick
                    const tl = gsap.timeline();
                    // Collapse: width only — overflow:hidden already clips content at width:0 (no opacity needed)
                    tl.to(others, { width: 0,              duration: 0.18, ease: 'power2.in',  force3D: true, overwrite: 'auto' }, 0)
                      .to(gaps,   { width: 0, opacity: 0,  duration: 0.16, ease: 'power2.in',               overwrite: 'auto' }, 0)
                      .to(label,  { width: 'auto', opacity: 1, duration: 0.24, ease: 'power2.out',          overwrite: 'auto' }, 0.16);
                    if (sel) tl.to(sel, { width: 40, opacity: 1, duration: 0.22, ease: 'power2.out', force3D: true, overwrite: 'auto' }, 0);
                } else {
                    if (sel) gsap.set(sel, { width: 40, opacity: 1 });
                    gsap.set(others, { width: 0 });
                    gsap.set(gaps,   { width: 0, opacity: 0 });
                    gsap.set(label,  { width: 'auto', opacity: 1 });
                }
            } else {
                // Expand all wrappers back, hide label
                if (withAnimation) {
                    const tl = gsap.timeline();
                    tl.to(label, { width: 0, opacity: 0,  duration: 0.14, ease: 'power2.in',               overwrite: 'auto' }, 0)
                      .to(btns,  { width: 40, opacity: 1, duration: 0.24, ease: 'back.out(1.4)', force3D: true, overwrite: 'auto' }, 0.12)
                      .to(gaps,  { width: 6,  opacity: 1, duration: 0.24, ease: 'back.out(1.4)',            overwrite: 'auto' }, 0.12);
                } else {
                    gsap.set(label, { width: 0, opacity: 0 });
                    gsap.set(btns,  { width: 40, opacity: 1 });
                    gsap.set(gaps,  { width: 6,  opacity: 1 });
                }
            }
        },
    }"
    class="flex items-center"
    style="min-height: 40px;"
>
    @foreach($options as $option)
        {{-- Wrapper is what GSAP animates; button inside stays full-size (no border artifacts at width:0) --}}
        <div
            class="icon-btn shrink-0 overflow-hidden"
            data-id="{{ $option['id'] }}"
            style="width: 40px;"
        >
            <button
                type="button"
                title="{{ $option['label'] }}"
                @disabled($readOnly)
                @click="toggle({{ $option['id'] }})"
                class="flex items-center justify-center w-10 h-10 rounded-lg border-2 transition-colors duration-150"
                :class="String(value) === String({{ $option['id'] }})
                    ? (colorSelected['{{ $option['color'] }}'] ?? colorSelected.gray)
                    : colorUnselected"
            >
                <span class="pointer-events-none">{!! $option['icon_html'] !!}</span>
            </button>
        </div>
        @if(!$loop->last)
            <span class="icon-gap inline-block shrink-0" style="width: 6px;"></span>
        @endif
    @endforeach

    {{-- Label: fades in beside the selected icon; click it to deselect --}}
    <div class="icon-label overflow-hidden whitespace-nowrap" style="width: 0; opacity: 0;">
        <span
            x-text="selectedLabel"
            @click="if (!readOnly) toggle(value)"
            class="ml-3 text-sm font-semibold text-gray-700 dark:text-gray-300 select-none"
            :class="!readOnly && 'cursor-pointer'"
        ></span>
    </div>
</div>
