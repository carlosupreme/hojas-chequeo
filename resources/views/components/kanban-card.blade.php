@props(['status', 'equipo', 'date', 'version'])

@php
    $config = match($status) {
        'pending' => [
            'border' => 'border-l-4 border-l-amber-400',
            'badge_bg' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400',
            'icon' => 'exclamation-circle',
            'label' => 'Pendiente'
        ],
        'completed' => [
            'border' => 'border-l-4 border-l-emerald-400 grayscale hover:grayscale-0',
            'badge_bg' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400',
            'icon' => 'check-circle',
            'label' => 'Completado'
        ],
        default => [ // new
            'border' => 'border-l-4 border-l-gray-300 dark:border-l-gray-600',
            'badge_bg' => 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300',
            'icon' => 'plus',
            'label' => 'Nuevo'
        ],
    };
@endphp

<div class="group relative bg-white dark:bg-gray-800 rounded-xl p-3 shadow-sm hover:shadow-md transition-all duration-200 cursor-pointer border border-gray-100 dark:border-gray-700 {{ $config['border'] }}">
    <div class="flex gap-3">

        {{-- 1. Avatar (Fixed Size) --}}
        <div class="shrink-0">
            <div class="h-16 w-16 rounded-xl bg-gray-50 dark:bg-gray-700 overflow-hidden border border-gray-100 dark:border-gray-600 flex items-center justify-center">
                @if ($equipo->foto)
                    <img src="{{ asset('storage/' . $equipo->foto) }}" class="h-full w-full object-cover" alt="">
                @else
                    <svg class="h-6 w-6 text-gray-300 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                @endif
            </div>
        </div>

        {{-- 2. Content --}}
        <div class="flex-1 min-w-0 flex flex-col justify-between">

            {{-- Top: Text --}}
            <div>
                <h4 class="text-sm font-black text-gray-900 dark:text-white truncate leading-tight">
                    {{ $equipo->tag }}
                </h4>
                <p class="text-xs text-gray-500 dark:text-gray-400 truncate mt-0.5">
                    {{ $equipo->nombre }}
                </p>
            </div>

            {{-- Bottom: Badge & Time --}}
            <div class="flex items-end justify-between mt-2">
                {{-- Status Badge --}}
                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-[10px] font-bold uppercase tracking-wide {{ $config['badge_bg'] }}">
                    @if($status === 'pending')
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                    @elseif($status === 'completed')
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                    @else
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                    @endif
                    {{ $config['label'] }}
                </span>

                {{-- Time --}}
                <span class="text-[10px] font-medium text-gray-400 text-right leading-none">
                    @if($date)
                        {{ $date->diffForHumans(null, true, true) }}
                    @else
                        --
                    @endif
                </span>
            </div>
        </div>
    </div>
</div>
