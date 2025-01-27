<div class="w-full p-4 space-y-6">
    <div class="flex justify-between items-center mb-4">
        <div class="relative w-full">
            <input
                wire:model.live="search"
                type="search"
                placeholder="Buscar por nombre, tag, o area"
                class="w-full pl-10 pr-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:border-gray-600 dark:text-white"
            >
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
        @foreach ($hojas as $hoja)
            <div
                wire:click="selectEquipo({{ $hoja->id }})"
                class="cursor-pointer rounded-xl shadow-md transition-all duration-300 ease-in-out transform hover:scale-105 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700"
                x-data="{}"
            >
                <div class="p-6 space-y-4">
                    <div class="flex items-start space-x-4">
                        <div class="flex-shrink-0">
                            @if ($hoja->equipo->foto)
                                <img class="h-16 w-16 rounded-full object-cover ring-2 ring-gray-300 dark:ring-gray-600" src="/storage/{{ $hoja->equipo->foto }}" alt="{{ $hoja->equipo->nombre }}">
                            @else
                                <img class="h-16 w-16 rounded-full object-cover ring-2 ring-gray-300 dark:ring-gray-600" src="{{asset('placeholder.jpg')}}" alt="Sin foto"/>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0 overflow-hidden  ">
                            <h3 class="text-lg font-semibold break-words text-gray-900 dark:text-white leading-tight">
                                {{ $hoja->equipo->nombre }}
                            </h3>
                            <p class="text-sm font-medium text-blue-600 dark:text-blue-400">
                                {{ $hoja->equipo->tag }}
                            </p>
                            <p class="text-xs font-medium text-gray-600 dark:text-gray-400">
                                version {{ $hoja->version }}
                            </p>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <p class="text-sm text-gray-600 dark:text-gray-300 flex items-center">
                            <svg class="h-4 w-4 mr-2 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                            {{ $hoja->equipo->area }}
                        </p>
                        <p class="text-sm text-gray-600 dark:text-gray-300 flex items-center">
                            <svg class="h-4 w-4 mr-2 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            Ultimo chequeo: {{ $hoja->chequeosDiarios->sortByDesc('created_at')->first()?->created_at->diffForHumans() ?? 'Nunca' }}
                        </p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

