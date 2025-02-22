<div class="relative" x-data="{open: @entangle('open')}">
    <button
        wire:click="$toggle('open')"
        type="button"
        class="w-full bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm px-4 py-2 inline-flex justify-between items-center text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
    >
        <span>{{ $selectedName }}</span>
        <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
             aria-hidden="true">
            <path fill-rule="evenodd"
                  d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z"
                  clip-rule="evenodd"/>
        </svg>
    </button>

    @if($open)
        <div
            x-show="open"
            x-trap="open"
            @click.away="$parent.close()"
            x-transition:enter="transition ease-out duration-100"
            x-transition:enter-start="transform opacity-0 scale-95"
            x-transition:enter-end="transform opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-75"
            x-transition:leave-start="transform opacity-100 scale-100"
            x-transition:leave-end="transform opacity-0 scale-95"
            class="absolute z-50 mt-1 w-full flex flex-col gap-2 bg-white dark:bg-gray-700 shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm"
        >
            @foreach ($statuses as $status)
                <button wire:click="choose({{ $status->id }})"
                        wire:key="status-{{ $status->id }}"
                        class="cursor-pointer select-none relative py-2 pl-3 pr-9 hover:bg-gray-100 dark:hover:bg-gray-600"
                >
                    <div class="flex items-center">
                    <span class="mr-3" style="color: {{ $status->color }}">
                        <x-dynamic-component :component="$status->icono" class="h-5 w-5"/>
                    </span>
                        <span class="font-normal block truncate min-w-fit">
                        {{ $status->nombre }}
                    </span>
                    </div>
                </button>
            @endforeach

            <div
                wire:click="choose('custom')"
                class="cursor-pointer select-none relative py-2 pl-3 pr-9 hover:bg-gray-100 dark:hover:bg-gray-600"
            >
                <div class="flex items-center">
                <span class="text-gray-600 dark:text-gray-400 mr-3">
                    <x-heroicon-o-pencil class="h-5 w-5"/>
                </span>
                    <span class="font-normal block truncate">
                    Personalizado
                </span>
                </div>
            </div>
        </div>
    @endif

    @if ($showCustomInput)
        <div class="mt-2">
            <x-filament::input.wrapper>
                <x-filament::input
                    wire:model.live.debounce.500ms="customText"
                    placeholder="Escribe el estado"
                    class="w-full"
                />
            </x-filament::input.wrapper>
        </div>
    @endif
</div>
