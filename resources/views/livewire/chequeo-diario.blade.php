<div class="bg-white dark:bg-gray-900 sm:px-4 py-5 rounded-lg px-2">
    @if($page === 1)
        <livewire:select-hoja-chequeo/>
    @else
        <button
            wire:click="resetState"
            class="flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-700"
        >
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Regresar
        </button>
        <div class="flex sm:flex-row flex-col gap-4 items-center w-full my-5 place-content-center">
            <div class="flex gap-4 items-center place-content-center">
                <h2 class="font-bold">{{ $this->checkSheet->equipo->nombre }}</h2>
                <x-filament::badge color="warning">{{$this->checkSheet->equipo->tag}}</x-filament::badge>
            </div>
            <x-filament::badge>{{\Carbon\Carbon::now()->format('d/m/Y')}}</x-filament::badge>
        </div>

        @if($this->hasItems())
            <livewire:chequeo-items :items=" $checkSheet->items"/>

            <div class="my-5">
                <h2 class="font-bold">Observaciones</h2>
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
            <x-filament-actions::modals/>
        @else
            <div class="bg-gray-50 gap-4 w-full grid place-items-center p-4 mb-4">
                <h2 class="font-bold text-slate-600">No hay items en esta hoja de chequeo</h2>
                <x-filament::button wire:click="resetState" color="gray" class="mt-4">Elegir otra</x-filament::button>
            </div>
        @endif
    @endif
</div>
