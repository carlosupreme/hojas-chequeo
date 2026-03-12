<x-filament-panels::page class="space-y-6" wire:poll.3s="refreshLog">

    {{-- Status Banner --}}
    @if ($isRunning)
        <div class="flex items-center gap-3 rounded-xl border border-amber-200 bg-amber-50 p-4 dark:border-amber-700 dark:bg-amber-900/20">
            <svg class="h-5 w-5 animate-spin text-amber-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
            </svg>
            <div>
                <p class="font-semibold text-amber-800 dark:text-amber-300">Actualización en curso</p>
                <p class="text-sm text-amber-700 dark:text-amber-400">El proceso está corriendo en segundo plano. Refresca el log para ver el progreso.</p>
            </div>
        </div>
    @else
        <div class="flex items-center gap-3 rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-900">
            <x-heroicon-o-information-circle class="h-5 w-5 text-gray-400" />
            <div>
                <p class="font-semibold text-gray-700 dark:text-gray-300">Sistema listo para actualizar</p>
                <p class="text-sm text-gray-500">Presiona "Actualizar Sistema" para descargar los últimos cambios desde GitHub.</p>
            </div>
        </div>
    @endif

    {{-- Log Output --}}
    <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">
        <div class="flex items-center justify-between border-b border-gray-200 px-4 py-3 dark:border-gray-700">
            <h3 class="flex items-center gap-2 font-semibold text-gray-700 dark:text-gray-300">
                <x-heroicon-o-command-line class="h-4 w-4" />
                Log de Actualización
            </h3>
            <span class="text-xs text-gray-400">{{ file_exists('/tmp/deploy.log') ? 'Última actualización: ' . \Carbon\Carbon::createFromTimestamp(filemtime('/tmp/deploy.log'))->diffForHumans() : 'Sin logs' }}</span>
        </div>
        <pre
            id="deploy-log"
            class="max-h-[500px] overflow-y-auto p-4 font-mono text-xs leading-relaxed text-gray-800 dark:text-gray-200 whitespace-pre-wrap"
            x-data
            x-init="$el.scrollTop = $el.scrollHeight"
            x-on:livewire:navigated.window="$el.scrollTop = $el.scrollHeight"
        >{{ $this->logContent }}</pre>
    </div>

</x-filament-panels::page>
