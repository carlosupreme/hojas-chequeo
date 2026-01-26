@php
    use Carbon\Carbon;
    $hojaChequeo = $record->hojaChequeo;
    $user = $record->user;
@endphp

<div>
    <div data-animate="chequeo-items" class="min-h-screen bg-gray-50/50 dark:bg-gray-950 pb-20">

        {{-- 1. STICKY HEADER BAR --}}
        <div
            class="sticky top-0 z-30 bg-white/80 dark:bg-gray-900/80 backdrop-blur-md border-b border-gray-200 dark:border-gray-800 shadow-sm transition-all rounded-b-lg">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 py-4">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    {{-- Left: Title & Context --}}
                    <div class="flex items-center gap-4">
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <h1 class="text-xl font-bold text-gray-900 dark:text-white tracking-tight leading-none">
                                    {{ $hojaChequeo->equipo->nombre }}
                                </h1>
                                <span
                                    class="inline-flex items-center rounded-md bg-blue-50 px-2 py-0.5 text-xs font-bold text-blue-700 ring-1 ring-inset ring-blue-700/10 dark:bg-blue-900/30 dark:text-blue-400 dark:ring-blue-400/30 uppercase tracking-wide">
                                    {{ $hojaChequeo->equipo->tag }}
                                </span>
                            </div>
                            <div class="flex items-center gap-3 text-xs text-gray-500 dark:text-gray-400 font-medium">
                                <span class="flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path
                                            stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    {{ $user->turno->nombre ?? 'N/A' }}
                                </span>
                                <span class="text-gray-300 dark:text-gray-600">|</span>
                                <span>
                                    Realizado por: <span
                                        class="text-gray-900 dark:text-white font-semibold">{{ $record->nombre_operador }}</span>
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Right: Date Display --}}
                    <div class="w-full sm:w-auto text-right">
                        <div class="text-sm font-bold text-gray-900 dark:text-white capitalize">
                            {{ Carbon::parse($record->finalizado_en)->locale('es')->isoFormat('dddd D, MMMM YYYY') }}
                        </div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">
                            {{ Carbon::parse($record->finalizado_en)->format('H:i A') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 2. MAIN CONTENT --}}
        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-8 space-y-8">

            {{-- Section A: Checklist Items (Read Only Table) --}}
            <div
                class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-800 overflow-hidden">
                <div
                    class="px-6 py-4 border-b border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-800/50 flex justify-between items-center">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                        <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                        </svg>
                        Items de Control
                    </h3>
                </div>
                {{-- Passing readOnly=true to disable inputs --}}
                <livewire:chequeo-items :hoja="$hojaChequeo" :ejecucion="$record" :readOnly="true"/>
            </div>

            {{-- Section B: Finalization Details (Observaciones & Firmas) --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                {{-- Left: Observations --}}
                <div
                    class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-800 flex flex-col h-full">
                    <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-800">
                        <h3 class="font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                            <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                            </svg>
                            Observaciones del Operador
                        </h3>
                    </div>
                    <div class="p-5 flex-1">
                        @if($record->observaciones)
                            <div class="prose prose-sm dark:prose-invert text-gray-600 dark:text-gray-300">
                                <p>{{ $record->observaciones }}</p>
                            </div>
                        @else
                            <div class="h-full flex flex-col items-center justify-center text-gray-400 py-6">
                                <svg class="w-10 h-10 mb-2 opacity-20" fill="none" viewBox="0 0 24 24"
                                     stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                </svg>
                                <span class="text-sm italic">Sin observaciones registradas.</span>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Right: Signatures & Metadata --}}
                <div class="space-y-6">
                    {{-- Operator Signature Card --}}
                    <div
                        class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-800 p-5">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h4 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider">
                                    Firma Operador</h4>
                                <p class="text-xs text-gray-500">{{ $record->nombre_operador }}</p>
                            </div>
                            <div
                                class="px-2 py-1 bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-400 text-[10px] font-bold uppercase rounded">
                                Validado
                            </div>
                        </div>

                        <div
                            class="h-32 border-2 border-dashed border-gray-200 dark:border-gray-700 rounded-lg bg-gray-50/50 dark:bg-gray-800/50 flex items-center justify-center overflow-hidden relative group">
                            @if($record->firma_operador)
                                {{-- Assuming signature is stored as image URL or Base64 --}}
                                <img
                                    src="{{ app(\App\Services\ImageService::class)->getAsBase64($record->firma_operador) }}"
                                    class="max-h-full max-w-full object-contain mix-blend-multiply dark:mix-blend-normal"
                                    alt="Firma Operador"
                                >
                            @else
                                <span class="text-gray-400 text-sm italic">Firma Digital No Disponible</span>
                            @endif
                        </div>
                    </div>

                    {{-- Time Stats (Bonus UX) --}}
                    <div
                        class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-800 p-4">
                        <div class="grid grid-cols-3 divide-x divide-gray-100 dark:divide-gray-700 text-center">
                            <div>
                                <span
                                    class="block text-[10px] uppercase text-gray-400 font-bold tracking-wider">Inicio</span>
                                <span class="text-sm font-mono text-gray-700 dark:text-gray-200">
                                    {{ $record->created_at->format('H:i') }}
                                </span>
                            </div>
                            <div>
                                <span
                                    class="block text-[10px] uppercase text-gray-400 font-bold tracking-wider">Fin</span>
                                <span class="text-sm font-mono text-gray-700 dark:text-gray-200">
                                    {{ $record->finalizado_en->format('H:i') }}
                                </span>
                            </div>
                            <div>
                                <span class="block text-[10px] uppercase text-gray-400 font-bold tracking-wider">Duración</span>
                                <span class="text-sm font-mono text-gray-900 dark:text-white font-bold">
                                    {{ (int)$record->created_at->diffInMinutes($record->finalizado_en, true) }} min
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Supervisor Signature (Conditional) --}}
                    @if($record->firma_supervisor)
                        <div
                            class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-800 p-5 mt-4">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <h4 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider">
                                        Vo.Bo. Supervisor</h4>
                                </div>
                            </div>
                            <div class="h-24 flex items-center justify-center">
                                <img src="{{app(\App\Services\ImageService::class)->getAsBase64($record->firma_supervisor) }}" class="max-h-full object-contain"
                                     alt="Firma Supervisor">
                            </div>
                        </div>
                    @endif

                </div>
            </div>

        </div>
    </div>
</div>
