<x-filament-panels::page class="!p-0 !max-w-full">

{{-- Poll every 15s for reports/recorridos. Reverb handles chequeos instantly. --}}
<div wire:poll.15s class="sr-only"></div>

<style>
/* ── Control Panel — scoped component styles using theme tokens ── */
.cp-page          { padding: 1.25rem 1.5rem; display: flex; flex-direction: column; gap: 1rem; }
@media (min-width: 768px) { .cp-page { padding: 1.5rem 2rem; } }

/* Header bar */
.cp-header        { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 0.75rem;
                    background: var(--bg-surface); border: 1px solid var(--border);
                    border-radius: var(--radius-md); padding: 0.875rem 1.25rem;
                    box-shadow: var(--shadow-xs); }
.cp-header-title  { font-weight: 700; font-size: 0.9375rem; color: var(--text-primary); letter-spacing: -0.01em; }
.cp-header-date   { font-size: 0.75rem; color: var(--text-muted); }
.cp-stat-chip     { display: flex; flex-direction: column; align-items: center; gap: 0.0625rem; }
.cp-stat-num      { font-size: 1rem; font-weight: 700; color: var(--text-primary); line-height: 1; }
.cp-stat-lbl      { font-size: 0.6875rem; color: var(--text-muted); white-space: nowrap; }
.cp-stat-divider  { width: 1px; height: 2rem; background: var(--border); }

/* Alert strips */
.cp-alert         { display: flex; align-items: center; gap: 0.75rem; border-radius: var(--radius-md);
                    padding: 0.625rem 1rem; font-size: 0.8125rem; border-width: 1px; border-style: solid;
                    transition: opacity 0.15s; text-decoration: none; }
