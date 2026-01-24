@props(['options'])

<div
    x-data="{
        open: false,
        value: @entangle($attributes->wire('model')),
        options: @js($options),
        triggerWidth: 0,
        get selectedOption() {
            return this.options.find(o => o.id == this.value) || null
        },
        init() {
            // When opening, capture the button's width to apply to the dropdown
            this.$watch('open', (isOpen) => {
                if (isOpen) {
                    this.triggerWidth = this.$refs.trigger.getBoundingClientRect().width;
                }
            });
        }
    }"
    x-on:click.outside="open = false"
    class="relative w-full"
>

    {{-- TRIGGER BUTTON --}}
    <button
        x-ref="trigger"
        type="button"
        @click="open = !open"
        class="w-full border rounded-md px-4 py-3 flex items-center justify-between bg-white dark:bg-gray-800 text-sm border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-blue-500 transition-shadow"
    >
        <div class="flex items-center gap-2 truncate">
            <template x-if="selectedOption">
                <div class="flex items-center gap-2 truncate">
                    <div x-html="selectedOption.icon_html" class="shrink-0"></div>
                    <span x-text="selectedOption.label" class="truncate"></span>
                </div>
            </template>
            <span x-show="!selectedOption" class="text-gray-500">Elige…</span>
        </div>
        <svg class="w-4 h-4 text-gray-500 shrink-0 ml-2" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd"
                  d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.25a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z"
                  clip-rule="evenodd"/>
        </svg>
    </button>

    {{--
        DROPDOWN MENU
        1. x-teleport='body': Moves this div to the end of the <body> tag, escaping the table overflow.
        2. x-anchor: Tells Alpine to float this element next to $refs.trigger.
    --}}
    <template x-teleport="body">
        <div
            x-show="open"
            x-transition:enter="transition ease-out duration-100"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-75"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            x-anchor.bottom-start.offset.5="$refs.trigger"
            :style="'width: ' + triggerWidth + 'px'"
            class="z-9999 absolute mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-md shadow-xl"
        >
            <ul class="max-h-60 overflow-y-auto custom-scrollbar p-1">
                <template x-for="option in options" :key="option.id">
                    <li
                        @click="value = option.id; open = false"
                        class="px-3 py-2.5 cursor-pointer flex items-center gap-3 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md text-sm text-gray-700 dark:text-gray-200 transition-colors"
                        :class="{'bg-gray-50 dark:bg-gray-700/50': value == option.id}"
                    >
                        <div x-html="option.icon_html" class="shrink-0"></div>
                        <span x-text="option.label" class="font-medium"></span>

                        {{-- Optional: Checkmark for selected item --}}
                        <svg x-show="value == option.id" class="w-4 h-4 ml-auto text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </li>
                </template>
            </ul>
        </div>
    </template>
</div>
