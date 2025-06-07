<div class="bg-white dark:bg-gray-900 sm:px-4 py-5 rounded-lg px-2">
    @if($page === 1)
        <livewire:select-hoja-chequeo />
    @else
        <button wire:click="resetState"
            class="flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-700">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18">
                </path>
            </svg>
            Regresar
        </button>
        <div class="flex sm:flex-row flex-col gap-4 items-center w-full my-5 place-content-center">
            <div class="flex gap-4 items-center place-content-center">
                <h2 class="font-bold">{{ $this->checkSheet->equipo->nombre }}</h2>
                <x-filament::badge color="warning">{{$this->checkSheet->equipo->tag}}</x-filament::badge>
            </div>
            <div class="flex items-center space-x-2">
                <x-filament::button class="text-gray-800 dark:text-gray-200" color="secondary" size="sm" x-data="{}"
                    x-on:click="$dispatch('open-modal', { id: 'date-picker-modal' })">
                    <span class="flex items-center gap-1 text-gray-800 dark:text-gray-200">
                        <x-heroicon-o-calendar class="w-4 h-4" />
                        {{$this->dateSelected->format('d/m/Y')}}
                    </span>
                </x-filament::button>
                <x-filament::modal id="date-picker-modal" width="md">
                    <x-slot name="heading">
                        Seleccionar fecha
                    </x-slot>

                    <div class="space-y-4">
                        <input 
                        type="date" 
                        wire:model.live="tempDateSelected"
                        class="block w-full rounded-md border-gray-300 bg-gray-100 text-gray-800 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:placeholder-gray-400"
                        x-on:change="$wire.updateSelectedDate()"
                    />
                    </div>

                    <x-slot name="footerActions">
                        <x-filament::button color="gray" x-on:click="$dispatch('close-modal', { id: 'date-picker-modal' })">
                            Cancelar
                        </x-filament::button>

                        <x-filament::button color="primary" wire:click="updateSelectedDate"
                            x-on:click="$dispatch('close-modal', { id: 'date-picker-modal' })">
                            Aplicar
                        </x-filament::button>
                    </x-slot>
                </x-filament::modal>
            </div>
        </div>

        @if($this->hasItems())
            <livewire:chequeo-items :items=" $checkSheet->items" />

            <div class="my-5">
               
                {!! $this->checkSheet->observaciones !!}
            </div>

            <form>
                {{ $this->form }}
            </form>

            <div class="mt-4">
                @error('items')
                    <span class="text-red-500">{{ $message }}</span>
                @enderror
            </div>

            <div class="flex w-full gap-2 my-5 items-center place-content-end">
                <x-filament::button wire:click="save" class="mt-4">Guardar</x-filament::button>
            </div>
            <x-filament-actions::modals />
        @else
            <div class="bg-gray-50 gap-4 w-full grid place-items-center p-4 mb-4">
                <h2 class="font-bold text-slate-600">No hay items en esta hoja de chequeo</h2>
                <x-filament::button wire:click="resetState" color="gray" class="mt-4">Elegir otra</x-filament::button>
            </div>
        @endif
    @endif 
</div>