.cp-alert:hover   { opacity: 0.85; }
.cp-alert-danger  { background: color-mix(in srgb, #ef4444 8%, var(--bg-surface)); border-color: color-mix(in srgb, #ef4444 30%, transparent); }
.cp-alert-warning { background: color-mix(in srgb, #f59e0b 8%, var(--bg-surface)); border-color: color-mix(in srgb, #f59e0b 30%, transparent); }
.cp-alert-title   { font-weight: 600; }
.cp-alert-desc    { opacity: 0.65; }

/* Breadcrumb */
.cp-breadcrumb    { display: flex; align-items: center; gap: 0.5rem; font-size: 0.8125rem; }
.cp-breadcrumb-btn{ display: flex; align-items: center; gap: 0.375rem; color: var(--border-focus);
                    font-weight: 500; background: none; border: none; cursor: pointer; padding: 0; }
.cp-breadcrumb-btn:hover { text-decoration: underline; }
.cp-breadcrumb-sep{ color: var(--text-muted); }
.cp-breadcrumb-cur{ font-weight: 600; color: var(--text-primary); }

/* Area overview grid */
.cp-area-grid     { display: grid; gap: 1rem; grid-template-columns: 1fr; }
@media (min-width: 768px)  { .cp-area-grid { grid-template-columns: repeat(2, 1fr); } }
@media (min-width: 1280px) { .cp-area-grid { grid-template-columns: repeat(3, 1fr); } }

/* Area card */
.cp-area-card     { background: var(--bg-surface); border: 1px solid var(--border);
                    border-radius: var(--radius-md); overflow: hidden; cursor: pointer;
                    box-shadow: var(--shadow-sm);
                    transition: box-shadow 0.2s, border-color 0.2s, transform 0.15s; }
.cp-area-card:hover { box-shadow: var(--shadow-md); transform: translateY(-1px); }
.cp-area-card.is-danger  { border-color: #ef4444; }
.cp-area-card.is-warning { border-color: #f59e0b; }
.cp-area-card.is-ok      { border-color: #22c55e; }

.cp-area-header   { display: flex; align-items: center; justify-content: space-between;
                    padding: 0.875rem 1rem; border-bottom: 1px solid var(--border); }
.cp-area-header.is-danger  { background: color-mix(in srgb, #ef4444 8%, var(--bg-surface)); }
.cp-area-header.is-warning { background: color-mix(in srgb, #f59e0b 8%, var(--bg-surface)); }
.cp-area-header.is-ok      { background: color-mix(in srgb, #22c55e 8%, var(--bg-surface)); }

.cp-area-name     { font-weight: 700; font-size: 0.9375rem; color: var(--text-primary); }
.cp-area-pill     { font-size: 0.6875rem; font-weight: 600; padding: 0.25rem 0.625rem;
                    border-radius: 9999px; white-space: nowrap; }
.cp-area-pill.is-danger  { background: color-mix(in srgb, #ef4444 15%, transparent); color: #ef4444; }
.cp-area-pill.is-warning { background: color-mix(in srgb, #f59e0b 15%, transparent); color: #f59e0b; }
.cp-area-pill.is-ok      { background: color-mix(in srgb, #22c55e 15%, transparent); color: #22c55e; }

.cp-area-stats    { display: grid; grid-template-columns: repeat(3, 1fr);
                    border-bottom: 1px solid var(--border); }
.cp-area-stat     { padding: 0.875rem 0.5rem; text-align: center; }
.cp-area-stat + .cp-area-stat { border-left: 1px solid var(--border); }
.cp-area-stat-val { font-size: 1.5rem; font-weight: 800; line-height: 1; color: var(--text-primary); }
.cp-area-stat-lbl { font-size: 0.6875rem; color: var(--text-muted); margin-top: 0.25rem; }

.cp-area-dots     { padding: 0.875rem 1rem; }
.cp-area-dots-lbl { font-size: 0.625rem; text-transform: uppercase; letter-spacing: 0.08em;
                    color: var(--text-muted); margin-bottom: 0.5rem; }
.cp-area-dots-grid{ display: flex; flex-wrap: wrap; gap: 0.375rem; }

.cp-area-footer   { padding: 0.5rem 1rem; display: flex; align-items: center; justify-content: flex-end; gap: 0.25rem;
                    font-size: 0.6875rem; color: var(--text-muted); border-top: 1px solid var(--border);
                    transition: color 0.15s; }
.cp-area-card:hover .cp-area-footer { color: var(--border-focus); }

/* Equipment drill-down grid */
.cp-eq-grid { display: grid; gap: 0.75rem;
              grid-template-columns: repeat(2, 1fr); }
@media (min-width: 640px)  { .cp-eq-grid { grid-template-columns: repeat(3, 1fr); } }
@media (min-width: 1024px) { .cp-eq-grid { grid-template-columns: repeat(4, 1fr); } }
@media (min-width: 1280px) { .cp-eq-grid { grid-template-columns: repeat(5, 1fr); } }
@media (min-width: 1536px) { .cp-eq-grid { grid-template-columns: repeat(6, 1fr); } }

/* Equipment card */
.cp-eq-card       { background: var(--bg-surface); border: 1px solid var(--border);
                    border-radius: var(--radius-md); overflow: hidden; cursor: pointer;
                    transition: box-shadow 0.2s, transform 0.15s; }
.cp-eq-card:hover { box-shadow: var(--shadow-md); transform: translateY(-1px); }
.cp-eq-card.is-danger  { border-color: #ef4444; }
.cp-eq-card.is-warning { border-color: #f59e0b; }
.cp-eq-card.is-ok      { border-color: #22c55e; }

.cp-eq-thumb      { position: relative; height: 7rem; background: var(--bg-subtle); overflow: hidden; }
.cp-eq-thumb img  { width: 100%; height: 100%; object-fit: cover;
                    transition: transform 0.3s; }
.cp-eq-card:hover .cp-eq-thumb img { transform: scale(1.05); }
.cp-eq-placeholder{ width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; }

.cp-eq-body       { padding: 0.625rem 0.75rem; }
.cp-eq-name       { font-weight: 600; font-size: 0.8125rem; color: var(--text-primary);
                    white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.cp-eq-tag        { font-family: 'ui-monospace', monospace; font-size: 0.6875rem;
                    color: var(--text-muted); margin-top: 0.125rem; }
.cp-eq-badge      { display: inline-flex; align-items: center; gap: 0.3125rem;
                    font-size: 0.6875rem; font-weight: 600;
                    padding: 0.25rem 0.5rem; border-radius: var(--radius-sm); margin-top: 0.5rem; }
.cp-eq-badge.is-danger  { background: color-mix(in srgb, #ef4444 15%, var(--bg-subtle)); color: #ef4444; }
.cp-eq-badge.is-warning { background: color-mix(in srgb, #f59e0b 15%, var(--bg-subtle)); color: #f59e0b; }
.cp-eq-badge.is-ok      { background: color-mix(in srgb, #22c55e 15%, var(--bg-subtle)); color: #22c55e; }

/* Detail modal */
.cp-modal-backdrop{ position: fixed; inset: 0; background: var(--bg-overlay);
                    backdrop-filter: blur(6px); z-index: 200;
                    display: flex; align-items: center; justify-content: center; padding: 1rem; }
.cp-modal         { background: var(--modal-bg); border: 1px solid var(--modal-border);
                    border-radius: var(--radius-lg); box-shadow: var(--shadow-xl);
                    width: 100%; max-width: 26rem; overflow: hidden; }
.cp-modal-photo   { position: relative; height: 11rem; background: var(--bg-subtle); }
.cp-modal-photo img { width: 100%; height: 100%; object-fit: cover; }
.cp-modal-close   { position: absolute; top: 0.75rem; right: 0.75rem;
                    background: rgba(0,0,0,0.45); color: #fff; border: none; cursor: pointer;
                    border-radius: 9999px; padding: 0.375rem;
                    display: flex; align-items: center; justify-content: center;
                    transition: background 0.15s; }
.cp-modal-close:hover { background: rgba(0,0,0,0.65); }

.cp-modal-body    { padding: 1.25rem; display: flex; flex-direction: column; gap: 1rem; }
.cp-modal-title   { font-size: 1.125rem; font-weight: 800; color: var(--text-primary); letter-spacing: -0.01em; }
.cp-modal-meta    { display: flex; align-items: center; gap: 0.5rem; margin-top: 0.25rem; }
.cp-modal-code    { font-family: 'ui-monospace', monospace; font-size: 0.6875rem;
                    background: var(--bg-subtle); color: var(--text-secondary);
                    padding: 0.1875rem 0.5rem; border-radius: var(--radius-sm); }
.cp-modal-area    { font-size: 0.75rem; color: var(--text-muted); }

.cp-modal-stats   { display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.5rem; }
.cp-modal-stat    { background: var(--bg-subtle); border-radius: var(--radius-sm);
                    padding: 0.75rem 0.5rem; text-align: center; }
.cp-modal-stat-val{ font-size: 1.25rem; font-weight: 800; line-height: 1; color: var(--text-primary); }
.cp-modal-stat-lbl{ font-size: 0.6875rem; color: var(--text-muted); margin-top: 0.25rem; }

.cp-modal-row     { display: flex; align-items: center; justify-content: space-between;
                    padding: 0.625rem 0; border-bottom: 1px solid var(--border);
                    font-size: 0.8125rem; }
.cp-modal-row-lbl { color: var(--text-muted); }
.cp-modal-row-val { font-weight: 600; color: var(--text-primary); }

.cp-warning-box   { display: flex; align-items: center; gap: 0.5rem; font-size: 0.75rem;
                    background: color-mix(in srgb, #f59e0b 10%, var(--bg-subtle));
                    color: #f59e0b; border-radius: var(--radius-sm); padding: 0.625rem 0.75rem; }

/* Legend */
.cp-legend        { display: flex; flex-wrap: wrap; align-items: center; gap: 1.25rem;
                    padding-top: 0.25rem; }
.cp-legend-item   { display: flex; align-items: center; gap: 0.5rem;
                    font-size: 0.75rem; color: var(--text-muted); }
.cp-legend-dot    { width: 0.625rem; height: 0.625rem; border-radius: 0.125rem; flex-shrink: 0; }

/* Status dot */
.cp-dot           { position: relative; display: inline-flex; }
.cp-dot span      { display: inline-flex; border-radius: 9999px; }

/* Badge counter (absolute overlay) */
.cp-count-badge   { position: absolute; top: 0.375rem; left: 0.375rem;
                    background: #ef4444; color: #fff; font-size: 0.625rem; font-weight: 800;
                    padding: 0.125rem 0.375rem; border-radius: 9999px; line-height: 1; }
.cp-status-overlay{ position: absolute; bottom: 0.625rem; left: 0.625rem; }
.cp-status-pip    { position: absolute; top: 0.5rem; right: 0.5rem; }
</style>

<div x-data="{ selectedAreaName: null, selectedEquipo: null }" class="cp-page">

    {{-- ─── HEADER BAR ─────────────────────────────────────────────────────── --}}
    <div class="cp-header">
        <div class="flex items-center gap-3">
            <span class="relative flex h-2 w-2 flex-shrink-0">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                <span class="relative inline-flex h-2 w-2 rounded-full bg-green-500"></span>
            </span>
            <div>
                <span class="cp-header-title">Panel de Control</span>
                <span class="cp-header-date ml-2">{{ now()->format('d/m/Y · H:i') }}</span>
            </div>
        </div>

        <div class="flex items-center gap-4">
            <div class="cp-stat-chip">
                <span class="cp-stat-num">{{ $this->todayStats['chequeos'] }}</span>
                <span class="cp-stat-lbl">chequeos</span>
            </div>
            <div class="cp-stat-divider"></div>
            <div class="cp-stat-chip">
                <span class="cp-stat-num">{{ $this->todayStats['recorridos'] }}</span>
                <span class="cp-stat-lbl">recorridos</span>
            </div>
            <div class="cp-stat-divider"></div>
            <div class="cp-stat-chip">
                <span class="cp-stat-num">{{ $this->todayStats['reportes'] }}</span>
                <span class="cp-stat-lbl">reportes</span>
            </div>
            <div class="cp-stat-divider"></div>
            @php $totalAlta = collect($this->equiposByArea)->flatMap(fn($a) => $a['equipos'])->sum('reportes_alta_prioridad'); @endphp
            @if($totalAlta > 0)
                <div class="cp-stat-chip">
                    <span class="cp-stat-num" style="color:#ef4444">{{ $totalAlta }}</span>
                    <span class="cp-stat-lbl" style="color:#ef4444">urgente{{ $totalAlta > 1 ? 's' : '' }}</span>
                </div>
            @else
                <div class="cp-stat-chip">
                    <span class="cp-stat-num" style="color:#22c55e">✓</span>
                    <span class="cp-stat-lbl" style="color:#22c55e">Sin alertas</span>
                </div>
            @endif
        </div>
    </div>

    {{-- ─── ALERT STRIPS ────────────────────────────────────────────────────── --}}
    @foreach($this->alerts as $alert)
        @if($alert['type'] === 'danger')
            <a href="{{ $alert['link'] }}" class="cp-alert cp-alert-danger">
                <span class="relative flex h-2.5 w-2.5 flex-shrink-0">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                    <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-red-500"></span>
                </span>
                <span class="cp-alert-title" style="color:#ef4444">{{ $alert['title'] }}</span>
                <span class="cp-alert-desc hidden sm:inline" style="color:#ef4444">— {{ $alert['description'] }}</span>
                <x-heroicon-o-arrow-right class="w-4 h-4 ml-auto flex-shrink-0" style="color:#ef4444" />
            </a>
        @elseif($alert['type'] === 'warning')
            <a href="{{ $alert['link'] }}" class="cp-alert cp-alert-warning">
                <x-heroicon-o-exclamation-triangle class="w-4 h-4 flex-shrink-0" style="color:#f59e0b" />
                <span class="cp-alert-title" style="color:#f59e0b">{{ $alert['title'] }}</span>
                <span class="cp-alert-desc hidden sm:inline" style="color:#f59e0b">— {{ $alert['description'] }}</span>
                <x-heroicon-o-arrow-right class="w-4 h-4 ml-auto flex-shrink-0" style="color:#f59e0b" />
            </a>
        @endif
    @endforeach

    {{-- ─── BREADCRUMB ──────────────────────────────────────────────────────── --}}
    <div x-show="selectedAreaName !== null" x-cloak class="cp-breadcrumb">
        <button @click="selectedAreaName = null; selectedEquipo = null" class="cp-breadcrumb-btn">
            <x-heroicon-o-building-office-2 class="w-4 h-4" />
            Empresa
        </button>
        <x-heroicon-o-chevron-right class="w-3.5 h-3.5 cp-breadcrumb-sep" />
        <span class="cp-breadcrumb-cur" x-text="selectedAreaName"></span>
    </div>

    {{-- ─── OVERVIEW: AREA ZONES ─────────────────────────────────────────────── --}}
    <div x-show="selectedAreaName === null"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         class="cp-area-grid">

        @foreach($this->equiposByArea as $areaData)
        @php
            $total      = count($areaData['equipos']);
            $conChequeo = collect($areaData['equipos'])->where('tiene_chequeo_hoy', true)->count();
            $pendientes = collect($areaData['equipos'])->sum('reportes_pendientes');
            $altaPrio   = collect($areaData['equipos'])->sum('reportes_alta_prioridad');

            $variant = match(true) {
                $altaPrio > 0                              => 'danger',
                $conChequeo < $total || $pendientes > 0   => 'warning',
                default                                    => 'ok',
            };
            $statusLabel = match($variant) {
                'danger'  => 'Alerta crítica',
                'warning' => 'Atención',
                'ok'      => 'Todo en orden',
            };
        @endphp

        <div @click="selectedAreaName = @js($areaData['area'])"
             class="cp-area-card is-{{ $variant }}">

            <div class="cp-area-header is-{{ $variant }}">
                <h3 class="cp-area-name">{{ $areaData['area'] }}</h3>
                <span class="cp-area-pill is-{{ $variant }}">{{ $statusLabel }}</span>
            </div>

            <div class="cp-area-stats">
                <div class="cp-area-stat">
                    <div class="cp-area-stat-val">{{ $total }}</div>
                    <div class="cp-area-stat-lbl">equipos</div>
                </div>
                <div class="cp-area-stat">
                    <div class="cp-area-stat-val" style="{{ $conChequeo === $total ? 'color:#22c55e' : 'color:#f59e0b' }}">
                        {{ $conChequeo }}/{{ $total }}
                    </div>
                    <div class="cp-area-stat-lbl">chequeos hoy</div>
                </div>
                <div class="cp-area-stat">
                    <div class="cp-area-stat-val" style="{{ $pendientes > 0 ? ($altaPrio > 0 ? 'color:#ef4444' : 'color:#f59e0b') : 'color:var(--text-muted)' }}">
                        {{ $pendientes }}
                    </div>
                    <div class="cp-area-stat-lbl">reportes pend.</div>
                </div>
            </div>

            <div class="cp-area-dots">
                <p class="cp-area-dots-lbl">Estado por equipo</p>
                <div class="cp-area-dots-grid">
                    @foreach($areaData['equipos'] as $eq)
                    @php
                        $dotStyle = match(true) {
                            $eq['reportes_alta_prioridad'] > 0                             => 'background:#ef4444',
                            $eq['tiene_chequeo_hoy'] && $eq['reportes_pendientes'] === 0   => 'background:#22c55e',
                            default                                                         => 'background:#f59e0b',
                        };
                        $dotExtra = $eq['reportes_alta_prioridad'] > 0 ? 'animate-pulse' : '';
                    @endphp
                    <div class="w-4 h-4 rounded-sm {{ $dotExtra }} transition-all duration-300"
                         style="{{ $dotStyle }}"
                         title="{{ $eq['nombre'] }} · {{ $eq['tag'] }}"></div>
                    @endforeach
                </div>
            </div>

            <div class="cp-area-footer">
                <span>Ver equipos</span>
                <x-heroicon-o-arrow-right class="w-3 h-3" />
            </div>
        </div>
        @endforeach
    </div>

    {{-- ─── AREA DRILL-DOWN ──────────────────────────────────────────────────── --}}
    @foreach($this->equiposByArea as $areaData)
    <div x-show="selectedAreaName === @js($areaData['area'])"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-x-2"
         x-transition:enter-end="opacity-100 translate-x-0"
         x-cloak
         class="cp-eq-grid">

        @foreach($areaData['equipos'] as $equipo)
        @php
            $eqVariant = match(true) {
                $equipo['reportes_alta_prioridad'] > 0                                         => 'danger',
                !$equipo['tiene_chequeo_hoy'] || $equipo['reportes_pendientes'] > 0            => 'warning',
                default                                                                         => 'ok',
            };
            $dotStyle = match($eqVariant) {
                'danger'  => 'background:#ef4444',
                'warning' => 'background:#f59e0b',
                'ok'      => 'background:#22c55e',
            };
            $eqLabel = match($eqVariant) {
                'danger'  => 'Alerta',
                'warning' => 'Atención',
                'ok'      => 'OK',
            };
        @endphp

        <div @click="selectedEquipo = @js($equipo)"
             class="cp-eq-card is-{{ $eqVariant }}">

            <div class="cp-eq-thumb">
                @if($equipo['foto_url'])
                    <img src="{{ $equipo['foto_url'] }}" alt="{{ $equipo['nombre'] }}" loading="lazy">
                @else
                    <div class="cp-eq-placeholder">
                        <x-heroicon-o-cog-6-tooth class="w-10 h-10" style="color:var(--text-muted)" />
                    </div>
                @endif

                {{-- Status pip --}}
                <div class="cp-status-pip">
                    <span class="relative flex h-3 w-3">
                        @if($equipo['reportes_alta_prioridad'] > 0)
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full" style="background:#ef4444;opacity:0.75"></span>
                        @endif
                        <span class="relative inline-flex h-3 w-3 rounded-full" style="{{ $dotStyle }}"></span>
                    </span>
                </div>

                @if($equipo['reportes_pendientes'] > 0)
                    <span class="cp-count-badge">{{ $equipo['reportes_pendientes'] }}</span>
                @endif
            </div>

            <div class="cp-eq-body">
                <div class="cp-eq-name">{{ $equipo['nombre'] }}</div>
                <div class="cp-eq-tag">{{ $equipo['tag'] }}</div>
                <div class="flex items-center justify-between mt-1.5">
                    <span class="cp-eq-badge is-{{ $eqVariant }}">
                        <span class="w-1.5 h-1.5 rounded-full" style="{{ $dotStyle }}"></span>
                        {{ $eqLabel }}
                    </span>
                    @if($equipo['ultimo_chequeo_viejo'])
                        <span style="font-size:0.6875rem;color:#f59e0b;font-weight:600">Vencido</span>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endforeach

    {{-- ─── LEGEND ──────────────────────────────────────────────────────────── --}}
    <div class="cp-legend">
        <span class="cp-legend-item">
            <span class="cp-legend-dot" style="background:#22c55e"></span>
            Todo en orden
        </span>
        <span class="cp-legend-item">
            <span class="cp-legend-dot" style="background:#f59e0b"></span>
            Sin chequeo / reportes pendientes
        </span>
        <span class="cp-legend-item">
            <span class="cp-legend-dot animate-pulse" style="background:#ef4444"></span>
            Reporte urgente
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
         class="cp-modal-backdrop">

        <div x-show="selectedEquipo !== null"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             class="cp-modal">

            {{-- Photo header --}}
            <div class="cp-modal-photo">
                <img x-show="selectedEquipo?.foto_url"
                     :src="selectedEquipo?.foto_url"
                     :alt="selectedEquipo?.nombre">
                <div x-show="!selectedEquipo?.foto_url"
                     class="cp-eq-placeholder" style="position:absolute;inset:0">
                    <x-heroicon-o-cog-6-tooth class="w-16 h-16" style="color:var(--text-muted)" />
                </div>

                <button @click="selectedEquipo = null" class="cp-modal-close">
                    <x-heroicon-o-x-mark class="w-4 h-4" />
                </button>

                {{-- Status pill --}}
                <div class="cp-status-overlay">
                    <span x-show="selectedEquipo?.reportes_alta_prioridad > 0"
                          style="display:inline-flex;align-items:center;gap:0.375rem;background:#ef4444;color:#fff;font-size:0.6875rem;font-weight:700;padding:0.25rem 0.625rem;border-radius:9999px">
                        <span class="w-1.5 h-1.5 rounded-full bg-white animate-pulse"></span>
                        Alerta crítica
                    </span>
                    <span x-show="selectedEquipo?.reportes_alta_prioridad === 0 && selectedEquipo?.tiene_chequeo_hoy && selectedEquipo?.reportes_pendientes === 0"
                          style="display:inline-flex;align-items:center;gap:0.375rem;background:#22c55e;color:#fff;font-size:0.6875rem;font-weight:700;padding:0.25rem 0.625rem;border-radius:9999px">
                        ✓ Todo en orden
                    </span>
                    <span x-show="selectedEquipo?.reportes_alta_prioridad === 0 && (!selectedEquipo?.tiene_chequeo_hoy || selectedEquipo?.reportes_pendientes > 0)"
                          style="display:inline-flex;align-items:center;gap:0.375rem;background:#f59e0b;color:#fff;font-size:0.6875rem;font-weight:700;padding:0.25rem 0.625rem;border-radius:9999px">
                        ⚠ Atención
                    </span>
                </div>
            </div>

            {{-- Content --}}
            <div class="cp-modal-body">
                <div>
                    <h2 class="cp-modal-title" x-text="selectedEquipo?.nombre"></h2>
                    <div class="cp-modal-meta">
                        <code class="cp-modal-code" x-text="selectedEquipo?.tag"></code>
                        <span class="cp-modal-area" x-text="selectedEquipo?.area"></span>
                    </div>
                </div>

                <div class="cp-modal-stats">
                    <div class="cp-modal-stat">
                        <div class="cp-modal-stat-val"
                             :style="selectedEquipo?.tiene_chequeo_hoy ? 'color:#22c55e' : 'color:var(--text-muted)'"
                             x-text="selectedEquipo?.tiene_chequeo_hoy ? '✓' : '—'"></div>
                        <div class="cp-modal-stat-lbl">Chequeo hoy</div>
                    </div>
                    <div class="cp-modal-stat">
                        <div class="cp-modal-stat-val"
                             :style="selectedEquipo?.reportes_pendientes > 0 ? 'color:#f59e0b' : 'color:var(--text-muted)'"
                             x-text="selectedEquipo?.reportes_pendientes"></div>
                        <div class="cp-modal-stat-lbl">Pend.</div>
                    </div>
                    <div class="cp-modal-stat">
                        <div class="cp-modal-stat-val"
                             :style="selectedEquipo?.reportes_alta_prioridad > 0 ? 'color:#ef4444' : 'color:var(--text-muted)'"
                             x-text="selectedEquipo?.reportes_alta_prioridad"></div>
                        <div class="cp-modal-stat-lbl">Alta prior.</div>
                    </div>
                </div>

                <div>
                    <div class="cp-modal-row">
                        <span class="cp-modal-row-lbl">Último chequeo</span>
                        <span class="cp-modal-row-val" x-text="selectedEquipo?.ultimo_chequeo ?? 'Sin registro'"></span>
                    </div>
                    <template x-if="selectedEquipo?.capacidad">
                        <div class="cp-modal-row">
                            <span class="cp-modal-row-lbl">Capacidad</span>
                            <span class="cp-modal-row-val" x-text="selectedEquipo?.capacidad"></span>
                        </div>
                    </template>
                    <template x-if="selectedEquipo?.ultimo_chequeo_viejo">
                        <div class="cp-warning-box" style="margin-top:0.5rem">
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
