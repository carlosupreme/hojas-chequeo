<x-filament-panels::page>
    <livewire:chequeo-items :items=" $record->hojaChequeo->items" :default-values="$defaultValues"/>

    <div class="flex w-full gap-2 mb-5 items-center place-content-end">
        <x-filament::button wire:click="update" class="mt-4">Editar</x-filament::button>
    </div>
</x-filament-panels::page>
