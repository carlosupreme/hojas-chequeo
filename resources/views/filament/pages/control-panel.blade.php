<x-filament-panels::page class="min-h-screen space-y-6">

    {{-- HEADER --}}
    <div class="bg-white dark:bg-gray-900 p-6 rounded-xl shadow-sm border border-gray-200 dark:border-gray-800">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Estado Actual del Negocio</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Resumen en tiempo real &mdash; {{ now()->locale('es')->isoFormat('dddd D [de] MMMM [de] YYYY') }}
                </p>
            </div>
            <div class="flex items-center gap-2 text-xs text-gray-400">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                </span>
                Actualizado: {{ now()->format('H:i') }}
            </div>
        </div>
    </div>

    {{-- ALERTS SECTION --}}
    @php $alerts = $this->alerts; @endphp
    @if(count($alerts) > 0)
        <div class="space-y-3">
            @foreach($alerts as $alert)
                @php
                    $colors = match($alert['type']) {
                        'danger'  => ['bg' => 'bg-red-50 dark:bg-red-950/40', 'border' => 'border-red-200 dark:border-red-800', 'icon_bg' => 'bg-red-100 dark:bg-red-900/50', 'icon' => 'text-red-600 dark:text-red-400', 'title' => 'text-red-800 dark:text-red-300', 'desc' => 'text-red-600 dark:text-red-400', 'pulse' => true],
                        'warning' => ['bg' => 'bg-amber-50 dark:bg-amber-950/40', 'border' => 'border-amber-200 dark:border-amber-800', 'icon_bg' => 'bg-amber-100 dark:bg-amber-900/50', 'icon' => 'text-amber-600 dark:text-amber-400', 'title' => 'text-amber-800 dark:text-amber-300', 'desc' => 'text-amber-600 dark:text-amber-400', 'pulse' => false],
                        'info'    => ['bg' => 'bg-blue-50 dark:bg-blue-950/40', 'border' => 'border-blue-200 dark:border-blue-800', 'icon_bg' => 'bg-blue-100 dark:bg-blue-900/50', 'icon' => 'text-blue-600 dark:text-blue-400', 'title' => 'text-blue-800 dark:text-blue-300', 'desc' => 'text-blue-600 dark:text-blue-400', 'pulse' => false],
                        'success' => ['bg' => 'bg-green-50 dark:bg-green-950/40', 'border' => 'border-green-200 dark:border-green-800', 'icon_bg' => 'bg-green-100 dark:bg-green-900/50', 'icon' => 'text-green-600 dark:text-green-400', 'title' => 'text-green-800 dark:text-green-300', 'desc' => 'text-green-600 dark:text-green-400', 'pulse' => false],
                        default   => ['bg' => 'bg-gray-50 dark:bg-gray-900', 'border' => 'border-gray-200 dark:border-gray-800', 'icon_bg' => 'bg-gray-100 dark:bg-gray-800', 'icon' => 'text-gray-600 dark:text-gray-400', 'title' => 'text-gray-800 dark:text-gray-300', 'desc' => 'text-gray-600 dark:text-gray-400', 'pulse' => false],
                    };
                @endphp
                <div class="{{ $colors['bg'] }} {{ $colors['border'] }} border rounded-xl p-4 flex items-start gap-4">
                    <div class="shrink-0">
                        <div class="p-2.5 rounded-lg {{ $colors['icon_bg'] }} relative">
                            @if($alert['type'] === 'danger')
                                <span class="absolute inset-0 rounded-lg animate-ping bg-red-400/20"></span>
                            @endif
                            @switch($alert['icon'])
                                @case('fire')
                                    <x-heroicon-s-fire class="w-5 h-5 {{ $colors['icon'] }} relative" />
                                    @break
                                @case('clipboard-document-check')
                                    <x-heroicon-s-clipboard-document-check class="w-5 h-5 {{ $colors['icon'] }} relative" />
                                    @break
                                @case('exclamation-triangle')
                                    <x-heroicon-s-exclamation-triangle class="w-5 h-5 {{ $colors['icon'] }} relative" />
                                    @break
                                @case('information-circle')
                                    <x-heroicon-s-information-circle class="w-5 h-5 {{ $colors['icon'] }} relative" />
                                    @break
                                @case('check-circle')
                                    <x-heroicon-s-check-circle class="w-5 h-5 {{ $colors['icon'] }} relative" />
                                    @break
                                @default
                                    <x-heroicon-s-bell-alert class="w-5 h-5 {{ $colors['icon'] }} relative" />
                            @endswitch
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold {{ $colors['title'] }}">{{ $alert['title'] }}</p>
                        <p class="text-xs {{ $colors['desc'] }} mt-0.5">{{ $alert['description'] }}</p>
                    </div>
                    <div class="flex items-center gap-2 shrink-0 self-center">
                        @if(!empty($alert['link']))
                            <a
                                href="{{ $alert['link'] }}"
                                class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold border transition-colors
                                    {{ $alert['type'] === 'danger'
                                        ? 'bg-red-100 border-red-300 text-red-700 hover:bg-red-200 dark:bg-red-900/40 dark:border-red-700 dark:text-red-300 dark:hover:bg-red-900/70'
                                        : ($alert['type'] === 'warning'
                                            ? 'bg-amber-100 border-amber-300 text-amber-700 hover:bg-amber-200 dark:bg-amber-900/40 dark:border-amber-700 dark:text-amber-300 dark:hover:bg-amber-900/70'
                                            : ($alert['type'] === 'info'
                                                ? 'bg-blue-100 border-blue-300 text-blue-700 hover:bg-blue-200 dark:bg-blue-900/40 dark:border-blue-700 dark:text-blue-300 dark:hover:bg-blue-900/70'
                                                : 'bg-green-100 border-green-300 text-green-700 hover:bg-green-200 dark:bg-green-900/40 dark:border-green-700 dark:text-green-300 dark:hover:bg-green-900/70'
                                            )
                                        )
                                    }}"
                            >
                                {{ $alert['link_label'] }}
                                <svg class="w-3 h-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M3 10a.75.75 0 01.75-.75h10.638L10.23 5.29a.75.75 0 111.04-1.08l5.5 5.25a.75.75 0 010 1.08l-5.5 5.25a.75.75 0 11-1.04-1.08l4.158-3.96H3.75A.75.75 0 013 10z" clip-rule="evenodd" />
                                </svg>
                            </a>
                        @endif
                        @if($alert['type'] === 'danger')
                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-[10px] font-bold bg-red-100 text-red-700 dark:bg-red-900/60 dark:text-red-300 uppercase tracking-wider">
                                Urgente
                            </span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- GLOBAL KPI CARDS --}}
    @php $stats = $this->todayStats; @endphp
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        {{-- Chequeos Hoy --}}
        <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-800 p-5">
            <div class="flex items-center gap-3">
                <div class="p-3 rounded-lg bg-green-50 dark:bg-green-900/30">
                    <x-heroicon-o-clipboard-document-check class="w-6 h-6 text-green-600 dark:text-green-400" />
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['chequeos'] }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Chequeos Hoy</p>
                </div>
            </div>
        </div>

        {{-- Recorridos Hoy --}}
        <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-800 p-5">
            <div class="flex items-center gap-3">
                <div class="p-3 rounded-lg bg-purple-50 dark:bg-purple-900/30">
                    <x-heroicon-o-clipboard-document-list class="w-6 h-6 text-purple-600 dark:text-purple-400" />
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['recorridos'] }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Recorridos Hoy</p>
                </div>
            </div>
        </div>

        {{-- Reportes Hoy --}}
        <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-800 p-5">
            <div class="flex items-center gap-3">
                <div class="p-3 rounded-lg {{ $stats['reportes_pendientes'] > 0 ? 'bg-red-50 dark:bg-red-900/30' : 'bg-amber-50 dark:bg-amber-900/30' }}">
                    <x-heroicon-o-exclamation-triangle class="w-6 h-6 {{ $stats['reportes_pendientes'] > 0 ? 'text-red-600 dark:text-red-400' : 'text-amber-600 dark:text-amber-400' }}" />
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['reportes'] }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Reportes Hoy
                        @if($stats['reportes_pendientes'] > 0)
                            <span class="text-red-500 font-semibold">({{ $stats['reportes_pendientes'] }} pendientes)</span>
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- EQUIPOS BY AREA --}}
    @foreach($this->equiposByArea as $areaGroup)
        <div class="space-y-3">
            {{-- Area Header --}}
            <div class="flex items-center gap-3">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ $areaGroup['area'] }}</h3>
                <span class="text-xs font-medium text-gray-500 bg-gray-100 dark:bg-gray-800 dark:text-gray-400 px-2 py-1 rounded-full">
                    {{ count($areaGroup['equipos']) }} equipos
                </span>
            </div>

            {{-- Equipos Grid --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                @foreach($areaGroup['equipos'] as $equipo)
                    <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-800 overflow-hidden hover:shadow-md transition-shadow duration-200">
                        {{-- Equipo Image / Placeholder --}}
                        <div class="h-32 bg-gray-100 dark:bg-gray-800 relative overflow-hidden">
                            @if($equipo['foto'])
                                <img
                                    src="{{ Storage::url($equipo['foto']) }}"
                                    alt="{{ $equipo['nombre'] }}"
                                    class="w-full h-full object-cover"
                                />
                            @else
                                <div class="w-full h-full flex items-center justify-center">
                                    <x-heroicon-o-cog-6-tooth class="w-12 h-12 text-gray-300 dark:text-gray-600" />
                                </div>
                            @endif

                            {{-- Status Badge --}}
                            <div class="absolute top-2 right-2">
                                @if($equipo['tiene_chequeo_hoy'])
                                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300">
                                        <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                                        Chequeado
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                        <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>
                                        Sin chequeo
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{-- Equipo Info --}}
                        <div class="p-4 space-y-3">
                            <div>
                                <h4 class="font-semibold text-gray-900 dark:text-white truncate" title="{{ $equipo['nombre'] }}">
                                    {{ $equipo['nombre'] }}
                                </h4>
                                <div class="flex items-center gap-2 mt-1">
                                    @if($equipo['tag'])
                                        <span class="text-xs text-gray-500 dark:text-gray-400 font-mono bg-gray-50 dark:bg-gray-800 px-1.5 py-0.5 rounded">
                                            {{ $equipo['tag'] }}
                                        </span>
                                    @endif
                                    @if($equipo['capacidad'])
                                        <span class="text-xs text-gray-400 dark:text-gray-500">
                                            {{ $equipo['capacidad'] }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            {{-- Today's Metrics --}}
                            <div class="grid grid-cols-3 gap-2 pt-2 border-t border-gray-100 dark:border-gray-800">
                                {{-- Chequeos --}}
                                <div class="text-center">
                                    <p class="text-lg font-bold {{ $equipo['chequeos_hoy'] > 0 ? 'text-green-600 dark:text-green-400' : 'text-gray-400' }}">
                                        {{ $equipo['chequeos_hoy'] }}
                                    </p>
                                    <p class="text-[10px] text-gray-500 dark:text-gray-400 leading-tight">Chequeos</p>
                                </div>

                                {{-- Reportes --}}
                                <div class="text-center">
                                    <p class="text-lg font-bold {{ $equipo['reportes_hoy'] > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-gray-400' }}">
                                        {{ $equipo['reportes_hoy'] }}
                                    </p>
                                    <p class="text-[10px] text-gray-500 dark:text-gray-400 leading-tight">Reportes</p>
                                </div>

                                {{-- Pendientes --}}
                                <div class="text-center">
                                    <p class="text-lg font-bold {{ $equipo['reportes_pendientes'] > 0 ? 'text-red-600 dark:text-red-400' : 'text-gray-400' }}">
                                        {{ $equipo['reportes_pendientes'] }}
                                    </p>
                                    <p class="text-[10px] text-gray-500 dark:text-gray-400 leading-tight">Reportes pendientes</p>
                                </div>
                            </div>

                            {{-- Last check time --}}
                            @if($equipo['ultimo_chequeo'])
                                <div class="text-xs flex items-center gap-1.5 {{ $equipo['ultimo_chequeo_viejo'] ? 'text-red-500 dark:text-red-400 font-medium' : 'text-gray-400 dark:text-gray-500' }}">
                                    <x-heroicon-m-clock class="w-3 h-3 shrink-0" />
                                    Último chequeo: {{ $equipo['ultimo_chequeo'] }}
                                    @if($equipo['ultimo_chequeo_viejo'])
                                        <span class="inline-flex items-center rounded-full bg-red-100 dark:bg-red-900/40 px-1.5 py-0.5 text-[10px] font-semibold text-red-600 dark:text-red-400 uppercase tracking-wide">
                                            Vencido
                                        </span>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach

    {{-- Empty State --}}
    @if(empty($this->equiposByArea))
        <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-800 p-12 text-center">
            <x-heroicon-o-cog-6-tooth class="w-12 h-12 text-gray-300 dark:text-gray-600 mx-auto mb-4" />
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">No hay equipos registrados</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Comienza agregando equipos al sistema.</p>
        </div>
    @endif

</x-filament-panels::page>
