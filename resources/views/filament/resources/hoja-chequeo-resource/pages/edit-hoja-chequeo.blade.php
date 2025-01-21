<x-filament-panels::page>
    <div>
        <form>
            {{ $this->form }}
        </form>

        <x-filament-actions::modals/>
    </div>

    <livewire:update-items :record="$record"></livewire:update-items>

    <div class="w-1/3">
        <x-filament::button wire:click="update">
            {{ __('Update') }}
        </x-filament::button>
    </div>

</x-filament-panels::page>
