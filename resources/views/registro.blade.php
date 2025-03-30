
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Registro (Inscripción)') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="registro-container">
                        <!-- Content will go here -->
                        <h3 class="text-lg font-medium text-gray-900">Formulario de Registro</h3>
                        <p class="mt-2 text-gray-600">Esta sección permite registrar nuevos participantes en el sistema.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>