<x-filament-widgets::widget>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        {{-- Top Ejecuciones --}}
        <div class="fi-wi-stats-overview-stat rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="flex items-center gap-x-2 mb-4">
                <span class="fi-wi-stats-overview-stat-icon flex items-center justify-center rounded-lg bg-primary-50 p-2 dark:bg-primary-400/10">
                    <x-heroicon-o-clipboard-document-check class="h-6 w-6 text-primary-600 dark:text-primary-400" />
                </span>
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">
                    Top 3 más Chequeos
                </span>
            </div>
            <div class="space-y-3">
                @forelse($this->getTopEjecuciones() as $index => $item)
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-x-3">
                            <span @class([
                                'flex items-center justify-center w-6 h-6 rounded-full text-xs font-bold mr-1',
                                'bg-yellow-100 text-yellow-800 dark:bg-yellow-400/20 dark:text-yellow-400' => $index === 0,
                                'bg-gray-100 text-gray-800 dark:bg-gray-400/20 dark:text-gray-400' => $index === 1,
                                'bg-amber-100 text-amber-800 dark:bg-amber-400/20 dark:text-amber-400' => $index === 2,
                            ])>
                                {{ $index + 1 }}
                            </span>
                            <span class="text-sm font-medium text-gray-900 dark:text-white truncate max-w-[120px]" title="{{ $item['nombre_operador'] }}">
                                {{ $item['nombre_operador'] }}
                            </span>
                        </div>
                        <span class="text-lg font-bold text-primary-600 dark:text-primary-400">
                            {{ $item['total'] }}
                        </span>
                    </div>
                @empty
                    <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">Sin datos</p>
                @endforelse
            </div>
        </div>

        {{-- Top Reportes --}}
        <div class="fi-wi-stats-overview-stat rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="flex items-center gap-x-2 mb-4">
                <span class="fi-wi-stats-overview-stat-icon flex items-center justify-center rounded-lg bg-danger-50 p-2 dark:bg-danger-400/10">
                    <x-heroicon-o-document-text class="h-6 w-6 text-danger-600 dark:text-danger-400" />
                </span>
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">
                    Top 3 - Reportes
                </span>
            </div>
            <div class="space-y-3">
                @forelse($this->getTopReportes() as $index => $item)
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-x-3">
                            <span @class([
                                'flex items-center justify-center w-6 h-6 rounded-full text-xs font-bold mr-1',
                                'bg-yellow-100 text-yellow-800 dark:bg-yellow-400/20 dark:text-yellow-400' => $index === 0,
                                'bg-gray-100 text-gray-800 dark:bg-gray-400/20 dark:text-gray-400' => $index === 1,
                                'bg-amber-100 text-amber-800 dark:bg-amber-400/20 dark:text-amber-400' => $index === 2,
                            ])>
                                {{ $index + 1 }}
                            </span>
                            <span class="text-sm font-medium text-gray-900 dark:text-white truncate max-w-[120px]" title="{{ $item['nombre'] }}">
                                {{ $item['nombre'] }}
                            </span>
                        </div>
                        <span class="text-lg font-bold text-danger-600 dark:text-danger-400">
                            {{ $item['total'] }}
                        </span>
                    </div>
                @empty
                    <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">Sin datos</p>
                @endforelse
            </div>
        </div>

        {{-- Top Recorridos --}}
        <div class="fi-wi-stats-overview-stat rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="flex items-center gap-x-2 mb-4">
                <span class="fi-wi-stats-overview-stat-icon flex items-center justify-center rounded-lg bg-success-50 p-2 dark:bg-success-400/10">
                    <x-heroicon-o-clipboard-document-list class="h-6 w-6 text-success-600 dark:text-success-400" />
                </span>
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">
                    Top 3 - Recorridos
                </span>
            </div>
            <div class="space-y-3">
                @forelse($this->getTopRecorridos() as $index => $item)
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-x-3">
                            <span @class([
                                'flex items-center justify-center w-6 h-6 rounded-full text-xs font-bold mr-1',
                                'bg-yellow-100 text-yellow-800 dark:bg-yellow-400/20 dark:text-yellow-400' => $index === 0,
                                'bg-gray-100 text-gray-800 dark:bg-gray-400/20 dark:text-gray-400' => $index === 1,
                                'bg-amber-100 text-amber-800 dark:bg-amber-400/20 dark:text-amber-400' => $index === 2,
                            ])>
                                {{ $index + 1 }}
                            </span>
                            <span class="text-sm font-medium text-gray-900 dark:text-white truncate max-w-[120px]" title="{{ $item['nombre'] }}">
                                {{ $item['nombre'] }}
                            </span>
                        </div>
                        <span class="text-lg font-bold text-success-600 dark:text-success-400">
                            {{ $item['total'] }}
                        </span>
                    </div>
                @empty
                    <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">Sin datos</p>
                @endforelse
            </div>
        </div>
    </div>
</x-filament-widgets::widget>
