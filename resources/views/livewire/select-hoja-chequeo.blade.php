<div>
    <form wire:submit="nextPage">
        {{ $this->form }}
        <x-filament::button type="submit" class="mt-4">Siguiente</x-filament::button>
    </form>

    <x-filament-actions::modals/>
</div>
