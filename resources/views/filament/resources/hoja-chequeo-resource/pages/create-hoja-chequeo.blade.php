<x-filament-panels::page>
    <div class="flex items-center gap-4 mb-5">
        <form>{{ $this->form }}</form>
    </div>

    <livewire:create-items></livewire:create-items>

    <div class="w-1/3">
        <x-filament::button wire:click="create">Crear</x-filament::button>
    </div>

    <x-filament-actions::modals/>

</x-filament-panels::page>
