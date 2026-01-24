@php use App\Area; @endphp
@props(['hoja', 'status' => 'new'])

@php
    // Define visual styles based on status
    $styles = match($status) {
        'pending' => [
            'card_border' => 'border-l-4 border-l-amber-500 border-y-gray-200 border-r-gray-200 dark:border-y-gray-700 dark:border-r-gray-700',
            'status_badge' => 'bg-amber-50 text-amber-700 ring-amber-600/20 dark:bg-amber-900/30 dark:text-amber-400 dark:ring-amber-400/30',
            'icon_color' => 'text-amber-500',
            'label' => 'Pendiente'
        ],
        'completed' => [
            'card_border' => 'border-l-4 border-l-emerald-500 border-y-gray-200 border-r-gray-200 dark:border-y-gray-700 dark:border-r-gray-700',
            'status_badge' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20 dark:bg-emerald-900/30 dark:text-emerald-400 dark:ring-emerald-400/30',
            'icon_color' => 'text-emerald-500',
            'label' => 'Completado'
        ],
        default => [ // 'new'
            'card_border' => 'border border-gray-200 dark:border-gray-700 pl-4', // pl-4 compensates for lack of heavy border
            'status_badge' => 'bg-gray-50 text-gray-600 ring-gray-500/10 dark:bg-gray-800 dark:text-gray-400 dark:ring-gray-400/20',
            'icon_color' => 'text-gray-400',
            'label' => 'Nuevo'
        ],
    };
@endphp

<div class="group relative flex flex-col bg-white dark:bg-gray-800 rounded-xl shadow-sm transition-all duration-300 hover:shadow-md hover:-translate-y-0.5 cursor-pointer {{ $styles['card_border'] }}">

    <div class="p-5 flex items-start gap-4">
        {{-- Avatar Section --}}
        <div class="shrink-0">
            <div class="h-14 w-14 rounded-xl overflow-hidden bg-gray-50 dark:bg-gray-700 border border-gray-100 dark:border-gray-600 shadow-sm">
                @if ($hoja->equipo->foto)
                    <img
                        class="h-full w-full object-cover group-hover:scale-105 transition-transform duration-500"
                        src="{{ asset('storage/' . $hoja->equipo->foto) }}"
                        alt="{{ $hoja->equipo->nombre }}"
                    >
                @else
                    <div class="h-full w-full flex items-center justify-center text-gray-400 dark:text-gray-500">
                        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                @endif
            </div>
        </div>

        {{-- Main Info Section --}}
        <div class="flex-1 min-w-0 pt-0.5">
            <div class="flex justify-between items-start">
                <div class="pr-2">
                    {{-- Title --}}
                    <h3 class="text-base font-bold text-gray-900 dark:text-white leading-tight group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors truncate">
                        {{ $hoja->equipo->tag }}
                    </h3>

                    {{-- Subtitle --}}
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 truncate">
                        {{ $hoja->equipo->nombre }}
                    </p>
                </div>

                {{-- Area Badge --}}
                @php
                    $areaColor = match($hoja->equipo->area) {
                        Area::TINTORERIA->value => 'text-cyan-700 bg-cyan-50 dark:text-cyan-400 dark:bg-cyan-500/10',
                        Area::LAVANDERIA_INSTITUCIONAL->value => 'text-indigo-700 bg-indigo-50 dark:text-indigo-400 dark:bg-indigo-500/10',
                        Area::CUARTO_DE_MAQUINAS->value => 'text-slate-700 bg-slate-50 dark:text-slate-400 dark:bg-slate-500/10',
                        default => 'text-gray-600 bg-gray-50 dark:text-gray-400 dark:bg-gray-500/10'
                    };
                @endphp
                <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium {{ $areaColor }}">
                    {{ $hoja->equipo->area }}
                </span>
            </div>
        </div>
    </div>

    {{-- Footer / Status Bar --}}
    <div class="px-5 pb-4 mt-auto">
        <div class="pt-3 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between text-xs sm:text-sm">

            {{-- Left Side: Status Text --}}
            <span class="inline-flex items-center gap-1.5 px-2 py-1 rounded-md text-xs font-medium ring-1 ring-inset {{ $styles['status_badge'] }}">
                {{-- Dynamic Icon based on status --}}
                @if($status === 'completed')
                    <svg class="w-3.5 h-3.5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                @elseif($status === 'pending')
                    <svg class="w-3.5 h-3.5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>
                @else
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
                @endif
                {{ $styles['label'] }}
            </span>

            {{-- Right Side: Last Check Info --}}
            <span class="flex items-center gap-1.5 text-gray-400 dark:text-gray-500 text-xs">
                @if($hoja->latestChequeoDiario)
                    <span>{{ $hoja->latestChequeoDiario->finalizado_en->diffForHumans() }}</span>
                @else
                    <span>Sin historial</span>
                @endif
            </span>
        </div>
    </div>
</div>
