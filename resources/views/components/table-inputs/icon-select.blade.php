@props(['options'])

<div x-data="{
    open: false,
    value: @entangle($attributes->wire('model')),
    options: @js($options),
    get selectedOption() {
        return this.options.find(o => o.id == this.value) || null
    }
}" x-on:click.outside="open = false" class="relative">

    <button type="button" @click="open = !open"
            class="w-full border rounded-md px-4 py-3 flex items-center justify-between bg-white dark:bg-gray-800 text-sm">
        <div class="flex items-center gap-2">
            <template x-if="selectedOption">
                <div class="flex items-center gap-2">
                    <div x-html="selectedOption.icon_html"></div>
                    <span x-text="selectedOption.label"></span>
                </div>
            </template>
            <span x-show="!selectedOption" class="text-gray-500">Elige…</span>
        </div>
        <svg class="w-4 h-4 text-gray-500" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd"
                  d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.25a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z"
                  clip-rule="evenodd"/>
        </svg>
    </button>

    <div x-show="open" x-transition
         class="absolute z-50 w-full mt-1 bg-white dark:bg-gray-800 border rounded-md shadow-lg">
        <ul class="max-h-60 overflow-auto">
            <template x-for="option in options" :key="option.id">
                <li @click="value = option.id; open = false"
                    class="px-4 py-3 cursor-pointer flex items-center gap-2 hover:bg-gray-100 dark:hover:bg-gray-700 text-sm">
                    <div x-html="option.icon_html"></div>
                    <span x-text="option.label"></span>
                </li>
            </template>
        </ul>
    </div>
</div>
