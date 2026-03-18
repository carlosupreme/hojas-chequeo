<x-filament-panels::page class="!p-0 !max-w-full">

<div wire:poll.15s class="sr-only"></div>

<style>
/* ═══════════════════════════════════════════════════════════
   Control Panel — scoped styles using theme tokens
   ═══════════════════════════════════════════════════════════ */

.cp-page        { padding: 1rem 1.25rem; display: flex; flex-direction: column; gap: 0.75rem; }
@media (min-width: 768px) { .cp-page { padding: 1.25rem 1.75rem; } }

/* ── Header ── */
.cp-header      { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 0.75rem;
                  background: var(--bg-surface); border: 1px solid var(--border);
                  border-radius: var(--radius-md); padding: 0.75rem 1.25rem;
                  box-shadow: var(--shadow-xs); }
.cp-header-title{ font-weight: 700; font-size: 0.9375rem; color: var(--text-primary); letter-spacing: -0.01em; }
.cp-header-date { font-size: 0.75rem; color: var(--text-muted); }

.cp-stat-chip   { display: flex; flex-direction: column; align-items: center; gap: 0.0625rem; }
.cp-stat-num    { font-size: 1rem; font-weight: 700; color: var(--text-primary); line-height: 1; }
.cp-stat-lbl    { font-size: 0.6875rem; color: var(--text-muted); white-space: nowrap; }
.cp-stat-divider{ width: 1px; height: 2rem; background: var(--border); }

.cp-turno-select{ font-size: 0.8125rem; padding: 0.3125rem 0.75rem 0.3125rem 0.625rem;
                  border: 1px solid var(--border); border-radius: var(--radius-sm);
                  background: var(--bg-subtle); color: var(--text-primary);
                  cursor: pointer; outline: none; appearance: none;
                  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6' fill='none'%3E%3Cpath d='M1 1l4 4 4-4' stroke='%2394a3b8' stroke-width='1.5' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
                  background-repeat: no-repeat; background-position: right 0.5rem center; padding-right: 1.75rem; }
.cp-turno-select:focus { border-color: var(--border-focus); }

/* ── Alert strips ── */
.cp-alert       { display: flex; align-items: center; gap: 0.75rem; border-radius: var(--radius-md);
                  padding: 0.5rem 1rem; font-size: 0.8125rem; border-width: 1px; border-style: solid;
                  text-decoration: none; transition: opacity 0.15s; }
