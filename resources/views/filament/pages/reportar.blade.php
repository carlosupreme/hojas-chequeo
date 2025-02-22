<div class="min-h-screen py-12">
    <div class="mb-4">
        <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">
            Reportar falla de equipo
        </h1>
        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
            Complete el formulario para reportar una falla en el equipo
        </p>
    </div>

    <form wire:submit.prevent="submit">
        {{ $this->form }}

        <div class="flex justify-end pt-6">
            <x-filament::button
                type="submit"
                wire:loading.attr="disabled"
                class="filament-button-size-lg"
            >
                <x-slot name="icon">
                    <x-heroicon-m-clipboard-document-check
                        class="h-5 w-5"
                        wire:loading.remove.delay
                        wire:target="submit"
                    />

                    <x-filament::loading-indicator
                        class="h-5 w-5"
                        wire:loading.delay
                        wire:target="submit"
                    />
                </x-slot>

                Enviar reporte
            </x-filament::button>
        </div>
    </form>

    <x-filament-actions::modals/>
</div>
