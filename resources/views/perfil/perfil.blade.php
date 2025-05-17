<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <i class="fas fa-user-circle"></i> Mi Perfil
        </h2>
    </x-slot>
    
    <!-- Estilos y scripts específicos para la página de perfil -->
    <link rel="stylesheet" href="{{ asset('css/perfil/perfil.css') }}">
    <script src="{{ asset('js/perfil.js') }}" defer></script>

    <link rel="stylesheet" href="/css/perfil/perfil.css">
    <script src="/js/perfil.js" defer></script>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Mensajes de éxito o error -->
            @if (session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
                    <p>{{ session('success') }}</p>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Información del perfil -->
                        <div class="bg-white p-6 rounded-lg shadow-md">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-semibold" style="color:white"><i class="fas fa-info-circle"></i> Información Personal</h3>
                                <div class="flex gap-2">
                                    <button type="button" id="change-password-btn" class="password-btn bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                                        <i class="fas fa-key"></i> Cambiar Contraseña
                                    </button>
                                    <button type="button" id="edit-profile-btn" class="edit-btn bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                                        <i class="fas fa-edit"></i> Editar
                                    </button>
                                    <button type="button" id="cancel-edit-btn" class="cancel-btn bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline hidden">
                                        <i class="fas fa-times"></i> Cancelar
                                    </button>
                                </div>
                            </div>
                            
                            <form method="POST" action="{{ route('perfil.update') }}" id="profile-form">
                                @csrf
                                @method('PUT')
                                
                                <div class="mb-4">
                                    <label for="name" class="block text-sm font-medium text-gray-700">Nombre</label>
                                    <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" class="profile-input mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" disabled>
                                    @error('name')
                                        <span class="text-red-500 text-xs">{{ $message }}</span>
                                    @enderror
                                </div>
                                
                                <div class="mb-4">
                                    <label for="email" class="block text-sm font-medium text-gray-700">Correo Electrónico</label>
                                    <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" class="profile-input mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" disabled>
                                    @error('email')
                                        <span class="text-red-500 text-xs">{{ $message }}</span>
                                    @enderror
                                </div>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="mb-4">
                                        <label for="apellidoPaterno" class="block text-sm font-medium text-gray-700">Apellido Paterno</label>
                                        <input type="text" name="apellidoPaterno" id="apellidoPaterno" value="{{ old('apellidoPaterno', $user->apellidoPaterno) }}" class="profile-input mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" disabled>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label for="apellidoMaterno" class="block text-sm font-medium text-gray-700">Apellido Materno</label>
                                        <input type="text" name="apellidoMaterno" id="apellidoMaterno" value="{{ old('apellidoMaterno', $user->apellidoMaterno) }}" class="profile-input mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" disabled>
                                    </div>
                                </div>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="mb-4">
                                        <label for="ci" class="block text-sm font-medium text-gray-700">CI</label>
                                        <input type="number" name="ci" id="ci" value="{{ old('ci', $user->ci) }}" class="profile-input mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" disabled>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label for="fechaNacimiento" class="block text-sm font-medium text-gray-700">Fecha de Nacimiento</label>
                                        <input type="date" name="fechaNacimiento" id="fechaNacimiento" value="{{ old('fechaNacimiento', $user->fechaNacimiento) }}" class="profile-input mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" disabled>
                                    </div>
                                </div>
                                
                                <div class="mb-4">
                                    <label for="genero" class="block text-sm font-medium text-gray-700">Género</label>
                                    <select name="genero" id="genero" class="profile-input mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" disabled>
                                        <option value="">Seleccionar</option>
                                        <option value="M" {{ old('genero', $user->genero) == 'M' ? 'selected' : '' }}>Masculino</option>
                                        <option value="F" {{ old('genero', $user->genero) == 'F' ? 'selected' : '' }}>Femenino</option>
                                        <option value="O" {{ old('genero', $user->genero) == 'O' ? 'selected' : '' }}>Otro</option>
                                    </select>
                                </div>
                                
                                <div class="flex items-center justify-end mt-4">
                                    <button type="submit" id="save-profile-btn" class="save-btn bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline hidden">
                                        <i class="fas fa-save"></i> Guardar Cambios
                                    </button>
                                </div>
                            </form>
                        </div>
                        
                        <!-- Cambio de contraseña -->
                        <div class="bg-white p-6 rounded-lg shadow-md password-section hidden">
                            <h3 class="text-lg font-semibold mb-4"><i class="fas fa-lock"></i> Cambiar Contraseña</h3>
                            
                            <form method="POST" action="{{ route('perfil.update-password') }}" id="password-form">
                                @csrf
                                @method('PUT')
                                
                                <div class="mb-4">
                                    <label for="current_password" class="block text-sm font-medium text-gray-700">Contraseña Actual</label>
                                    <input type="password" name="current_password" id="current_password" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    @error('current_password')
                                        <span class="text-red-500 text-xs">{{ $message }}</span>
                                    @enderror
                                </div>
                                
                                <div class="mb-4">
                                    <label for="password" class="block text-sm font-medium text-gray-700">Nueva Contraseña</label>
                                    <input type="password" name="password" id="password" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    @error('password')
                                        <span class="text-red-500 text-xs">{{ $message }}</span>
                                    @enderror
                                </div>
                                
                                <div class="mb-4">
                                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirmar Nueva Contraseña</label>
                                    <input type="password" name="password_confirmation" id="password_confirmation" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                </div>
                                
                                <div class="flex items-center justify-end mt-4">
                                    <button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                                        <i class="fas fa-key"></i> Actualizar Contraseña
                                    </button>
                                </div>
                            </form>
                        </div>
                        
                        <!-- Información del rol -->
                        <div class="bg-white p-6 rounded-lg shadow-md md:col-span-2">
                            <h3 class="text-lg font-semibold mb-4" style="color:white"><i class="fas fa-user-tag"></i> Roles y Permisos</h3>
                            
                            <div class="overflow-x-auto">
                                <table class="min-w-full bg-white">
                                    <thead>
                                        <tr>
                                            <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Rol</th>
                                            <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($user->roles as $rol)
                                        <tr>
                                            <td class="py-2 px-4 border-b border-gray-200">{{ $rol->nombre }}</td>
                                            <td class="py-2 px-4 border-b border-gray-200">
                                                @if($rol->pivot->habilitado)
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                        <i class="fas fa-check-circle mr-1"></i> Habilitado
                                                    </span>
                                                @else
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                        <i class="fas fa-times-circle mr-1"></i> Deshabilitado
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>