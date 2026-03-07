<x-filament-panels::page>
    <div class="space-y-4 pb-8">

        {{-- ─── 1. Context bar: turno + centro_costo ────────────────────────────── --}}
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-4 w-full">
            {{ $this->form }}
        </div>

        {{-- ─── 2. Tombola selector ─────────────────────────────────────────────── --}}
        <div>
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2 px-1">
                Seleccionar Tombola
            </p>
            <div class="overflow-x-auto -mx-4 px-4">
                <div class="flex gap-3 pb-2" style="width: max-content">
                    @foreach($tombolas as $tombola)
                        @php $isSelected = $this->equipoId === $tombola->id; @endphp
                        <button
                            wire:click="selectEquipo({{ $tombola->id }})"
                            wire:key="tom-{{ $tombola->id }}"
                            class="flex-shrink-0 w-36 rounded-xl border-2 p-3 text-left transition-all
                                {{ $isSelected
                                    ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/30 shadow-md shadow-blue-500/20'
                                    : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 hover:border-gray-300 dark:hover:border-gray-600' }}"
                        >
                            <div class="text-xs text-gray-400 dark:text-gray-500 mb-0.5 font-medium">Tombola</div>
                            <div class="font-bold text-sm {{ $isSelected ? 'text-blue-700 dark:text-blue-300' : 'text-gray-800 dark:text-gray-200' }} truncate">
                                {{ $tombola->tag }}
                            </div>
                            <div class="mt-2 flex items-center gap-1">
                                <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-semibold
                                    {{ $tombola->registro_cargas_count > 0
                                        ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400'
                                        : 'bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-500' }}">
                                    {{ $tombola->registro_cargas_count }}
                                    <span class="font-normal">hoy</span>
                                </span>
                            </div>
                        </button>
                    @endforeach

                    @if($tombolas->isEmpty())
                        <p class="text-sm text-gray-400 py-4">No hay tombolas registradas.</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- ─── 3. Active equipo panel ──────────────────────────────────────────── --}}
        @if($equipo)

            {{-- Stats strip --}}
            <div class="grid grid-cols-3 gap-3">
                <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-3 text-center">
                    <div class="text-3xl font-bold text-blue-600 dark:text-blue-400 tabular-nums">
                        {{ $stats['hoy'] }}
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1 font-medium">Hoy total</div>
                </div>
                <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-3 text-center">
                    <div class="text-3xl font-bold text-violet-600 dark:text-violet-400 tabular-nums">
                        {{ $stats['turno'] }}
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1 font-medium">Este turno</div>
                </div>
                <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-3 text-center">
                    <div class="text-3xl font-bold text-emerald-600 dark:text-emerald-400 tabular-nums">
                        {{ $stats['yo'] }}
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1 font-medium">Yo hoy</div>
                </div>
            </div>

            {{-- Big action button --}}
            <button
                wire:click="registrarCarga"
                wire:loading.attr="disabled"
                wire:loading.class="opacity-60 !scale-100 cursor-not-allowed"
                class="group relative w-full overflow-hidden rounded-3xl select-none
                       bg-gradient-to-b from-blue-500 to-blue-700
                       shadow-[0_8px_32px_-4px_rgba(59,130,246,0.55),0_2px_8px_-2px_rgba(59,130,246,0.3)]
                       active:shadow-[0_2px_8px_-2px_rgba(59,130,246,0.4)]
                       transition-all duration-150 ease-out
                       active:scale-[.97] active:translate-y-px
                       disabled:cursor-not-allowed"
            >
                {{-- Gloss sheen --}}
                <span class="pointer-events-none absolute inset-x-0 top-0 h-1/2 rounded-t-3xl
                             bg-gradient-to-b from-white/20 to-transparent"></span>

                {{-- Idle state --}}
                <span wire:loading.remove wire:target="registrarCarga"
                      class="flex flex-col items-center justify-center gap-3 py-8 px-6">
                    {{-- Circle icon container --}}
                    <span class="flex items-center justify-center w-16 h-16 rounded-full
                                 bg-white/15 ring-1 ring-white/20
                                 group-active:bg-white/10 transition-colors">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                  d="M12 4v16m8-8H4"/>
                        </svg>
                    </span>
                    <span class="flex flex-col items-center gap-0.5">
                        <span class="text-xl font-extrabold tracking-wide text-white drop-shadow-sm">
                            Registrar Carga
                        </span>
                        <span class="text-sm font-semibold text-blue-100/80 tracking-wider uppercase">
                            {{ $equipo->tag }}
                        </span>
                    </span>
                </span>

                {{-- Loading state --}}
                <span wire:loading wire:target="registrarCarga"
                      class="flex items-center justify-center gap-3 py-8 px-6">
                    <svg class="w-6 h-6 text-white animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor"
                              d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                    </svg>
                    <span class="text-xl font-extrabold text-white">Guardando…</span>
                </span>
            </button>

            {{-- Today's load history --}}
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                        Cargas de hoy — {{ $equipo->tag }}
                    </h3>
                    <span class="text-xs text-gray-400">{{ now()->format('d/m/Y') }}</span>
                </div>

                @forelse($cargasHoy->groupBy(fn($c) => $c->registrado_en->format('H')) as $hora => $cargas)
                    <div class="border-b border-gray-50 dark:border-gray-800/60 last:border-0">
                        {{-- Hour header --}}
                        <div class="px-4 py-1.5 bg-gray-50/70 dark:bg-gray-800/40 flex items-center justify-between">
                            <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 font-mono">
                                {{ $hora }}:00
                            </span>
                            <span class="text-xs text-gray-400">
                                {{ $cargas->count() }} {{ Str::plural('carga', $cargas->count()) }}
                            </span>
                        </div>
                        {{-- Cargas in this hour --}}
                        @foreach($cargas as $carga)
                            <div class="px-4 py-2.5 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <span class="text-sm font-mono text-gray-700 dark:text-gray-300">
                                        {{ $carga->registrado_en->format('H:i') }}
                                    </span>
                                    <span class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ $carga->user->name }}
                                    </span>
                                </div>
                                @if($carga->turno)
                                    <span class="text-xs px-2 py-0.5 rounded-full bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400">
                                        {{ $carga->turno->nombre }}
                                    </span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @empty
                    <div class="px-4 py-8 text-center">
                        <svg class="w-10 h-10 text-gray-200 dark:text-gray-700 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        <p class="text-sm text-gray-400">Sin cargas registradas hoy</p>
                    </div>
                @endforelse
            </div>

        @else
            {{-- Empty state --}}
            <div class="rounded-xl border-2 border-dashed border-gray-200 dark:border-gray-700 p-10 text-center">
                <svg class="w-12 h-12 text-gray-300 dark:text-gray-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5"/>
                </svg>
                <p class="text-sm font-medium text-gray-400 dark:text-gray-500">
                    Selecciona una tombola para comenzar
                </p>
            </div>
        @endif

    </div>
</x-filament-panels::page>
