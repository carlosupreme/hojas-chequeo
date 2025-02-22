<div class="min-h-screen py-12">
    <div class="max-w-5xl mx-auto bg-white dark:bg-gray-800 rounded-2xl shadow-xl">
        <div class="px-6 py-8 sm:px-8 border-b dark:border-gray-700">
            <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">
                Reportar falla de equipo
            </h1>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                Complete el formulario para reportar una falla en el equipo o vehículo
            </p>
        </div>

        <form wire:submit.prevent="submit" x-data="{
            photoPreview: null,
            photoName: '',
            showAreaInput: false,
            showDepartmentInput: false,
            showEquipmentInput: false,
            showTagInput: false,
            handleFileSelect(event) {
                if (!event.target.files.length) return;
                let file = event.target.files[0];
                this.photoName = file.name;
                let reader = new FileReader();
                reader.onload = e => {
                    this.photoPreview = e.target.result;
                };
                reader.readAsDataURL(file);
            },
            handleEquipmentChange(event) {
                if(event.target.value && event.target.value !== 'Otro') {
                    $wire.set('vehicle', 'N/A');
                    $wire.set('tag', 'N/A');
                    this.showTagInput = false;
                }
            },
            handleVehicleChange(event) {
                if(event.target.value && event.target.value !== 'Otro') {
                    $wire.set('equipment', 'N/A');
                    $wire.set('tag', 'N/A');
                    this.showEquipmentInput = false;
                    this.showTagInput = false;
                }
            },
            handleTagChange(event) {
                if(event.target.value && event.target.value !== 'Otro') {
                    $wire.set('equipment', 'N/A');
                    $wire.set('vehicle', 'N/A');
                    this.showEquipmentInput = false;
                }
            }
        }" class="p-6 sm:p-8 space-y-8">

            <div class="bg-gray-50 dark:bg-gray-900/50 rounded-xl p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Información Personal</h2>
                <div class="max-w-2xl">
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nombre
                        completo</label>
                    <div class="mt-1 flex rounded-md shadow-sm">
                        <span
                            class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 dark:border-gray-600 bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400 sm:text-sm">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path
                                    d="M10 0a10 10 0 1 0 10 10A10.011 10.011 0 0 0 10 0Zm0 5a3 3 0 1 1 0 6 3 3 0 0 1 0-6Zm0 13a8.949 8.949 0 0 1-4.951-1.488A3.987 3.987 0 0 1 9 13h2a3.987 3.987 0 0 1 3.951 3.512A8.949 8.949 0 0 1 10 18Z"/>
                            </svg>
                        </span>
                        <input wire:model="name" type="text" id="name" required
                               class="flex-1 min-w-0 block w-full px-3 py-2 rounded-r-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                    </div>
                    @error('name') <p class="mt-1 text-sm text-red-600 dark:text-red-500">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="bg-gray-50 dark:bg-gray-900/50 rounded-xl p-6 space-y-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Ubicación</h2>

                    <div>
                        <label for="area"
                               class="block text-sm font-medium text-gray-700 dark:text-gray-300">Área</label>
                        <select wire:model="area" id="area" required
                                x-on:change="showAreaInput = $event.target.value === 'Otro'"
                                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent sm:text-sm rounded-md transition">
                            <option value="">Selecciona área</option>
                            @foreach($formData['areas'] as $area)
                                <option value="{{ $area }}">{{ $area }}</option>
                            @endforeach
                            <option value="Otro">Otro</option>
                        </select>
                        <input x-show="showAreaInput" wire:model="area" type="text" placeholder="Especifica el área"
                               class="mt-2 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 rounded-md shadow-sm text-sm text-gray-900 dark:text-white">
                        @error('area') <p
                            class="mt-1 text-sm text-red-600 dark:text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="department" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Departamento</label>
                        <select wire:model="department" id="department" required
                                x-on:change="showDepartmentInput = $event.target.value === 'Otro'"
                                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent sm:text-sm rounded-md transition">
                            <option value="">Selecciona departamento</option>
                            @foreach($formData['departments'] as $dept)
                                <option value="{{ $dept }}">{{ $dept }}</option>
                            @endforeach
                            <option value="Otro">Otro</option>
                        </select>
                        <input x-show="showDepartmentInput" wire:model="department" type="text"
                               placeholder="Especifica el departamento"
                               class="mt-2 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 rounded-md shadow-sm text-sm text-gray-900 dark:text-white">
                        @error('department') <p
                            class="mt-1 text-sm text-red-600 dark:text-red-500">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="bg-gray-50 dark:bg-gray-900/50 rounded-xl p-6 space-y-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Equipo</h2>

                    <div>
                        <label for="equipment"
                               class="block text-sm font-medium text-gray-700 dark:text-gray-300">Bienes</label>
                        <select wire:model="equipment" id="equipment"
                                @required($this->tag == "" || $this->vehicle == "")
                                x-on:change="handleEquipmentChange($event); showEquipmentInput = $event.target.value === 'Otro'"
                                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent sm:text-sm rounded-md transition">
                            <option value="">Selecciona bien</option>
                            @foreach($formData['equipments'] as $equipment)
                                <option value="{{ $equipment }}">{{ $equipment }}</option>
                            @endforeach
                            <option value="Otro">Otro</option>
                        </select>
                        <input x-show="showEquipmentInput" wire:model="equipment" type="text"
                               placeholder="Especifica el equipo"
                               class="mt-2 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 rounded-md shadow-sm text-sm text-gray-900 dark:text-white">
                        @error('equipment') <p
                            class="mt-1 text-sm text-red-600 dark:text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="tag" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tag</label>
                        <select wire:model="tag" id="tag" @required($this->equipment == "" || $this->vehicle == "")
                        x-on:change="handleTagChange($event); showTagInput = $event.target.value === 'Otro'"
                                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent sm:text-sm rounded-md transition">
                            <option value="">Selecciona tag</option>
                            @foreach($formData['tags'] as $tag)
                                <option value="{{ $tag }}">{{ $tag }}</option>
                            @endforeach
                            <option value="Otro">Otro</option>
                        </select>
                        <input x-show="showTagInput" wire:model="tag" type="text" placeholder="Especifica el tag"
                               class="mt-2 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 rounded-md shadow-sm text-sm text-gray-900 dark:text-white">
                        @error('tag') <p
                            class="mt-1 text-sm text-red-600 dark:text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="vehicle" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Vehículos</label>
                        <select wire:model="vehicle" id="vehicle"
                                @required($this->equipment == "" || $this->tag == "") required
                                x-on:change="handleVehicleChange($event)"
                                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent sm:text-sm rounded-md transition">
                            <option value="">Selecciona vehículo</option>
                            @foreach($formData['vehicles'] as $vehicle)
                                <option value="{{ $vehicle }}">{{ $vehicle }}</option>
                            @endforeach
                            <option value="Otro">Otro</option>
                        </select>
                        @error('vehicle') <p
                            class="mt-1 text-sm text-red-600 dark:text-red-500">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <div class="bg-gray-50 dark:bg-gray-900/50 rounded-xl p-6 space-y-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Detalles de la falla</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="failure" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Falla
                            que presenta</label>
                        <input wire:model="failure" type="text" id="failure" required
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 rounded-md shadow-sm text-sm text-gray-900 dark:text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                        @error('failure') <p
                            class="mt-1 text-sm text-red-600 dark:text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="priority" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Prioridad</label>
                        <select wire:model="priority" id="priority" required
                                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent sm:text-sm rounded-md transition">
                            <option value="Baja">Baja</option>
                            <option value="Media">Media</option>
                            <option value="Alta">Alta</option>
                        </select>
                        @error('priority') <p
                            class="mt-1 text-sm text-red-600 dark:text-red-500">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label for="observations" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Observaciones</label>
                    <textarea wire:model="observations" id="observations" rows="4"
                              class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 rounded-md shadow-sm text-sm text-gray-900 dark:text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"></textarea>
                    @error('observations') <p
                        class="mt-1 text-sm text-red-600 dark:text-red-500">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="bg-gray-50 dark:bg-gray-900/50 rounded-xl p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Evidencia fotográfica</h2>

                <div class="flex flex-col items-center justify-center w-full">
                    <label for="photo"
                           class="flex flex-col items-center justify-center w-full h-64 border-2 border-gray-300 dark:border-gray-600 border-dashed rounded-lg cursor-pointer bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition"
                           :class="{ 'bg-gray-50': photoPreview }">

                        <template x-if="!photoPreview">
                            <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                <svg class="w-10 h-10 mb-3 text-gray-400" fill="none" stroke="currentColor"
                                     viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                </svg>
                                <p class="mb-2 text-sm text-gray-500 dark:text-gray-400">
                                    <span class="font-semibold">Haz clic para subir</span> o arrastra y suelta
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">PNG, JPG (IMAGEN)</p>
                            </div>
                        </template>

                        <template x-if="photoPreview">
                            <div class="flex flex-col items-center justify-center">
                                <img :src="photoPreview" class="object-cover max-h-40 rounded-lg mb-2">
                                <p class="text-sm text-gray-500" x-text="photoName"></p>
                            </div>
                        </template>

                        <input
                            id="photo"
                            type="file"
                            class="hidden"
                            wire:model="photo"
                            @change="handleFileSelect($event)"
                            accept="image/*">
                    </label>

                    <div x-show="photoPreview" class="mt-4 flex gap-2">
                        <button type="button"
                                @click="photoPreview = null; photoName = ''; $refs.photo.value = ''"
                                class="px-3 py-1 text-sm text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300">
                            Eliminar foto
                        </button>
                    </div>

                    @error('photo')
                    <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex justify-end pt-6">
                <button type="submit"
                        class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                    </svg>
                    Enviar reporte
                </button>
            </div>
        </form>
    </div>
</div>
