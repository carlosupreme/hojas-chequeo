<x-layouts.app>
    <div class="min-h-screen flex items-center justify-center bg-gradient-to-r from-blue-50 to-purple-50">
        <div class="text-center">
            <!-- Título de la página -->
            <h1 class="text-4xl font-bold text-gray-800 mb-8 animate-fade-in-down">
                Bienvenido, Operador
            </h1>

            <!-- Contenedor de botones -->
            <div class="space-y-6">
                <!-- Botón "Reportar Falla" -->
                <a href=""
                   class="inline-block w-64 px-6 py-3 bg-red-500 text-white font-semibold rounded-lg shadow-lg hover:bg-red-600 transform hover:scale-105 transition-transform duration-300 animate-fade-in-up">
                    <span class="flex items-center justify-center space-x-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                             stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>Reportar Falla</span>
                    </span>
                </a>

                <!-- Botón "Hacer Chequeo" -->
                <a href="{{route('chequeo')}}"
                   class="inline-block w-64 px-6 py-3 bg-blue-500 text-white font-semibold rounded-lg shadow-lg hover:bg-blue-600 transform hover:scale-105 transition-transform duration-300 animate-fade-in-up delay-100">
                    <span class="flex items-center justify-center space-x-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                             stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>Hacer Chequeo</span>
                    </span>
                </a>
            </div>
        </div>
    </div>
</x-layouts.app>
