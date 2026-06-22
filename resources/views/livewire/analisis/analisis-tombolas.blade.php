<div class="space-y-6">

    {{-- ── FILTERS ─────────────────────────────────────────────────────────── --}}
    <div class="bg-white dark:bg-gray-900 p-5 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm">
        <div class="flex flex-wrap items-end gap-4">
            {{-- Equipo --}}
            <div class="flex-1 min-w-[180px]">
                <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1.5">Tombola</label>
                <select wire:model.live="equipoId"
                        class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Todas</option>
                    @foreach($this->tombolas as $t)
                        <option value="{{ $t->id }}">{{ $t->tag }} — {{ $t->nombre }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Centro de Costo --}}
            <div class="flex-1 min-w-[160px]">
                <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1.5">Centro de costo</label>
                <select wire:model.live="centroCostoId"
                        class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Todos</option>
                    @foreach($this->centrosCosto as $cc)
                        <option value="{{ $cc->id }}">{{ $cc->nombre }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Clear --}}
            @if($equipoId || $centroCostoId)
                <button wire:click="clearFilters"
                        class="px-3 py-2 text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 underline underline-offset-2 transition-colors">
                    Limpiar filtros
                </button>
            @endif
        </div>
    </div>

    {{-- ── KPI CARDS ────────────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">

        {{-- Total --}}
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm p-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-500">Total cargas</p>
            <p class="mt-1 text-4xl font-black text-gray-900 dark:text-white">{{ number_format($this->kpis['total']) }}</p>
            <p class="mt-1 text-xs text-gray-400">en {{ $this->kpis['days'] }} {{ Str::plural('día', $this->kpis['days']) }}</p>
        </div>

        {{-- Avg / day --}}
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm p-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-500">Promedio / día</p>
            <p class="mt-1 text-4xl font-black text-blue-600 dark:text-blue-400">{{ $this->kpis['avg_per_day'] }}</p>
            <p class="mt-1 text-xs text-gray-400">cargas por día</p>
        </div>

        {{-- Top equipo --}}
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm p-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-500">Tombola + activa</p>
            <p class="mt-1 text-2xl font-black text-emerald-600 dark:text-emerald-400 truncate">{{ $this->kpis['top_equipo'] }}</p>
            <p class="mt-1 text-xs text-gray-400">más cargas en el período</p>
        </div>

        {{-- Top centro de costo --}}
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm p-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-500">Centro + activo</p>
            <p class="mt-1 text-2xl font-black text-violet-600 dark:text-violet-400 truncate">{{ $this->kpis['top_centro_costo'] }}</p>
            <p class="mt-1 text-xs text-gray-400">mayor volumen registrado</p>
        </div>
    </div>

    {{-- ── CHARTS ───────────────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Daily trend (area) --}}
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm p-5">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4">Cargas por día</h3>
            <div
                x-data="tombolasDailyChart"
                x-init="initChart({
                    data: @js($this->dailyTrend['data']),
                    labels: @js($this->dailyTrend['labels'])
                })"
                @chart-data-updated.window="updateChart({
                    data: @js($this->dailyTrend['data']),
                    labels: @js($this->dailyTrend['labels'])
                })"
                class="h-64 w-full"
                wire:ignore
            ></div>
        </div>

        {{-- Cargas por tombola (horizontal bar) --}}
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm p-5">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4">Cargas por tombola</h3>
            <div
                x-data="tombolasEquipoChart"
                x-init="initChart({
                    data: @js($this->byEquipo->pluck('total')->values()),
                    labels: @js($this->byEquipo->pluck('tag')->values())
                })"
                @chart-data-updated.window="updateChart({
                    data: @js($this->byEquipo->pluck('total')->values()),
                    labels: @js($this->byEquipo->pluck('tag')->values())
                })"
                class="h-64 w-full"
                wire:ignore
            ></div>
        </div>
    </div>

    {{-- ── HORAS DE TRABAJO ────────────────────────────────────────────────── --}}
    @php
        $horasPorEquipo = $this->horasPorEquipo;
        $horasPorDia    = $this->horasPorDiaTurno;
        $turnosCols     = $horasPorDia['turnos'] ?? [];
        $diasRows       = $horasPorDia['days'] ?? [];
    @endphp

    @if(count($horasPorEquipo) || count($turnosCols))
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Left: avg hours per equipo --}}
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-800">
                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wide">
                    Horas de trabajo de tómbolas
                </h3>
                <p class="text-xs text-gray-400 mt-0.5">Promedio por sesión · {{ $this->startDate }} → {{ $this->endDate }}</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-800 text-left">
                            <th class="px-4 py-2.5 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Equipo</th>
                            <th class="px-4 py-2.5 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wide text-right">Promedio hrs</th>
                            <th class="px-4 py-2.5 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wide text-right">Total hrs</th>
                            <th class="px-4 py-2.5 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wide text-right">Sesiones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($horasPorEquipo as $row)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                                <td class="px-4 py-2.5 font-mono text-xs font-bold text-gray-700 dark:text-gray-200">{{ $row['tag'] }}</td>
                                <td class="px-4 py-2.5 text-right font-semibold text-blue-600 dark:text-blue-400">
                                    {{ $row['avg_horas'] > 0 ? $row['avg_horas'] : '—' }}
                                </td>
                                <td class="px-4 py-2.5 text-right text-gray-600 dark:text-gray-300">
                                    {{ $row['total_horas'] > 0 ? $row['total_horas'] : '—' }}
                                </td>
                                <td class="px-4 py-2.5 text-right text-gray-400 text-xs">{{ $row['sesiones'] }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-4 py-6 text-center text-sm text-gray-400">Sin registros en este período</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Right: daily avg hours per turno --}}
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-800">
                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wide">
                    Hrs promedio por día · turno
                </h3>
                <p class="text-xs text-gray-400 mt-0.5">Promedio de "Horas al final del turno" por día</p>
            </div>
            <div class="overflow-x-auto max-h-96 overflow-y-auto">
                <table class="w-full text-sm">
                    <thead class="sticky top-0 z-10">
                        <tr class="bg-gray-50 dark:bg-gray-800 text-left">
                            <th class="px-4 py-2.5 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Día</th>
                            @foreach($turnosCols as $t)
                                <th class="px-4 py-2.5 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wide text-right">
                                    {{ Str::upper(Str::substr($t['nombre'], 0, 3)) }}
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($diasRows as $row)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                                <td class="px-4 py-2 font-bold text-gray-700 dark:text-gray-200 text-center w-12">{{ $row['day'] }}</td>
                                @foreach($turnosCols as $t)
                                    @php $val = $row['turno_data'][$t['id']] ?? 0; @endphp
                                    <td class="px-4 py-2 text-right {{ $val > 0 ? 'text-gray-700 dark:text-gray-200 font-semibold' : 'text-gray-300 dark:text-gray-600' }}">
                                        {{ $val > 0 ? $val : '0' }}
                                    </td>
                                @endforeach
                            </tr>
                        @empty
                            <tr><td colspan="{{ count($turnosCols) + 1 }}" class="px-4 py-6 text-center text-sm text-gray-400">Sin registros</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
    @endif

    {{-- ── MATRIZ HORAS POR TURNO ─────────────────────────────────────────── --}}
    @php
        $matriz        = $this->matrizHoras;
        $matrizDays    = $matriz['days']        ?? [];
        $matrizRows    = $matriz['rows']        ?? [];
        $matrizSumDay  = $matriz['sum_per_day'] ?? [];
        $matrizAvgDay  = $matriz['avg_per_day'] ?? [];
    @endphp

    @if(count($matrizRows) && count($matrizDays))
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wide">
                    Horas por día y turno
                </h3>
                <p class="text-xs text-gray-400 mt-0.5">
                    <span class="text-blue-500 font-bold">T</span> Tintoreria &nbsp;·&nbsp;
                    <span class="text-emerald-500 font-bold">L</span> Lavanderia &nbsp;·&nbsp;
                    {{ $this->startDate }} → {{ $this->endDate }}
                </p>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="text-xs border-collapse">
                <thead>
                    {{-- Row 1: day numbers --}}
                    <tr class="bg-gray-100 dark:bg-gray-800">
                        <th class="px-3 py-2 text-left font-bold text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-700 sticky left-0 z-20 bg-gray-100 dark:bg-gray-800 min-w-[110px]"></th>
                        @foreach($matrizDays as $d)
                            <th colspan="2" class="px-0 py-2 text-center font-bold text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-700 min-w-[44px]">
                                {{ $d['day'] }}
                            </th>
                        @endforeach
                        <th class="px-2 py-2 text-center font-bold text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-700 bg-gray-200 dark:bg-gray-700 min-w-[44px]">Suma</th>
                        <th class="px-2 py-2 text-center font-bold text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-700 bg-gray-200 dark:bg-gray-700 min-w-[52px]">Promedio</th>
                    </tr>
                    {{-- Row 2: T / L sub-headers --}}
                    <tr class="bg-gray-50 dark:bg-gray-800/70">
                        <th class="px-3 py-1 text-left font-semibold text-gray-500 dark:text-gray-400 border border-gray-200 dark:border-gray-700 sticky left-0 z-20 bg-gray-50 dark:bg-gray-800/70">Equipo</th>
                        @foreach($matrizDays as $d)
                            <th class="px-0 py-1 text-center font-bold text-blue-600 dark:text-blue-400 border border-gray-200 dark:border-gray-700 w-[22px]">T</th>
                            <th class="px-0 py-1 text-center font-bold text-emerald-600 dark:text-emerald-400 border border-gray-200 dark:border-gray-700 w-[22px]">L</th>
                        @endforeach
                        <th class="px-1 py-1 text-center font-semibold text-gray-500 dark:text-gray-400 border border-gray-200 dark:border-gray-700 bg-gray-100 dark:bg-gray-800"></th>
                        <th class="px-1 py-1 text-center font-semibold text-gray-500 dark:text-gray-400 border border-gray-200 dark:border-gray-700 bg-gray-100 dark:bg-gray-800"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($matrizRows as $row)
                        <tr class="hover:bg-blue-50/30 dark:hover:bg-blue-900/10 transition-colors">
                            <td class="px-3 py-1.5 font-mono font-bold text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-700 sticky left-0 z-10 bg-white dark:bg-gray-900">{{ $row['tag'] }}</td>
                            @foreach($matrizDays as $d)
                                @php
                                    $tVal = $row['data'][$d['date']]['T'] ?? 0;
                                    $lVal = $row['data'][$d['date']]['L'] ?? 0;
                                @endphp
                                <td class="py-1.5 text-center border border-gray-200 dark:border-gray-700 w-[22px] {{ $tVal > 0 ? 'text-blue-700 dark:text-blue-300 font-semibold' : 'text-gray-300 dark:text-gray-600' }}">
                                    {{ $tVal > 0 ? (int)$tVal : 0 }}
                                </td>
                                <td class="py-1.5 text-center border border-gray-200 dark:border-gray-700 w-[22px] {{ $lVal > 0 ? 'text-emerald-700 dark:text-emerald-300 font-semibold' : 'text-gray-300 dark:text-gray-600' }}">
                                    {{ $lVal > 0 ? (int)$lVal : 0 }}
                                </td>
                            @endforeach
                            <td class="px-2 py-1.5 text-center font-black text-gray-900 dark:text-white border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/40">{{ (int)$row['suma'] }}</td>
                            <td class="px-2 py-1.5 text-center font-semibold text-blue-600 dark:text-blue-400 border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/40">{{ $row['promedio'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    {{-- Suma row --}}
                    <tr class="bg-gray-100 dark:bg-gray-800 font-bold">
                        <td class="px-3 py-2 font-bold text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-700 sticky left-0 z-10 bg-gray-100 dark:bg-gray-800">Suma</td>
                        @foreach($matrizDays as $d)
                            @php
                                $sT = $matrizSumDay[$d['date']]['T'] ?? 0;
                                $sL = $matrizSumDay[$d['date']]['L'] ?? 0;
                            @endphp
                            <td class="py-2 text-center border border-gray-200 dark:border-gray-700 {{ $sT > 0 ? 'text-blue-700 dark:text-blue-300' : 'text-gray-400 dark:text-gray-600' }}">{{ (int)$sT }}</td>
                            <td class="py-2 text-center border border-gray-200 dark:border-gray-700 {{ $sL > 0 ? 'text-emerald-700 dark:text-emerald-300' : 'text-gray-400 dark:text-gray-600' }}">{{ (int)$sL }}</td>
                        @endforeach
                        <td class="px-2 py-2 text-center font-black text-gray-900 dark:text-white border border-gray-200 dark:border-gray-700 bg-gray-200 dark:bg-gray-700">{{ (int)$matriz['suma'] }}</td>
                        <td class="px-2 py-2 text-center text-gray-400 dark:text-gray-500 border border-gray-200 dark:border-gray-700 bg-gray-200 dark:bg-gray-700">—</td>
                    </tr>
                    {{-- Promedio row --}}
                    <tr class="bg-gray-50 dark:bg-gray-800/60">
                        <td class="px-3 py-2 font-bold text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-700 sticky left-0 z-10 bg-gray-50 dark:bg-gray-800/60">Promedio</td>
                        @foreach($matrizDays as $d)
                            @php
                                $aT = $matrizAvgDay[$d['date']]['T'] ?? 0;
                                $aL = $matrizAvgDay[$d['date']]['L'] ?? 0;
                            @endphp
                            <td class="py-2 text-center border border-gray-200 dark:border-gray-700 {{ $aT > 0 ? 'text-blue-600 dark:text-blue-400' : 'text-gray-300 dark:text-gray-600' }}">{{ $aT > 0 ? $aT : 0 }}</td>
                            <td class="py-2 text-center border border-gray-200 dark:border-gray-700 {{ $aL > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-300 dark:text-gray-600' }}">{{ $aL > 0 ? $aL : 0 }}</td>
                        @endforeach
                        <td class="px-2 py-2 text-center text-gray-400 dark:text-gray-500 border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/40">—</td>
                        <td class="px-2 py-2 text-center font-bold text-blue-600 dark:text-blue-400 border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/40">{{ $matriz['promedio'] }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    @endif

    {{-- ── BREAKDOWNS ───────────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- By Equipo --}}
        <div class="lg:col-span-1 bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-800">
                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Por tombola</h3>
            </div>
            <div class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse($this->byEquipo as $row)
                    <div class="px-5 py-3">
                        <div class="flex justify-between items-center mb-1">
                            <div class="min-w-0">
                                <span class="text-xs font-bold text-gray-500 dark:text-gray-400 font-mono">{{ $row['tag'] }}</span>
                                <span class="text-xs text-gray-400 ml-1">{{ $row['nombre'] }}</span>
                            </div>
                            <div class="flex items-center gap-3 shrink-0 ml-2">
                                <span class="text-xs text-gray-400">{{ $row['avg'] }}/día</span>
                                <span class="text-sm font-bold text-gray-900 dark:text-white w-8 text-right">{{ $row['total'] }}</span>
                            </div>
                        </div>
                        <div class="h-1.5 rounded-full bg-gray-100 dark:bg-gray-800">
                            <div class="h-1.5 rounded-full bg-blue-500" style="width: {{ $row['pct'] }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="px-5 py-6 text-sm text-gray-400 text-center">Sin registros en este período</p>
                @endforelse
            </div>
        </div>

        {{-- By Centro de Costo --}}
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-800">
                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Por centro de costo</h3>
            </div>
            <div class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse($this->byCentroCosto as $row)
                    <div class="px-5 py-3">
                        <div class="flex justify-between items-center mb-1">
                            <span class="text-sm text-gray-700 dark:text-gray-300">{{ $row['nombre'] }}</span>
                            <div class="flex items-center gap-3 shrink-0 ml-2">
                                <span class="text-xs text-gray-400">{{ $row['avg'] }}/día</span>
                                <span class="text-sm font-bold text-gray-900 dark:text-white w-8 text-right">{{ $row['total'] }}</span>
                            </div>
                        </div>
                        <div class="h-1.5 rounded-full bg-gray-100 dark:bg-gray-800">
                            <div class="h-1.5 rounded-full bg-violet-500" style="width: {{ $row['pct'] }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="px-5 py-6 text-sm text-gray-400 text-center">Sin registros</p>
                @endforelse
            </div>
        </div>

        {{-- By Operator --}}
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-800">
                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Por operador</h3>
            </div>
            <div class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse($this->byUser as $row)
                    <div class="px-5 py-3">
                        <div class="flex justify-between items-center mb-1">
                            <span class="text-sm text-gray-700 dark:text-gray-300 truncate">{{ $row['nombre'] }}</span>
                            <span class="text-sm font-bold text-gray-900 dark:text-white shrink-0 ml-2 w-8 text-right">{{ $row['total'] }}</span>
                        </div>
                        <div class="h-1.5 rounded-full bg-gray-100 dark:bg-gray-800">
                            <div class="h-1.5 rounded-full bg-emerald-500" style="width: {{ $row['pct'] }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="px-5 py-6 text-sm text-gray-400 text-center">Sin registros</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
