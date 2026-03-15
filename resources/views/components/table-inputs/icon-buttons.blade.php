@props(['options', 'readOnly' => false])

<div
    wire:ignore
    x-data="{
        value: @entangle($attributes->wire('model')),
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
            this.$nextTick(() => {
                this._btns  = [...this.$el.querySelectorAll('.icon-btn')];
                this._gaps  = [...this.$el.querySelectorAll('.icon-gap')];
                this._label = this.$el.querySelector('.icon-label');

                // Initial state without animation (resumed chequeo)
                if (this.value !== null && this.value !== undefined && this.value !== '') {
                    const opt = this._opts.find(o => o.id == this.value);
                    if (opt) this.selectedLabel = opt.label;
                    this._anim(false);
                }

                // React to any value change: user click OR external update (e.g. PPM bulk-fill)
                this.$watch('value', (v) => {
                    const opt = this._opts.find(o => o.id == v);
                    this.selectedLabel = opt ? opt.label : '';
                    this._anim(true);
                });
            });
        },

        toggle(optId) {
            this.value = (this.value == optId) ? null : optId;
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
                // Keep selected button visible, collapse others, show label
                const sel    = btns.find(b => String(b.dataset.id) === String(v));
                const others = btns.filter(b => String(b.dataset.id) !== String(v));
                if (withAnimation) {
                    if (sel) gsap.to(sel, { width: 40, opacity: 1, duration: 0.22, ease: 'power2.out', overwrite: true });
                    gsap.to(others, { width: 0, opacity: 0, duration: 0.22, ease: 'power2.in',  overwrite: true });
                    gsap.to(gaps,   { width: 0, opacity: 0, duration: 0.20, ease: 'power2.in',  overwrite: true });
                    gsap.to(label,  { width: 'auto', opacity: 1, duration: 0.28, delay: 0.18, ease: 'power2.out', overwrite: true });
                } else {
                    if (sel) gsap.set(sel, { width: 40, opacity: 1 });
                    gsap.set(others, { width: 0, opacity: 0 });
                    gsap.set(gaps,   { width: 0, opacity: 0 });
                    gsap.set(label,  { width: 'auto', opacity: 1 });
                }
            } else {
                // Expand all buttons back, hide label
                if (withAnimation) {
                    gsap.to(label, { width: 0, opacity: 0, duration: 0.18, ease: 'power2.in',       overwrite: true });
                    gsap.to(btns,  { width: 40, opacity: 1, duration: 0.28, delay: 0.15, ease: 'back.out(1.4)', overwrite: true });
                    gsap.to(gaps,  { width: 6,  opacity: 1, duration: 0.28, delay: 0.15, ease: 'back.out(1.4)', overwrite: true });
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
        <button
            type="button"
            data-id="{{ $option['id'] }}"
            title="{{ $option['label'] }}"
            @disabled($readOnly)
            @click="toggle({{ $option['id'] }})"
            class="icon-btn flex items-center justify-center w-10 h-10 rounded-lg border-2 shrink-0 overflow-hidden transition-colors duration-150"
            :class="value == {{ $option['id'] }}
                ? (colorSelected['{{ $option['color'] }}'] ?? colorSelected.gray)
                : colorUnselected"
        >
            <span class="pointer-events-none">{!! $option['icon_html'] !!}</span>
        </button>
        @if(!$loop->last)
            <span class="icon-gap inline-block shrink-0" style="width: 6px;"></span>
        @endif
    @endforeach

    {{-- Label: fades in beside the selected icon; click it to deselect --}}
    <div class="icon-label overflow-hidden whitespace-nowrap" style="width: 0; opacity: 0;">
        <span
            x-text="selectedLabel"
            @click="toggle(value)"
            class="ml-3 text-sm font-semibold text-gray-700 dark:text-gray-300 cursor-pointer select-none"
        ></span>
    </div>
</div>
