<div x-data="{ value: @entangle($attributes->wire('model')) }" class="flex items-center gap-3">
    <span class="text-sm" :class="value ? 'text-gray-400' : 'text-red-600 font-medium'">No</span>
    <button type="button" @click="value = !value"
            class="relative inline-flex h-6 w-11 items-center rounded-full transition"
            :class="value ? 'bg-green-600' : 'bg-gray-300'">
        <span class="inline-block h-4 w-4 transform rounded-full bg-white transition"
              :class="value ? 'translate-x-6' : 'translate-x-1'"></span>
    </button>
    <span class="text-sm" :class="value ? 'text-green-600 font-medium' : 'text-gray-400'">Si</span>
</div>
