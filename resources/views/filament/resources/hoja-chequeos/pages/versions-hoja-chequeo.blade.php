<x-filament-panels::page>
    @php
        $versions = $this->getVersions();
        $equipo = $record->equipo;
    @endphp

    {{-- Equipo Header --}}
    <div class="flex items-center gap-4 mb-6">
        <x-filament::badge color="gray" icon="heroicon-o-tag">{{ $equipo->tag }}</x-filament::badge>
        <x-filament::badge color="primary">{{ $equipo->nombre }}</x-filament::badge>
        @if($equipo->area)
            <x-filament::badge color="warning">{{ $equipo->area }}</x-filament::badge>
        @endif
        <span class="text-sm text-gray-500 dark:text-gray-400">
            {{ $versions->count() }} {{ Str::plural('versión', $versions->count()) }}
        </span>
    </div>

    {{-- Versions Grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 mb-8">
        @foreach($versions as $version)
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl p-4 shadow-sm flex flex-col gap-3">
                <div class="flex items-center justify-between">
                    <x-filament::badge color="primary" size="lg">
                        v{{ $version->version }}
                    </x-filament::badge>
                    @if($version->encendido)
                        <x-filament::badge color="success" icon="heroicon-o-check-circle">
                            Publicada
                        </x-filament::badge>
                    @else
                        <x-filament::badge color="gray" icon="heroicon-o-x-circle">
                            Inactiva
                        </x-filament::badge>
                    @endif
                </div>

                <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                    @svg('heroicon-o-clipboard-document-check', 'w-4 h-4 text-amber-500 shrink-0')
                    <span>{{ $version->chequeos_count }} {{ Str::plural('ejecución', $version->chequeos_count) }}</span>
                </div>

                <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                    @svg('heroicon-o-calendar', 'w-4 h-4 shrink-0')
                    <span>Creada: {{ $version->created_at->format('d/m/Y H:i') }}</span>
                </div>

                @if($version->observaciones)
                    <p class="text-xs text-gray-500 dark:text-gray-400 italic line-clamp-2">
                        {{ $version->observaciones }}
                    </p>
                @endif
            </div>
        @endforeach
    </div>

    {{-- Merge Button --}}
    @if($versions->count() > 1)
        <div class="mb-6">
            <livewire:hoja-chequeo.merge-wizard :equipo-id="$record->equipo_id" />
        </div>
    @else
        <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 p-6 text-center text-sm text-gray-500 dark:text-gray-400">
            @svg('heroicon-o-information-circle', 'w-5 h-5 mx-auto mb-2 text-gray-400')
            Solo hay una versión. Crea más versiones para poder fusionarlas.
        </div>
    @endif

    <x-filament-actions::modals/>
</x-filament-panels::page>