.cp-alert:hover { opacity: 0.85; }
.cp-alert-danger { background: color-mix(in srgb, #ef4444 8%, var(--bg-surface)); border-color: color-mix(in srgb, #ef4444 30%, transparent); }
.cp-alert-warning{ background: color-mix(in srgb, #f59e0b 8%, var(--bg-surface)); border-color: color-mix(in srgb, #f59e0b 30%, transparent); }

/* ── Main 2-column grid ── */
.cp-two-col     { display: grid; gap: 0.75rem; grid-template-columns: 1fr; align-items: start; }
@media (min-width: 1024px) { .cp-two-col { grid-template-columns: 2fr minmax(0, 21rem); } }

.cp-left        { display: flex; flex-direction: column; gap: 0.75rem; min-width: 0; }
.cp-right       { display: flex; flex-direction: column; gap: 0.75rem; }
@media (min-width: 1024px) {
    .cp-right   { position: sticky; top: 0.75rem; max-height: calc(100vh - 5rem); overflow: hidden; }
}

/* ── Breadcrumb ── */
.cp-breadcrumb  { display: flex; align-items: center; gap: 0.5rem; font-size: 0.8125rem; }
.cp-breadcrumb-btn{ display: flex; align-items: center; gap: 0.375rem; color: var(--border-focus);
                  font-weight: 500; background: none; border: none; cursor: pointer; padding: 0; }
.cp-breadcrumb-btn:hover { text-decoration: underline; }
.cp-breadcrumb-sep{ color: var(--text-muted); }
.cp-breadcrumb-cur{ font-weight: 600; color: var(--text-primary); }

/* ── Area overview grid ── */
.cp-area-grid   { display: grid; gap: 0.75rem; grid-template-columns: 1fr; }
@media (min-width: 640px)  { .cp-area-grid { grid-template-columns: repeat(2, 1fr); } }
@media (min-width: 1280px) { .cp-area-grid { grid-template-columns: repeat(3, 1fr); } }

/* ── Area card ── */
.cp-area-card   { background: var(--bg-surface); border: 1px solid var(--border);
                  border-radius: var(--radius-md); overflow: hidden; cursor: pointer;
                  box-shadow: var(--shadow-sm);
                  transition: box-shadow 0.2s, border-color 0.2s, transform 0.15s; }
.cp-area-card:hover       { box-shadow: var(--shadow-md); transform: translateY(-1px); }
.cp-area-card.is-danger   { border-color: #ef4444; }
.cp-area-card.is-warning  { border-color: #f59e0b; }
.cp-area-card.is-ok       { border-color: #22c55e; }

.cp-area-header { display: flex; align-items: center; justify-content: space-between;
                  padding: 0.75rem 1rem; border-bottom: 1px solid var(--border); }
.cp-area-header.is-danger  { background: color-mix(in srgb, #ef4444 8%, var(--bg-surface)); }
.cp-area-header.is-warning { background: color-mix(in srgb, #f59e0b 8%, var(--bg-surface)); }
.cp-area-header.is-ok      { background: color-mix(in srgb, #22c55e 8%, var(--bg-surface)); }

.cp-area-name   { font-weight: 700; font-size: 0.9375rem; color: var(--text-primary); }
.cp-area-pill   { font-size: 0.6875rem; font-weight: 600; padding: 0.25rem 0.625rem;
                  border-radius: 9999px; white-space: nowrap; }
.cp-area-pill.is-danger  { background: color-mix(in srgb, #ef4444 15%, transparent); color: #ef4444; }
.cp-area-pill.is-warning { background: color-mix(in srgb, #f59e0b 15%, transparent); color: #f59e0b; }
.cp-area-pill.is-ok      { background: color-mix(in srgb, #22c55e 15%, transparent); color: #22c55e; }

.cp-area-stats  { display: grid; grid-template-columns: repeat(3, 1fr); border-bottom: 1px solid var(--border); }
.cp-area-stat   { padding: 0.75rem 0.5rem; text-align: center; }
.cp-area-stat + .cp-area-stat { border-left: 1px solid var(--border); }
.cp-area-stat-val{ font-size: 1.375rem; font-weight: 800; line-height: 1; color: var(--text-primary); }
.cp-area-stat-lbl{ font-size: 0.6875rem; color: var(--text-muted); margin-top: 0.25rem; }

.cp-area-dots   { padding: 0.75rem 1rem; }
.cp-area-dots-lbl{ font-size: 0.625rem; text-transform: uppercase; letter-spacing: 0.08em;
                  color: var(--text-muted); margin-bottom: 0.5rem; }
.cp-area-dots-grid{ display: flex; flex-wrap: wrap; gap: 0.375rem; }

.cp-area-footer { padding: 0.5rem 1rem; display: flex; align-items: center; justify-content: flex-end; gap: 0.25rem;
                  font-size: 0.6875rem; color: var(--text-muted); border-top: 1px solid var(--border);
                  transition: color 0.15s; }
.cp-area-card:hover .cp-area-footer { color: var(--border-focus); }

/* ── Equipment grid ── */
.cp-eq-grid     { display: grid; gap: 0.625rem;
                  grid-template-columns: repeat(2, 1fr); }
@media (min-width: 640px)  { .cp-eq-grid { grid-template-columns: repeat(3, 1fr); } }
@media (min-width: 1024px) { .cp-eq-grid { grid-template-columns: repeat(4, 1fr); } }
@media (min-width: 1280px) { .cp-eq-grid { grid-template-columns: repeat(5, 1fr); } }

/* ── Equipment card ── */
.cp-eq-card     { background: var(--bg-surface); border: 1px solid var(--border);
                  border-radius: var(--radius-md); overflow: hidden; cursor: pointer;
                  transition: box-shadow 0.2s, transform 0.15s; }
.cp-eq-card:hover{ box-shadow: var(--shadow-md); transform: translateY(-1px); }
.cp-eq-card.is-danger  { border-color: #ef4444; }
.cp-eq-card.is-warning { border-color: #f59e0b; }
.cp-eq-card.is-ok      { border-color: #22c55e; }

.cp-eq-thumb    { position: relative; height: 6.5rem; background: var(--bg-subtle); overflow: hidden; }
.cp-eq-thumb img{ width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s; }
.cp-eq-card:hover .cp-eq-thumb img { transform: scale(1.05); }
.cp-eq-placeholder{ width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; }
.cp-status-pip  { position: absolute; top: 0.5rem; right: 0.5rem; }
.cp-count-badge { position: absolute; top: 0.375rem; left: 0.375rem;
                  background: #ef4444; color: #fff; font-size: 0.625rem; font-weight: 800;
                  padding: 0.125rem 0.375rem; border-radius: 9999px; line-height: 1; }

.cp-eq-body     { padding: 0.5rem 0.625rem 0.625rem; }
.cp-eq-name     { font-weight: 600; font-size: 0.8125rem; color: var(--text-primary);
                  white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.cp-eq-tag      { font-family: 'ui-monospace', monospace; font-size: 0.625rem; color: var(--text-muted); margin-top: 0.0625rem; }
.cp-eq-badge    { display: inline-flex; align-items: center; gap: 0.3125rem;
                  font-size: 0.625rem; font-weight: 600;
                  padding: 0.1875rem 0.4375rem; border-radius: var(--radius-sm); margin-top: 0.375rem; }
.cp-eq-badge.is-danger  { background: color-mix(in srgb, #ef4444 15%, var(--bg-subtle)); color: #ef4444; }
.cp-eq-badge.is-warning { background: color-mix(in srgb, #f59e0b 15%, var(--bg-subtle)); color: #f59e0b; }
.cp-eq-badge.is-ok      { background: color-mix(in srgb, #22c55e 15%, var(--bg-subtle)); color: #22c55e; }

/* ── Equipo chequeo status line ── */
.cp-eq-live     { display: flex; align-items: center; gap: 0.3125rem; font-size: 0.6875rem;
                  font-weight: 600; color: #ef4444; margin-top: 0.375rem; overflow: hidden; }
.cp-eq-live-op  { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.cp-eq-chk      { display: flex; align-items: center; gap: 0.3125rem; font-size: 0.6875rem;
                  color: var(--text-muted); margin-top: 0.375rem; overflow: hidden; }
.cp-eq-chk-op   { font-weight: 500; color: var(--text-secondary);
                  overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

/* ── Live section (right column) ── */
.cp-live-section{ background: var(--bg-surface);
                  border: 1px solid color-mix(in srgb, #ef4444 35%, transparent);
                  border-radius: var(--radius-md); overflow: hidden; }
.cp-live-header { display: flex; align-items: center; gap: 0.5rem; padding: 0.5rem 0.875rem;
                  border-bottom: 1px solid color-mix(in srgb, #ef4444 20%, transparent); }
.cp-live-title  { font-size: 0.6875rem; font-weight: 700; text-transform: uppercase;
                  letter-spacing: 0.07em; color: #ef4444; }
.cp-live-count  { margin-left: auto; font-size: 0.6875rem; color: var(--text-muted); }
.cp-live-item   { display: flex; align-items: center; gap: 0.625rem; padding: 0.5rem 0.875rem;
                  border-bottom: 1px solid var(--border); text-decoration: none;
                  transition: background 0.15s; }
.cp-live-item:hover { background: var(--bg-subtle); }
.cp-live-item:last-child { border-bottom: none; }
.cp-live-info   { flex: 1; min-width: 0; }
.cp-live-equipo { font-size: 0.8125rem; font-weight: 600; color: var(--text-primary);
                  white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.cp-live-meta   { font-size: 0.6875rem; color: var(--text-muted); }
.cp-live-elapsed{ font-size: 0.6875rem; font-weight: 600; color: #f59e0b; white-space: nowrap; flex-shrink: 0; }

/* ── Feed section (right column) ── */
.cp-feed-section{ background: var(--bg-surface); border: 1px solid var(--border);
                  border-radius: var(--radius-md); display: flex; flex-direction: column;
                  overflow: hidden; }
@media (min-width: 1024px) { .cp-feed-section { overflow-y: auto; max-height: 55vh; } }
.cp-feed-header { position: sticky; top: 0; z-index: 1; display: flex; align-items: center;
                  gap: 0.5rem; padding: 0.5rem 0.875rem; border-bottom: 1px solid var(--border);
                  background: var(--bg-surface); }
.cp-feed-title  { font-size: 0.6875rem; font-weight: 700; text-transform: uppercase;
                  letter-spacing: 0.07em; color: var(--text-secondary); }
.cp-feed-count  { margin-left: auto; font-size: 0.6875rem; color: var(--text-muted); }

.cp-feed-item   { display: flex; align-items: flex-start; gap: 0.5rem; padding: 0.5rem 0.875rem;
                  border-bottom: 1px solid var(--border); font-size: 0.8125rem; }
.cp-feed-item:last-child { border-bottom: none; }
.cp-feed-item.is-link { text-decoration: none; cursor: pointer; }
.cp-feed-item.is-link:hover { background: var(--bg-subtle); }

.cp-feed-time   { font-size: 0.625rem; color: var(--text-muted); white-space: nowrap;
                  padding-top: 0.25rem; min-width: 2.25rem; font-variant-numeric: tabular-nums; }
.cp-feed-icon   { width: 1.375rem; height: 1.375rem; border-radius: var(--radius-sm);
                  display: flex; align-items: center; justify-content: center; flex-shrink: 0; margin-top: 0.125rem; }
.cp-feed-icon.t-chequeo  { background: color-mix(in srgb, #22c55e 12%, var(--bg-subtle)); color: #22c55e; }
.cp-feed-icon.t-recorrido{ background: color-mix(in srgb, #3b82f6 12%, var(--bg-subtle)); color: #3b82f6; }
.cp-feed-icon.t-warn     { background: color-mix(in srgb, #f59e0b 12%, var(--bg-subtle)); color: #f59e0b; }
.cp-feed-icon.t-danger   { background: color-mix(in srgb, #ef4444 12%, var(--bg-subtle)); color: #ef4444; }

.cp-feed-body   { flex: 1; min-width: 0; }
.cp-feed-equipo { font-weight: 600; color: var(--text-primary);
                  white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.cp-feed-sub    { font-size: 0.625rem; color: var(--text-muted);
                  display: flex; gap: 0.3rem; align-items: center; flex-wrap: wrap; margin-top: 0.0625rem; }
.cp-feed-falla  { font-size: 0.625rem; color: var(--text-secondary);
                  white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-top: 0.0625rem; }
.cp-feed-tag    { font-family: ui-monospace, monospace; font-size: 0.5625rem; }
.cp-turno-tag   { display: inline-block; font-size: 0.5625rem; font-weight: 700;
                  text-transform: uppercase; letter-spacing: 0.03em;
                  background: var(--bg-subtle); color: var(--text-secondary);
                  border: 1px solid var(--border); border-radius: 0.25rem;
                  padding: 0.0625rem 0.3rem; line-height: 1.4; }

/* ── Compliance card ── */
.cp-compliance-card { background: var(--bg-surface); border: 1px solid var(--border);
                      border-radius: var(--radius-md); overflow: hidden; }
.cp-compliance-hdr  { display: flex; align-items: center; justify-content: space-between;
                      padding: 0.625rem 1rem; border-bottom: 1px solid var(--border); gap: 0.75rem; }
.cp-compliance-title{ font-size: 0.8125rem; font-weight: 700; color: var(--text-primary); }
.cp-compliance-cc   { font-size: 0.6875rem; color: var(--text-muted); }
.cp-compliance-body { padding: 0.75rem 1rem; display: flex; flex-direction: column; gap: 0.625rem; }

.cp-toggle-wrap { display: flex; align-items: center; gap: 0.5rem; flex-shrink: 0; }
.cp-toggle-opt  { font-size: 0.6875rem; color: var(--text-muted); white-space: nowrap; }
.cp-toggle-opt.is-active { font-weight: 700; color: var(--text-primary); }
.cp-toggle      { position: relative; display: inline-flex; width: 2.25rem; height: 1.125rem;
                  cursor: pointer; flex-shrink: 0; }
.cp-toggle input{ opacity: 0; width: 0; height: 0; position: absolute; }
.cp-toggle-track{ width: 100%; height: 100%; background: var(--border); border-radius: 9999px; transition: background 0.2s; }
.cp-toggle input:checked ~ .cp-toggle-track { background: var(--border-focus); }
.cp-toggle-thumb{ position: absolute; top: 0.125rem; left: 0.125rem; width: 0.875rem; height: 0.875rem;
                  background: #fff; border-radius: 9999px; transition: transform 0.2s;
                  box-shadow: 0 1px 2px rgba(0,0,0,0.25); pointer-events: none; }
.cp-toggle input:checked ~ .cp-toggle-thumb { transform: translateX(1.125rem); }

.cp-presets     { display: flex; gap: 0.375rem; }
.cp-preset-btn  { font-size: 0.6875rem; padding: 0.25rem 0.625rem; border-radius: var(--radius-sm);
                  border: 1px solid var(--border); background: var(--bg-subtle); color: var(--text-secondary);
                  cursor: pointer; transition: background 0.15s, border-color 0.15s; white-space: nowrap; }
.cp-preset-btn:hover    { background: var(--border); }
.cp-preset-btn.is-active{ background: var(--border-focus); border-color: var(--border-focus); color: #fff; }

.cp-comp-table  { width: 100%; border-collapse: collapse; font-size: 0.8125rem; }
.cp-comp-table th{ font-size: 0.625rem; text-transform: uppercase; letter-spacing: 0.05em;
                   color: var(--text-muted); padding: 0.25rem 0.5rem; text-align: left;
                   border-bottom: 1px solid var(--border); }
.cp-comp-table th:not(:first-child){ text-align: right; }
.cp-comp-table td{ padding: 0.5rem 0.5rem; border-bottom: 1px solid var(--border);
                   color: var(--text-primary); vertical-align: middle; }
.cp-comp-table td:not(:first-child){ text-align: right; }
.cp-comp-table tr:last-child td { border-bottom: none; }
.cp-comp-table tr.is-selected td { background: color-mix(in srgb, var(--border-focus) 6%, var(--bg-surface)); }
.cp-pct         { font-size: 0.75rem; font-weight: 700; padding: 0.125rem 0.4375rem; border-radius: var(--radius-sm); }
.cp-pct.is-ok      { background: color-mix(in srgb, #22c55e 15%, var(--bg-subtle)); color: #22c55e; }
.cp-pct.is-warning { background: color-mix(in srgb, #f59e0b 15%, var(--bg-subtle)); color: #f59e0b; }
.cp-pct.is-danger  { background: color-mix(in srgb, #ef4444 15%, var(--bg-subtle)); color: #ef4444; }

/* ── Detail modal ── */
.cp-modal-backdrop{ position: fixed; inset: 0; background: var(--bg-overlay);
                    backdrop-filter: blur(6px); z-index: 200;
                    display: flex; align-items: center; justify-content: center; padding: 1rem; }
.cp-modal         { background: var(--modal-bg); border: 1px solid var(--modal-border);
                    border-radius: var(--radius-lg); box-shadow: var(--shadow-xl);
                    width: 100%; max-width: 26rem; overflow: hidden; }
.cp-modal-photo   { position: relative; height: 10rem; background: var(--bg-subtle); }
.cp-modal-photo img{ width: 100%; height: 100%; object-fit: cover; }
.cp-modal-close   { position: absolute; top: 0.625rem; right: 0.625rem;
                    background: rgba(0,0,0,0.45); color: #fff; border: none; cursor: pointer;
                    border-radius: 9999px; padding: 0.3125rem;
                    display: flex; align-items: center; justify-content: center;
                    transition: background 0.15s; }
.cp-modal-close:hover { background: rgba(0,0,0,0.65); }
.cp-status-overlay{ position: absolute; bottom: 0.5rem; left: 0.625rem; }

.cp-modal-body    { padding: 1rem; display: flex; flex-direction: column; gap: 0.875rem; }
.cp-modal-title   { font-size: 1.0625rem; font-weight: 800; color: var(--text-primary); letter-spacing: -0.01em; }
.cp-modal-meta    { display: flex; align-items: center; gap: 0.5rem; margin-top: 0.1875rem; }
.cp-modal-code    { font-family: 'ui-monospace', monospace; font-size: 0.6875rem;
                    background: var(--bg-subtle); color: var(--text-secondary);
                    padding: 0.1875rem 0.4375rem; border-radius: var(--radius-sm); }
.cp-modal-area    { font-size: 0.75rem; color: var(--text-muted); }

.cp-modal-stats   { display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.375rem; }
.cp-modal-stat    { background: var(--bg-subtle); border-radius: var(--radius-sm);
                    padding: 0.625rem 0.375rem; text-align: center; }
.cp-modal-stat-val{ font-size: 1.125rem; font-weight: 800; line-height: 1; color: var(--text-primary); }
.cp-modal-stat-lbl{ font-size: 0.625rem; color: var(--text-muted); margin-top: 0.1875rem; }

.cp-modal-row     { display: flex; align-items: center; justify-content: space-between;
                    padding: 0.5rem 0; border-bottom: 1px solid var(--border); font-size: 0.8125rem; }
.cp-modal-row:last-child { border-bottom: none; }
.cp-modal-row-lbl { color: var(--text-muted); }
.cp-modal-row-val { font-weight: 600; color: var(--text-primary); text-align: right; }

/* ── Modal: live chequeo button ── */
.cp-modal-live-btn{ display: flex; align-items: center; gap: 0.625rem;
                    background: color-mix(in srgb, #ef4444 10%, var(--bg-subtle));
                    border: 1px solid color-mix(in srgb, #ef4444 30%, transparent);
                    border-radius: var(--radius-sm); padding: 0.625rem 0.75rem;
                    text-decoration: none; transition: background 0.15s; }
.cp-modal-live-btn:hover { background: color-mix(in srgb, #ef4444 18%, var(--bg-subtle)); }
.cp-modal-live-op { font-size: 0.8125rem; font-weight: 600; color: #ef4444; flex: 1; }
.cp-modal-live-time{ font-size: 0.6875rem; color: #f59e0b; }
.cp-modal-live-goto{ font-size: 0.75rem; font-weight: 700; color: var(--border-focus); white-space: nowrap; flex-shrink: 0; }

.cp-warning-box   { display: flex; align-items: center; gap: 0.5rem; font-size: 0.75rem;
                    background: color-mix(in srgb, #f59e0b 10%, var(--bg-subtle));
                    color: #f59e0b; border-radius: var(--radius-sm); padding: 0.5rem 0.75rem; }

/* ── Legend ── */
.cp-legend        { display: flex; flex-wrap: wrap; align-items: center; gap: 1rem; padding-top: 0.125rem; }
.cp-legend-item   { display: flex; align-items: center; gap: 0.4375rem;
                    font-size: 0.75rem; color: var(--text-muted); }
.cp-legend-dot    { width: 0.5625rem; height: 0.5625rem; border-radius: 0.125rem; flex-shrink: 0; }
</style>

{{-- ═══════════════════════════════════════════════════════════
     COMPONENT
     ═══════════════════════════════════════════════════════════ --}}
<div x-data="{ selectedAreaName: null, selectedEquipo: null }" class="cp-page">

    {{-- ─── HEADER ─────────────────────────────────────────────── --}}
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

        <div class="flex items-center gap-3 flex-wrap">
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
            @if($this->todayStats['reportes_pendientes'] > 0)
                <div class="cp-stat-chip">
                    <span class="cp-stat-num" style="color:#ef4444">{{ $this->todayStats['reportes_pendientes'] }}</span>
                    <span class="cp-stat-lbl" style="color:#ef4444">urgente{{ $this->todayStats['reportes_pendientes'] > 1 ? 's' : '' }}</span>
                </div>
            @else
                <div class="cp-stat-chip">
                    <span class="cp-stat-num" style="color:#22c55e">✓</span>
                    <span class="cp-stat-lbl" style="color:#22c55e">Sin alertas</span>
                </div>
            @endif
            <div class="cp-stat-divider"></div>
            <select wire:model.live="selectedTurnoId" class="cp-turno-select">
                <option value="">Todos los turnos</option>
                @foreach($this->turnos as $turno)
                    <option value="{{ $turno->id }}">{{ $turno->nombre }}</option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- ─── ALERTS ──────────────────────────────────────────────── --}}
    @foreach($this->alerts as $alert)
        @if($alert['type'] === 'danger')
            <a href="{{ $alert['link'] }}" class="cp-alert cp-alert-danger">
                <span class="relative flex h-2.5 w-2.5 flex-shrink-0">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                    <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-red-500"></span>
                </span>
                <span style="font-weight:600;color:#ef4444">{{ $alert['title'] }}</span>
                <span class="hidden sm:inline" style="opacity:0.7;color:#ef4444">— {{ $alert['description'] }}</span>
                <x-heroicon-o-arrow-right class="w-4 h-4 ml-auto flex-shrink-0" style="color:#ef4444" />
            </a>
        @else
            <a href="{{ $alert['link'] }}" class="cp-alert cp-alert-warning">
                <x-heroicon-o-exclamation-triangle class="w-4 h-4 flex-shrink-0" style="color:#f59e0b" />
                <span style="font-weight:600;color:#f59e0b">{{ $alert['title'] }}</span>
                <span class="hidden sm:inline" style="opacity:0.7;color:#f59e0b">— {{ $alert['description'] }}</span>
                <x-heroicon-o-arrow-right class="w-4 h-4 ml-auto flex-shrink-0" style="color:#f59e0b" />
            </a>
        @endif
    @endforeach

    {{-- ─── TWO-COLUMN MAIN LAYOUT ─────────────────────────────── --}}
    <div class="cp-two-col">

        {{-- ══ LEFT COLUMN ══ --}}
        <div class="cp-left">

            {{-- Breadcrumb --}}
            <div x-show="selectedAreaName !== null" x-cloak class="cp-breadcrumb">
                <button @click="selectedAreaName = null; selectedEquipo = null" class="cp-breadcrumb-btn">
                    <x-heroicon-o-building-office-2 class="w-4 h-4" />
                    Empresa
                </button>
                <x-heroicon-o-chevron-right class="w-3.5 h-3.5 cp-breadcrumb-sep" />
                <span class="cp-breadcrumb-cur" x-text="selectedAreaName"></span>
            </div>

            {{-- ── AREA OVERVIEW ── --}}
            <div x-show="selectedAreaName === null"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 class="cp-area-grid">

                @foreach($this->equiposByArea as $areaData)
                @php
                    $total       = count($areaData['equipos']);
                    $conChequeo  = collect($areaData['equipos'])->where('tiene_chequeo_hoy', true)->count();
                    $pendientes  = collect($areaData['equipos'])->sum('reportes_pendientes');
                    $altaPrio    = collect($areaData['equipos'])->sum('reportes_alta_prioridad');
                    $enProgreso  = collect($areaData['equipos'])->where('en_progreso', true)->count();

                    $variant = match(true) {
                        $altaPrio > 0                            => 'danger',
                        $conChequeo < $total || $pendientes > 0  => 'warning',
                        default                                   => 'ok',
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
                            <div class="cp-area-stat-val"
                                 style="{{ $pendientes > 0 ? ($altaPrio > 0 ? 'color:#ef4444' : 'color:#f59e0b') : 'color:var(--text-muted)' }}">
                                {{ $pendientes }}
                            </div>
                            <div class="cp-area-stat-lbl">rept. pend.</div>
                        </div>
                    </div>

                    {{-- Dot grid --}}
                    <div class="cp-area-dots">
                        <p class="cp-area-dots-lbl">Estado por equipo
                            @if($enProgreso > 0)
                                <span style="color:#ef4444;font-weight:700"> · {{ $enProgreso }} en curso</span>
                            @endif
                        </p>
                        <div class="cp-area-dots-grid">
                            @foreach($areaData['equipos'] as $eq)
                            @php
                                $dotStyle = match(true) {
                                    $eq['en_progreso']                                                 => 'background:#ef4444',
                                    $eq['reportes_alta_prioridad'] > 0                                => 'background:#ef4444',
                                    $eq['tiene_chequeo_hoy'] && $eq['reportes_pendientes'] === 0       => 'background:#22c55e',
                                    default                                                            => 'background:#f59e0b',
                                };
                                $dotClass = ($eq['en_progreso'] || $eq['reportes_alta_prioridad'] > 0) ? 'animate-pulse' : '';
                            @endphp
                            <div class="w-4 h-4 rounded-sm {{ $dotClass }} transition-all duration-300"
                                 style="{{ $dotStyle }}"
                                 title="{{ $eq['nombre'] }} · {{ $eq['tag'] }}{{ $eq['en_progreso'] ? ' (en curso)' : '' }}"></div>
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

            {{-- ── AREA DRILL-DOWN ── --}}
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
                        $equipo['reportes_alta_prioridad'] > 0                              => 'danger',
                        $equipo['en_progreso']                                              => 'warning',
                        !$equipo['tiene_chequeo_hoy'] || $equipo['reportes_pendientes'] > 0 => 'warning',
                        default                                                             => 'ok',
                    };
                    $dotColor = match($eqVariant) {
                        'danger'  => '#ef4444',
                        'warning' => '#f59e0b',
                        'ok'      => '#22c55e',
                    };
                    $eqLabel = match($eqVariant) {
                        'danger'  => 'Alerta',
                        'warning' => $equipo['en_progreso'] ? 'En curso' : 'Atención',
                        'ok'      => 'OK',
                    };
                @endphp

                <div @click="selectedEquipo = @js($equipo)" class="cp-eq-card is-{{ $eqVariant }}">

                    <div class="cp-eq-thumb">
                        @if($equipo['foto_url'])
                            <img src="{{ $equipo['foto_url'] }}" alt="{{ $equipo['nombre'] }}" loading="lazy">
                        @else
                            <div class="cp-eq-placeholder">
                                <x-heroicon-o-cog-6-tooth class="w-9 h-9" style="color:var(--text-muted)" />
                            </div>
                        @endif

                        <div class="cp-status-pip">
                            <span class="relative flex h-3 w-3">
                                @if($equipo['reportes_alta_prioridad'] > 0 || $equipo['en_progreso'])
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full"
                                          style="background:{{ $dotColor }};opacity:0.75"></span>
                                @endif
                                <span class="relative inline-flex h-3 w-3 rounded-full"
                                      style="background:{{ $dotColor }}"></span>
                            </span>
                        </div>

                        @if($equipo['reportes_pendientes'] > 0)
                            <span class="cp-count-badge">{{ $equipo['reportes_pendientes'] }}</span>
                        @endif
                    </div>

                    <div class="cp-eq-body">
                        <div class="cp-eq-name">{{ $equipo['nombre'] }}</div>
                        <div class="cp-eq-tag">{{ $equipo['tag'] }}</div>

                        {{-- Chequeo status line --}}
                        @if($equipo['en_progreso'])
                            <div class="cp-eq-live">
                                <span class="relative flex h-2 w-2 flex-shrink-0">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                                    <span class="relative inline-flex h-2 w-2 rounded-full bg-red-500"></span>
                                </span>
                                <span class="cp-eq-live-op">{{ $equipo['en_progreso_operador'] ?? 'En curso' }}</span>
                            </div>
                        @elseif($equipo['tiene_chequeo_hoy'] && $equipo['ultimo_chequeo_operador'])
                            <div class="cp-eq-chk">
                                <span style="color:#22c55e;flex-shrink:0">✓</span>
                                <span class="cp-eq-chk-op">{{ $equipo['ultimo_chequeo_operador'] }}</span>
                            </div>
                        @else
                            <div class="cp-eq-chk">
                                <span style="color:#f59e0b;flex-shrink:0">—</span>
                                <span>Sin chequeo hoy</span>
                            </div>
                        @endif

                        <div class="flex items-center gap-1.5 mt-1">
                            <span class="cp-eq-badge is-{{ $eqVariant }}">
                                <span class="w-1.5 h-1.5 rounded-full" style="background:{{ $dotColor }}"></span>
                                {{ $eqLabel }}
                            </span>
                            @if($equipo['ultimo_chequeo_viejo'] && !$equipo['en_progreso'])
                                <span style="font-size:0.6250rem;color:#f59e0b;font-weight:600">Vencido</span>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @endforeach

            {{-- ── COMPLIANCE CARD (visible when turno selected) ── --}}
            @if($this->selectedTurnoId && !empty($this->cumplimientoTurno))
            @php
                $firstRow = $this->cumplimientoTurno[0];
                $activePreset = match(true) {
                    $this->cumplimientoStart === now()->format('Y-m-d') && $this->cumplimientoEnd === now()->format('Y-m-d') => 'today',
                    $this->cumplimientoStart === now()->startOfWeek()->format('Y-m-d') => 'week',
                    $this->cumplimientoStart === now()->startOfMonth()->format('Y-m-d') => 'month',
                    default => '',
                };
            @endphp
            <div class="cp-compliance-card" wire:key="compliance-{{ $this->selectedTurnoId }}">
                <div class="cp-compliance-hdr">
                    <div>
                        <div class="cp-compliance-title">Cumplimiento de Chequeos</div>
                        <div class="cp-compliance-cc">{{ $firstRow['centro_costo'] }}</div>
                    </div>
                    <div class="cp-toggle-wrap">
                        <span class="cp-toggle-opt {{ !$this->showCentroCosto ? 'is-active' : '' }}">Turno</span>
                        <label class="cp-toggle">
                            <input type="checkbox" wire:model.live="showCentroCosto">
                            <span class="cp-toggle-track"></span>
                            <span class="cp-toggle-thumb"></span>
                        </label>
                        <span class="cp-toggle-opt {{ $this->showCentroCosto ? 'is-active' : '' }}">Centro de Costo</span>
                    </div>
                </div>

                <div class="cp-compliance-body">
                    <div class="cp-presets">
                        <button wire:click="setCumplimientoPreset('today')"
                                class="cp-preset-btn {{ $activePreset === 'today' ? 'is-active' : '' }}">Hoy</button>
                        <button wire:click="setCumplimientoPreset('week')"
                                class="cp-preset-btn {{ $activePreset === 'week' ? 'is-active' : '' }}">Esta semana</button>
                        <button wire:click="setCumplimientoPreset('month')"
                                class="cp-preset-btn {{ $activePreset === 'month' ? 'is-active' : '' }}">Este mes</button>
                    </div>

                    <table class="cp-comp-table">
                        <thead>
                            <tr>
                                @if($this->showCentroCosto)<th>Turno</th>@endif
                                <th>Periodo</th>
                                <th>Actual / Esp.</th>
                                <th>%</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($this->cumplimientoTurno as $row)
                            @php
                                $pctClass = match(true) {
                                    $row['percentage'] >= 90 => 'is-ok',
                                    $row['percentage'] >= 70 => 'is-warning',
                                    default                  => 'is-danger',
                                };
                            @endphp
                            <tr class="{{ $row['is_selected'] ? 'is-selected' : '' }}">
                                @if($this->showCentroCosto)
                                    <td>
                                        {{ $row['turno'] }}
                                        @if($row['is_selected'])
                                            <span style="font-size:0.625rem;color:var(--border-focus);font-weight:600"> ★</span>
                                        @endif
                                    </td>
                                @endif
                                <td style="font-size:0.75rem;color:var(--text-muted)">{{ $row['date_range'] }}</td>
                                <td><span style="font-weight:700">{{ $row['actual'] }}</span>
                                    <span style="color:var(--text-muted)"> / {{ $row['expected'] }}</span></td>
                                <td><span class="cp-pct {{ $pctClass }}">{{ $row['percentage'] }}%</span></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

        </div>{{-- /cp-left --}}

        {{-- ══ RIGHT COLUMN ══ --}}
        <div class="cp-right">

            {{-- ── EN VIVO ── --}}
            @if(!empty($this->activityFeed['live']))
            <div class="cp-live-section">
                <div class="cp-live-header">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                        <span class="relative inline-flex h-2 w-2 rounded-full bg-red-500"></span>
                    </span>
                    <span class="cp-live-title">En Vivo</span>
                    <span class="cp-live-count">{{ count($this->activityFeed['live']) }} activo{{ count($this->activityFeed['live']) > 1 ? 's' : '' }}</span>
                </div>
                @foreach($this->activityFeed['live'] as $item)
                <a href="{{ $item['url'] }}" class="cp-live-item">
                    <span class="relative flex h-2.5 w-2.5 flex-shrink-0">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                        <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-red-500"></span>
                    </span>
                    <div class="cp-live-info">
                        <div class="cp-live-equipo">{{ $item['equipo_tag'] ?: $item['equipo'] }}</div>
                        <div class="cp-live-meta">
                            {{ $item['operador'] }} ·
                            @if($item['turno_nombre'])
                                <span class="cp-turno-tag">{{ $item['turno_nombre'] }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="cp-live-elapsed">{{ $item['elapsed'] }}</div>
                    <x-heroicon-o-arrow-right class="w-3.5 h-3.5 flex-shrink-0" style="color:var(--text-muted)" />
                </a>
                @endforeach
            </div>
            @endif

            {{-- ── ACTIVITY FEED ── --}}
            <div class="cp-feed-section">
                <div class="cp-feed-header">
                    <x-heroicon-o-clock class="w-3.5 h-3.5" style="color:var(--text-muted)" />
                    <span class="cp-feed-title">Actividad reciente</span>
                    <span class="cp-feed-count">{{ count($this->activityFeed['done']) }} eventos</span>
                </div>

                @forelse($this->activityFeed['done'] as $item)
                @php
                    $iconClass = match($item['type']) {
                        'chequeo'   => 't-chequeo',
                        'recorrido' => 't-recorrido',
                        default     => $item['status'] === 'danger' ? 't-danger' : 't-warn',
                    };
                    $isLink = $item['url'] !== null;
                @endphp
                @if($isLink)
                <a href="{{ $item['url'] }}" class="cp-feed-item is-link">
                @else
                <div class="cp-feed-item">
                @endif
                    <span class="cp-feed-time">{{ $item['time_fmt'] }}</span>
                    <span class="cp-feed-icon {{ $iconClass }}">
                        @if($item['type'] === 'chequeo')
                            <x-heroicon-m-clipboard-document-check class="w-3 h-3" />
                        @elseif($item['type'] === 'recorrido')
                            <x-heroicon-m-map class="w-3 h-3" />
                        @else
                            <x-heroicon-m-exclamation-triangle class="w-3 h-3" />
                        @endif
                    </span>
                    <div class="cp-feed-body">
                        <div class="cp-feed-equipo">{{ $item['equipo_tag'] ?: $item['equipo'] }}</div>
                        <div class="cp-feed-sub">
                            <span>{{ $item['operador'] }}</span> ·
                            @if($item['turno_nombre'])
                                <span class="cp-turno-tag">{{ $item['turno_nombre'] }}</span>
                            @endif
                            @if($item['type'] === 'reporte' && $item['prioridad'] === 'alta')
                                <span style="color:#ef4444;font-weight:700">· ALTA</span>
                            @endif
                        </div>
                        @if($item['type'] === 'reporte' && $item['falla'])
                            <div class="cp-feed-falla">{{ $item['falla'] }}</div>
                        @endif
                    </div>
                    <span style="font-size:0.5625rem;color:var(--text-muted);white-space:nowrap;padding-top:0.25rem;flex-shrink:0">
                        {{ $item['time_human'] }}
                    </span>
                @if($isLink)
                </a>
                @else
                </div>
                @endif
                @empty
                <div style="padding:1.5rem 1rem;text-align:center;font-size:0.8125rem;color:var(--text-muted)">
                    Sin actividad en las últimas 24 h
                </div>
                @endforelse
            </div>

        </div>{{-- /cp-right --}}
    </div>{{-- /cp-two-col --}}

    {{-- ─── LEGEND ─────────────────────────────────────────────── --}}
    <div class="cp-legend">
        <span class="cp-legend-item">
            <span class="cp-legend-dot" style="background:#22c55e"></span>Todo en orden
        </span>
        <span class="cp-legend-item">
            <span class="cp-legend-dot" style="background:#f59e0b"></span>Sin chequeo / reportes pendientes
        </span>
        <span class="cp-legend-item">
            <span class="cp-legend-dot animate-pulse" style="background:#ef4444"></span>Reporte urgente / en curso
        </span>
    </div>

    {{-- ─── EQUIPMENT DETAIL MODAL ─────────────────────────────── --}}
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

            <div class="cp-modal-photo">
                <img x-show="selectedEquipo?.foto_url"
                     :src="selectedEquipo?.foto_url"
                     :alt="selectedEquipo?.nombre">
                <div x-show="!selectedEquipo?.foto_url"
                     class="cp-eq-placeholder" style="position:absolute;inset:0">
                    <x-heroicon-o-cog-6-tooth class="w-14 h-14" style="color:var(--text-muted)" />
                </div>
                <button @click="selectedEquipo = null" class="cp-modal-close">
                    <x-heroicon-o-x-mark class="w-4 h-4" />
                </button>
                <div class="cp-status-overlay">
                    <span x-show="selectedEquipo?.reportes_alta_prioridad > 0"
                          style="display:inline-flex;align-items:center;gap:0.375rem;background:#ef4444;color:#fff;font-size:0.6875rem;font-weight:700;padding:0.25rem 0.625rem;border-radius:9999px">
                        <span class="w-1.5 h-1.5 rounded-full bg-white animate-pulse"></span>
                        Alerta crítica
                    </span>
                    <span x-show="selectedEquipo?.reportes_alta_prioridad === 0 && selectedEquipo?.tiene_chequeo_hoy && selectedEquipo?.reportes_pendientes === 0 && !selectedEquipo?.en_progreso"
                          style="display:inline-flex;align-items:center;gap:0.375rem;background:#22c55e;color:#fff;font-size:0.6875rem;font-weight:700;padding:0.25rem 0.625rem;border-radius:9999px">
                        ✓ Todo en orden
                    </span>
                </div>
            </div>

            <div class="cp-modal-body">
                <div>
                    <h2 class="cp-modal-title" x-text="selectedEquipo?.nombre"></h2>
                    <div class="cp-modal-meta">
                        <code class="cp-modal-code" x-text="selectedEquipo?.tag"></code>
                        <span class="cp-modal-area" x-text="selectedEquipo?.area"></span>
                    </div>
                </div>

                {{-- Live: en curso button --}}
                <template x-if="selectedEquipo?.en_progreso">
                    <a :href="selectedEquipo?.continuar_url" class="cp-modal-live-btn">
                        <span class="relative flex h-2.5 w-2.5 flex-shrink-0">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                            <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-red-500"></span>
                        </span>
                        <span class="cp-modal-live-op" x-text="'En curso · ' + (selectedEquipo?.en_progreso_operador ?? '—')"></span>
                        <span class="cp-modal-live-time" x-text="selectedEquipo?.en_progreso_desde"></span>
                        <span class="cp-modal-live-goto">Ir al chequeo →</span>
                    </a>
                </template>

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
                    <template x-if="selectedEquipo?.ultimo_chequeo_operador">
                        <div class="cp-modal-row">
                            <span class="cp-modal-row-lbl">Operador</span>
                            <span class="cp-modal-row-val" x-text="selectedEquipo?.ultimo_chequeo_operador"></span>
                        </div>
                    </template>
                    <template x-if="selectedEquipo?.capacidad">
                        <div class="cp-modal-row">
                            <span class="cp-modal-row-lbl">Capacidad</span>
                            <span class="cp-modal-row-val" x-text="selectedEquipo?.capacidad"></span>
                        </div>
                    </template>
                    <template x-if="selectedEquipo?.ultimo_chequeo_viejo && !selectedEquipo?.en_progreso">
                        <div class="cp-warning-box" style="margin-top:0.375rem">
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
