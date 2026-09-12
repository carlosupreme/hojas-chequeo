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

    {{-- Database Backup Card --}}
    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-900">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-start gap-3.5">
                <div class="p-2.5 rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-950/50 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-800/50">
                    <x-heroicon-o-circle-stack class="h-6 w-6" />
                </div>
                <div>
                    <h3 class="font-semibold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                        Copia de Seguridad de la Base de Datos
                        <span class="inline-flex items-center rounded-md bg-emerald-50 px-2 py-0.5 text-xs font-medium text-emerald-700 ring-1 ring-inset ring-emerald-600/20 dark:bg-emerald-950 dark:text-emerald-300">Recomendado</span>
                    </h3>
                    <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">
                        Descarga un archivo comprimido (<code class="text-xs">.tgz</code> o <code class="text-xs">.zip</code>) con el volcado completo de PostgreSQL antes de aplicar actualizaciones.
                    </p>
                    <div class="mt-2 flex flex-wrap items-center gap-2 text-xs text-gray-400">
                        <span class="inline-flex items-center gap-1">
                            <span class="font-medium text-gray-600 dark:text-gray-300">Base de datos:</span>
                            <code class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-gray-700 dark:bg-gray-800 dark:text-gray-300">{{ config('database.connections.pgsql.database', 'laravel') }}</code>
                        </span>
                        <span>•</span>
                        <span class="inline-flex items-center gap-1">
                            <span class="font-medium text-gray-600 dark:text-gray-300">Servidor:</span>
                            <code class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-gray-700 dark:bg-gray-800 dark:text-gray-300">{{ config('database.connections.pgsql.host', '127.0.0.1') }}:{{ config('database.connections.pgsql.port', '5432') }}</code>
                        </span>
                    </div>
                </div>
            </div>
            <div class="flex-shrink-0">
                {{ $this->getAction('downloadDatabase') }}
            </div>
        </div>
    </div>

    {{-- System Configuration Card --}}
    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-900">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-start gap-3.5">
                <div class="p-2.5 rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-950/50 dark:text-blue-400 border border-blue-100 dark:border-blue-800/50">
                    <x-heroicon-o-cog-6-tooth class="h-6 w-6" />
                </div>
                <div>
                    <h3 class="font-semibold text-gray-900 dark:text-gray-100 flex items-center gap-2">
                        Configuración Actual del Servidor
                        <span class="inline-flex items-center rounded-md bg-blue-50 px-2 py-0.5 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-600/20 dark:bg-blue-950 dark:text-blue-300">Nginx • PHP • PostgreSQL</span>
                    </h3>
                    <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">
                        Descarga un archivo comprimido (<code class="text-xs">.tgz</code> o <code class="text-xs">.zip</code>) con la configuración activa de Nginx, el archivo <code class="text-xs">php.ini</code> y la configuración de PostgreSQL (<code class="text-xs">postgresql.conf</code>, <code class="text-xs">pg_hba.conf</code> y directivas activas).
                    </p>
                    <div class="mt-2 flex flex-wrap items-center gap-2 text-xs text-gray-400">
                        <span class="inline-flex items-center gap-1">
                            <span class="font-medium text-gray-600 dark:text-gray-300">Nginx:</span>
                            <code class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-gray-700 dark:bg-gray-800 dark:text-gray-300">nginx.conf + sitios</code>
                        </span>
                        <span>•</span>
                        <span class="inline-flex items-center gap-1">
                            <span class="font-medium text-gray-600 dark:text-gray-300">PHP:</span>
                            <code class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-gray-700 dark:bg-gray-800 dark:text-gray-300">{{ basename(php_ini_loaded_file() ?: 'php.ini') }} + FPM</code>
                        </span>
                        <span>•</span>
                        <span class="inline-flex items-center gap-1">
                            <span class="font-medium text-gray-600 dark:text-gray-300">PostgreSQL:</span>
                            <code class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-gray-700 dark:bg-gray-800 dark:text-gray-300">postgresql.conf + pg_hba.conf</code>
                        </span>
                    </div>
                </div>
            </div>
            <div class="flex-shrink-0">
                {{ $this->getAction('downloadConfiguration') }}
            </div>
        </div>
    </div>

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
