<x-filament-panels::page class="!p-0 !max-w-full">
    {{-- Poll every 15s for reports/recorridos. Reverb handles chequeos instantly. --}}
    <div wire:poll.15s class="sr-only"></div>

    <div x-data="{ selectedAreaName: null, selectedEquipo: null }" class="space-y-4 p-4 md:p-6">

        {{-- ─── HEADER BAR ──────────────────────────────────────────────────────── --}}
        <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 px-4 py-3">
            <div class="flex items-center gap-3">
                <span class="relative flex h-2.5 w-2.5">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                    <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-green-500"></span>
                </span>
                <span class="font-semibold text-gray-800 dark:text-white">Panel de Control</span>
                <span class="text-xs text-gray-400">{{ now()->format('d/m/Y · H:i') }}</span>
            </div>

            <div class="flex flex-wrap items-center gap-5 text-sm">
                <div>
                    <span class="font-bold text-gray-900 dark:text-white">{{ $this->todayStats['chequeos'] }}</span>
                    <span class="text-gray-400 ml-1">chequeos hoy</span>
                </div>
                <div>
                    <span class="font-bold text-gray-900 dark:text-white">{{ $this->todayStats['recorridos'] }}</span>
                    <span class="text-gray-400 ml-1">recorridos</span>
                </div>
                <div>
                    <span class="font-bold text-gray-900 dark:text-white">{{ $this->todayStats['reportes'] }}</span>
                    <span class="text-gray-400 ml-1">reportes hoy</span>
                </div>
                @php $totalAlta = collect($this->equiposByArea)->flatMap(fn($a) => $a['equipos'])->sum('reportes_alta_prioridad'); @endphp
                @if($totalAlta > 0)
                    <span class="font-bold text-red-600 dark:text-red-400 animate-pulse">⚠ {{ $totalAlta }} urgente{{ $totalAlta > 1 ? 's' : '' }}</span>
                @else
                    <span class="font-bold text-green-600 dark:text-green-400">✓ Sin alertas críticas</span>
                @endif
            </div>
        </div>

        {{-- ─── ALERT STRIPS ────────────────────────────────────────────────────── --}}
        @foreach($this->alerts as $alert)
            @if($alert['type'] === 'danger')
                <a href="{{ $alert['link'] }}"
                   class="flex items-center gap-3 rounded-xl border border-red-300 dark:border-red-700 bg-red-50 dark:bg-red-900/20 px-4 py-2.5 text-sm hover:bg-red-100 dark:hover:bg-red-900/30 transition-colors">
                    <span class="relative flex h-2.5 w-2.5 flex-shrink-0">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                        <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-red-500"></span>
                    </span>
                    <span class="font-semibold text-red-700 dark:text-red-400">{{ $alert['title'] }}</span>
                    <span class="text-red-500/70 hidden sm:inline">— {{ $alert['description'] }}</span>
                    <x-heroicon-o-arrow-right class="w-4 h-4 text-red-400 ml-auto flex-shrink-0" />
                </a>
            @elseif($alert['type'] === 'warning')
                <a href="{{ $alert['link'] }}"
                   class="flex items-center gap-3 rounded-xl border border-amber-300 dark:border-amber-700 bg-amber-50 dark:bg-amber-900/20 px-4 py-2.5 text-sm hover:bg-amber-100 dark:hover:bg-amber-900/30 transition-colors">
                    <x-heroicon-o-exclamation-triangle class="w-4 h-4 text-amber-500 flex-shrink-0" />
                    <span class="font-semibold text-amber-700 dark:text-amber-400">{{ $alert['title'] }}</span>
                    <span class="text-amber-500/70 hidden sm:inline">— {{ $alert['description'] }}</span>
                    <x-heroicon-o-arrow-right class="w-4 h-4 text-amber-400 ml-auto flex-shrink-0" />
                </a>
            @endif
        @endforeach

        {{-- ─── BREADCRUMB ──────────────────────────────────────────────────────── --}}
        <div x-show="selectedAreaName !== null" x-cloak class="flex items-center gap-2 text-sm">
            <button @click="selectedAreaName = null; selectedEquipo = null"
                    class="flex items-center gap-1.5 text-primary-600 dark:text-primary-400 hover:underline font-medium">
                <x-heroicon-o-building-office-2 class="w-4 h-4" />
                Empresa
            </button>
            <x-heroicon-o-chevron-right class="w-3.5 h-3.5 text-gray-400" />
            <span class="font-semibold text-gray-900 dark:text-white" x-text="selectedAreaName"></span>
        </div>

        {{-- ─── OVERVIEW: AREA ZONES ────────────────────────────────────────────── --}}
        <div x-show="selectedAreaName === null"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">

            @foreach($this->equiposByArea as $areaData)
            @php
                $total      = count($areaData['equipos']);
                $conChequeo = collect($areaData['equipos'])->where('tiene_chequeo_hoy', true)->count();
                $pendientes = collect($areaData['equipos'])->sum('reportes_pendientes');
                $altaPrio   = collect($areaData['equipos'])->sum('reportes_alta_prioridad');

                [$border, $headerBg, $pillBg, $pillText, $statusLabel] = match(true) {
                    $altaPrio > 0              => ['border-red-500',   'bg-red-50 dark:bg-red-900/20',    'bg-red-100 dark:bg-red-900/40',    'text-red-600 dark:text-red-400',    'Alerta crítica'],
                    $conChequeo < $total || $pendientes > 0
                                               => ['border-amber-400', 'bg-amber-50 dark:bg-amber-900/20','bg-amber-100 dark:bg-amber-900/40','text-amber-600 dark:text-amber-400','Atención'],
                    default                    => ['border-green-500', 'bg-green-50 dark:bg-green-900/20', 'bg-green-100 dark:bg-green-900/40', 'text-green-600 dark:text-green-400', 'Todo en orden'],
                };
            @endphp

            <div @click="selectedAreaName = @js($areaData['area'])"
                 class="cursor-pointer rounded-xl border-2 {{ $border }} bg-white dark:bg-gray-900 shadow-sm hover:shadow-md transition-all duration-200 overflow-hidden group">

                <div class="{{ $headerBg }} px-4 py-3 flex items-center justify-between border-b border-inherit">
                    <h3 class="font-bold text-gray-900 dark:text-white">{{ $areaData['area'] }}</h3>
                    <span class="text-xs font-semibold px-2 py-0.5 rounded-full {{ $pillBg }} {{ $pillText }}">
                        {{ $statusLabel }}
                    </span>
                </div>

                <div class="grid grid-cols-3 divide-x divide-gray-100 dark:divide-gray-800 text-center py-4">
                    <div class="px-2">
                        <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $total }}</div>
                        <div class="text-xs text-gray-400 mt-0.5">equipos</div>
                    </div>
                    <div class="px-2">
                        <div class="text-2xl font-bold {{ $conChequeo === $total ? 'text-green-600' : 'text-amber-500' }}">
                            {{ $conChequeo }}/{{ $total }}
                        </div>
                        <div class="text-xs text-gray-400 mt-0.5">chequeos hoy</div>
                    </div>
                    <div class="px-2">
                        <div class="text-2xl font-bold {{ $pendientes > 0 ? ($altaPrio > 0 ? 'text-red-600' : 'text-amber-500') : 'text-gray-300 dark:text-gray-600' }}">
                            {{ $pendientes }}
                        </div>
                        <div class="text-xs text-gray-400 mt-0.5">reportes pend.</div>
                    </div>
                </div>

                <div class="px-4 pb-4">
                    <p class="text-xs uppercase tracking-wider text-gray-400 mb-2">Equipos</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach($areaData['equipos'] as $eq)
                        @php
                            $dotClass = match(true) {
                                $eq['reportes_alta_prioridad'] > 0                                   => 'bg-red-500 animate-pulse ring-2 ring-red-300 dark:ring-red-700',
                                $eq['tiene_chequeo_hoy'] && $eq['reportes_pendientes'] === 0         => 'bg-green-500',
                                default                                                               => 'bg-amber-400',
                            };
                        @endphp
                        <div class="w-4 h-4 rounded-sm {{ $dotClass }} transition-all duration-300"
                             title="{{ $eq['nombre'] }} · {{ $eq['tag'] }}"></div>
                        @endforeach
                    </div>
                </div>

                <div class="px-4 pb-3 flex items-center justify-end gap-1 text-xs text-gray-300 dark:text-gray-600 group-hover:text-primary-500 transition-colors duration-150">
                    <span>Ver equipos</span>
                    <x-heroicon-o-arrow-right class="w-3 h-3" />
                </div>
            </div>
            @endforeach
        </div>

        {{-- ─── AREA DRILL-DOWN ─────────────────────────────────────────────────── --}}
        @foreach($this->equiposByArea as $areaData)
        <div x-show="selectedAreaName === @js($areaData['area'])"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-x-2"
             x-transition:enter-end="opacity-100 translate-x-0"
             x-cloak
             class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-3">

            @foreach($areaData['equipos'] as $equipo)
            @php
                [$ring, $dot, $label, $labelColor, $labelBg] = match(true) {
                    $equipo['reportes_alta_prioridad'] > 0 => [
                        'ring-2 ring-red-500',
                        'bg-red-500 animate-pulse',
                        'Alerta',
                        'text-red-700 dark:text-red-300',
                        'bg-red-100 dark:bg-red-900/30',
                    ],
                    !$equipo['tiene_chequeo_hoy'] || $equipo['reportes_pendientes'] > 0 => [
                        'ring-2 ring-amber-400',
                        'bg-amber-400',
                        'Atención',
                        'text-amber-700 dark:text-amber-300',
                        'bg-amber-100 dark:bg-amber-900/30',
                    ],
                    default => [
                        'ring-2 ring-green-500',
                        'bg-green-500',
                        'OK',
                        'text-green-700 dark:text-green-300',
                        'bg-green-100 dark:bg-green-900/30',
                    ],
                };
            @endphp

            <div @click="selectedEquipo = @js($equipo)"
                 class="cursor-pointer bg-white dark:bg-gray-900 rounded-xl {{ $ring }} overflow-hidden hover:shadow-md transition-all duration-200 group">

                <div class="relative h-28 bg-gray-100 dark:bg-gray-800 overflow-hidden">
                    @if($equipo['foto_url'])
                        <img src="{{ $equipo['foto_url'] }}" alt="{{ $equipo['nombre'] }}"
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                             loading="lazy">
                    @else
                        <div class="w-full h-full flex items-center justify-center">
                            <x-heroicon-o-cog-6-tooth class="w-10 h-10 text-gray-300 dark:text-gray-600" />
                        </div>
                    @endif

                    <div class="absolute top-2 right-2">
                        <span class="relative flex h-3 w-3">
                            @if($equipo['reportes_alta_prioridad'] > 0)
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                            @endif
                            <span class="relative inline-flex h-3 w-3 rounded-full {{ $dot }}"></span>
                        </span>
                    </div>

                    @if($equipo['reportes_pendientes'] > 0)
                        <div class="absolute top-2 left-2">
                            <span class="bg-red-500 text-white text-xs font-bold px-1.5 py-0.5 rounded-full leading-none">
                                {{ $equipo['reportes_pendientes'] }}
                            </span>
                        </div>
                    @endif
                </div>

                <div class="p-2.5">
                    <div class="font-semibold text-gray-900 dark:text-white text-sm truncate">{{ $equipo['nombre'] }}</div>
                    <div class="font-mono text-xs text-gray-400 dark:text-gray-500 mt-0.5">{{ $equipo['tag'] }}</div>
                    <div class="mt-2 flex items-center justify-between gap-1">
                        <span class="inline-flex items-center gap-1 text-xs font-medium px-1.5 py-0.5 rounded {{ $labelBg }} {{ $labelColor }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $dot }}"></span>
                            {{ $label }}
                        </span>
                        @if($equipo['ultimo_chequeo_viejo'])
                            <span class="text-xs text-amber-500 font-medium">Vencido</span>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endforeach

        {{-- ─── LEGEND ──────────────────────────────────────────────────────────── --}}
        <div class="flex flex-wrap items-center gap-5 pt-1 text-xs text-gray-400">
            <span class="flex items-center gap-1.5">
                <span class="w-3 h-3 rounded-sm bg-green-500 inline-block"></span> Todo en orden
            </span>
            <span class="flex items-center gap-1.5">
                <span class="w-3 h-3 rounded-sm bg-amber-400 inline-block"></span> Sin chequeo / reportes pendientes
            </span>
            <span class="flex items-center gap-1.5">
                <span class="w-3 h-3 rounded-sm bg-red-500 inline-block animate-pulse"></span> Reporte urgente
            </span>
        </div>

        {{-- ─── EQUIPMENT DETAIL MODAL ──────────────────────────────────────────── --}}
        <div x-show="selectedEquipo !== null"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click.self="selectedEquipo = null"
             @keydown.escape.window="selectedEquipo = null"
             style="display:none"
             class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[200] flex items-center justify-center p-4">

            <div x-show="selectedEquipo !== null"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 class="bg-white dark:bg-gray-900 rounded-2xl shadow-2xl w-full max-w-md overflow-hidden">

                {{-- Photo header --}}
                <div class="relative h-48 bg-gray-100 dark:bg-gray-800">
                    <img x-show="selectedEquipo?.foto_url"
                         :src="selectedEquipo?.foto_url"
                         :alt="selectedEquipo?.nombre"
                         class="w-full h-full object-cover">
                    <div x-show="!selectedEquipo?.foto_url"
                         class="w-full h-full flex items-center justify-center">
                        <x-heroicon-o-cog-6-tooth class="w-20 h-20 text-gray-300 dark:text-gray-600" />
                    </div>

                    <button @click="selectedEquipo = null"
                            class="absolute top-3 right-3 bg-black/40 hover:bg-black/60 text-white rounded-full p-1.5 transition-colors">
                        <x-heroicon-o-x-mark class="w-4 h-4" />
                    </button>

                    {{-- Status pill over photo --}}
                    <div class="absolute bottom-3 left-3">
                        <span x-show="selectedEquipo?.reportes_alta_prioridad > 0"
                              class="inline-flex items-center gap-1.5 bg-red-500 text-white text-xs font-bold px-2.5 py-1 rounded-full">
                            <span class="w-1.5 h-1.5 rounded-full bg-white animate-pulse"></span>
                            Alerta crítica
                        </span>
                        <span x-show="selectedEquipo?.reportes_alta_prioridad === 0 && selectedEquipo?.tiene_chequeo_hoy && selectedEquipo?.reportes_pendientes === 0"
                              class="inline-flex items-center gap-1.5 bg-green-500 text-white text-xs font-bold px-2.5 py-1 rounded-full">
                            ✓ Todo en orden
                        </span>
                        <span x-show="selectedEquipo?.reportes_alta_prioridad === 0 && (!selectedEquipo?.tiene_chequeo_hoy || selectedEquipo?.reportes_pendientes > 0)"
                              class="inline-flex items-center gap-1.5 bg-amber-400 text-white text-xs font-bold px-2.5 py-1 rounded-full">
                            ⚠ Atención
                        </span>
                    </div>
                </div>

                {{-- Content --}}
                <div class="p-5 space-y-4">
                    <div>
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white" x-text="selectedEquipo?.nombre"></h2>
                        <div class="flex items-center gap-2 mt-1">
                            <code class="text-xs font-mono bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 px-2 py-0.5 rounded"
                                  x-text="selectedEquipo?.tag"></code>
                            <span class="text-xs text-gray-400" x-text="selectedEquipo?.area"></span>
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-2">
                        <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-3 text-center">
                            <div class="text-lg font-bold"
                                 :class="selectedEquipo?.tiene_chequeo_hoy ? 'text-green-600' : 'text-gray-400'"
                                 x-text="selectedEquipo?.tiene_chequeo_hoy ? '✓' : '—'"></div>
                            <div class="text-xs text-gray-500 mt-0.5">Chequeo hoy</div>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-3 text-center">
                            <div class="text-lg font-bold"
                                 :class="selectedEquipo?.reportes_pendientes > 0 ? 'text-amber-500' : 'text-gray-400'"
                                 x-text="selectedEquipo?.reportes_pendientes"></div>
                            <div class="text-xs text-gray-500 mt-0.5">Reps. pend.</div>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-3 text-center">
                            <div class="text-lg font-bold"
                                 :class="selectedEquipo?.reportes_alta_prioridad > 0 ? 'text-red-600' : 'text-gray-400'"
                                 x-text="selectedEquipo?.reportes_alta_prioridad"></div>
                            <div class="text-xs text-gray-500 mt-0.5">Alta prioridad</div>
                        </div>
                    </div>

                    <div class="space-y-2 text-sm">
                        <div class="flex items-center justify-between py-1.5 border-b border-gray-100 dark:border-gray-800">
                            <span class="text-gray-500">Último chequeo</span>
                            <span class="font-medium text-gray-900 dark:text-white"
                                  x-text="selectedEquipo?.ultimo_chequeo ?? 'Sin registro'"></span>
                        </div>
                        <template x-if="selectedEquipo?.capacidad">
                            <div class="flex items-center justify-between py-1.5 border-b border-gray-100 dark:border-gray-800">
                                <span class="text-gray-500">Capacidad</span>
                                <span class="font-medium text-gray-900 dark:text-white"
                                      x-text="selectedEquipo?.capacidad"></span>
                            </div>
                        </template>
                        <template x-if="selectedEquipo?.ultimo_chequeo_viejo">
                            <div class="flex items-center gap-2 text-amber-600 dark:text-amber-400 text-xs bg-amber-50 dark:bg-amber-900/20 rounded-lg px-3 py-2">
                                <x-heroicon-o-exclamation-triangle class="w-4 h-4 flex-shrink-0" />
                                El último chequeo supera las 24 horas
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>

    </div>
</x-filament-panels::page>
