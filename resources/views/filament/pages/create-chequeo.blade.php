<x-filament-panels::page>
    {{-- Top Header Section --}}
    @if($hojaChequeo && $this->hasItems())
        <div data-animate="chequeo-items">
            <div class="flex sm:flex-row flex-col gap-4 items-center w-full mb-6 mt-3 place-content-center">
                <button wire:click="resetState"
                        class="flex items-center  px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-700">
                    @svg('heroicon-o-arrow-left', 'w-4 h-4 mr-4')
                    Regresar
                </button>
                <div class="flex gap-4 items-center place-content-center flex-wrap">
                    <h2 class="font-bold">{{ $this->hojaChequeo->equipo->nombre }}</h2>
                    <x-filament::badge color="primary">{{$this->hojaChequeo->equipo->tag}}</x-filament::badge>
                    <x-filament::badge color="primary">{{$user->turno->nombre}}</x-filament::badge>
                    @if($this->hojaEjecucion)
                        <x-filament::badge color="primary">Reanudando</x-filament::badge>
                    @endif
                </div>

                {{$this->dateForm}}
            </div>

            <livewire:chequeo-items :hoja="$hojaChequeo" :ejecucion="$hojaEjecucion"/>

            @if($this->hojaChequeo->observaciones)
                <div class="my-4">
                    <h2 class="font-bold">Observaciones:</h2>
                    {!! $this->hojaChequeo->observaciones !!}
                </div>
            @endif

            <form>
                {{ $this->form }}
            </form>

            <div class="flex w-full gap-2 my-5 items-center place-content-end">
                <x-filament::button wire:click="create" class="mt-4">Guardar</x-filament::button>
            </div>
        </div>
        <x-filament-actions::modals/>
    @else
        <livewire:select-hoja-chequeo/>
    @endif

</x-filament-panels::page>
