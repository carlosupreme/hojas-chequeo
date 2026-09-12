@props(['itemId' => null, 'readOnly' => false])

<div
    x-data="{
        itemId: {{ $itemId ? (int)$itemId : 'null' }},
        @if($itemId)
        value: false,
        @else
        value: @entangle($attributes->wire('model')),
        @endif
        init() {
            if (this.itemId && typeof this.form !== 'undefined') {
                this.value = Boolean(this.form[this.itemId]);
                this.$watch('form[' + this.itemId + ']', (v) => {
                    this.value = Boolean(v);
                });
            }
        },
        toggle() {
            if ({{ $readOnly ? 'true' : 'false' }}) return;
            this.value = !this.value;
            if (this.itemId && typeof this.setAnswer === 'function') {
                this.setAnswer(this.itemId, this.value);
            }
        }
    }"
    class="flex items-center gap-3"
>
    <span class="text-xs font-semibold select-none" :class="value ? 'text-gray-400 dark:text-gray-500' : 'text-red-600 dark:text-red-400 font-bold'">NO</span>
    <button type="button" @click="toggle()"
            class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors duration-150 focus:outline-none"
            :class="value ? 'bg-green-600' : 'bg-gray-300 dark:bg-gray-700'">
        <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform duration-150 shadow-sm"
              :class="value ? 'translate-x-6' : 'translate-x-1'"></span>
    </button>
    <span class="text-xs font-semibold select-none" :class="value ? 'text-green-600 dark:text-green-400 font-bold' : 'text-gray-400 dark:text-gray-500'">SÍ</span>
</div>

