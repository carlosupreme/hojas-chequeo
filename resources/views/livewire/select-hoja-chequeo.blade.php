<div>
    <form wire:submit="nextPage" class="flex flex-col gap-5">
        {{ $this->form }}
        <x-filament::button type="submit" class="w-1/3">Siguiente</x-filament::button>
    </form>

    <x-filament-actions::modals/>
</div>
