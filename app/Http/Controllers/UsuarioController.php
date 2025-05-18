<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Rol;
use App\Models\Estudiante;
use Illuminate\Auth\Events\Registered;

class UsuarioController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('roles');
        
        // Excluir usuarios con rol de administrador (id 1)
        $query->whereDoesntHave('roles', function($q) {
            $q->where('rol.idRol', 1);
        });
        
        // Búsqueda por nombre o correo
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%');
            });
        }
        
        // Filtro por rol
        if ($request->has('rol') && !empty($request->rol)) {
            $query->whereHas('roles', function($q) use ($request) {
                $q->where('rol.idRol', $request->rol);
            });
        }
        
        $usuarios = $query->paginate(10);
        $roles = Rol::where('idRol', '!=', 1)->get(); // Excluir rol de administrador
        
        return view('Usuarios.usuario', compact('usuarios', 'roles'));
    }

    public function create()
    {
        $roles = Rol::where('idRol', '!=', 1)->get(); // Excluir rol de administrador
        return view('Usuarios.crearUsuario', compact('roles'));
    }

    public function store(Request $request)
    {
        // Verificar que no se esté intentando asignar el rol de administrador
        if (in_array(1, (array)$request->roles)) {
            return redirect()->back()
                ->with('error', 'No tiene permisos para asignar el rol de administrador')
                ->withInput();
        }
        
        // Validar los datos del formulario
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'apellidoPaterno' => 'required|string|max:255',
            'apellidoMaterno' => 'nullable|string|max:255',
            'ci' => 'required|integer|unique:users',
            'fechaNacimiento' => 'required|date',
            'genero' => 'required|in:M,F',
            'roles' => 'required|array|min:1',
            'roles.*' => 'exists:rol,idRol',
            'email_verified_at' => 'nullable',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Crear el usuario
        $userData = [
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'apellidoPaterno' => $request->apellidoPaterno,
            'apellidoMaterno' => $request->apellidoMaterno,
            'ci' => $request->ci,
            'fechaNacimiento' => $request->fechaNacimiento,
            'genero' => $request->genero,
        ];
        
        // Si el checkbox de email verificado está marcado, establecer la fecha actual
        if ($request->has('email_verified_at')) {
            $userData['email_verified_at'] = now();
        }
        
        $usuario = User::create($userData);

        // Asignar roles al usuario
        $esEstudiante = false;
        foreach ($request->roles as $rolId) {
            DB::table('userrol')->insert([
                'id' => $usuario->id,
                'idRol' => $rolId,
                'habilitado' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            // Verificar si el rol es de estudiante
            $rol = Rol::find($rolId);
            if ($rol && strtolower($rol->nombre) === 'estudiante') {
                $esEstudiante = true;
            }
        }
        
        // Si el usuario tiene rol de estudiante, crear registro en la tabla estudiante
        if ($esEstudiante) {
            Estudiante::create([
                'id' => $usuario->id
            ]);
        }
        
        // Si el correo no está verificado, enviar correo de verificación
        if (!$request->has('email_verified_at')) {
            event(new Registered($usuario));
        }

        return redirect()->route('usuarios')
            ->with('success', 'Usuario creado correctamente' . (!$request->has('email_verified_at') ? '. Se ha enviado un correo de verificación.' : ''));
    }

    public function show($id)
    {
        $usuario = User::with('roles')->findOrFail($id);
        
        // Verificar si el usuario tiene rol de administrador
        if ($usuario->roles->contains('idRol', 1)) {
            return redirect()->route('usuarios')
                ->with('error', 'No tiene permisos para ver este usuario');
        }
        
        return view('Usuarios.verUsuario', compact('usuario'));
    }

    public function edit($id)
    {
        $usuario = User::with('roles')->findOrFail($id);
        
        // Verificar si el usuario tiene rol de administrador
        if ($usuario->roles->contains('idRol', 1)) {
            return redirect()->route('usuarios')
                ->with('error', 'No tiene permisos para editar este usuario');
        }
        
        $roles = Rol::where('idRol', '!=', 1)->get(); // Excluir rol de administrador
        $usuarioRoles = $usuario->roles->pluck('idRol')->toArray();
        
        return view('Usuarios.editarUsuario', compact('usuario', 'roles', 'usuarioRoles'));
    }

    public function update(Request $request, $id)
    {
        $usuario = User::with('roles')->findOrFail($id);
        
        // Verificar si el usuario tiene rol de administrador
        if ($usuario->roles->contains('idRol', 1)) {
            return redirect()->route('usuarios')
                ->with('error', 'No tiene permisos para editar este usuario');
        }
        
        // Verificar que no se esté intentando asignar el rol de administrador
        if (in_array(1, (array)$request->roles)) {
            return redirect()->back()
                ->with('error', 'No tiene permisos para asignar el rol de administrador')
                ->withInput();
        }
        
        // Validar los datos del formulario
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            'apellidoPaterno' => 'required|string|max:255',
            'apellidoMaterno' => 'nullable|string|max:255',
            'ci' => 'required|integer|unique:users,ci,' . $id,
            'fechaNacimiento' => 'required|date',
            'genero' => 'required|in:M,F',
            'roles' => 'required|array|min:1',
            'roles.*' => 'exists:rol,idRol',
        ];
        
        // Si se proporciona una contraseña, validarla
        if ($request->filled('password')) {
            $rules['password'] = 'string|min:8|confirmed';
        }
        
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Actualizar el usuario
        // Verificar si el correo ha cambiado
        $emailCambiado = $usuario->email !== $request->email;
        
        $usuario->name = $request->name;
        $usuario->email = $request->email;
        $usuario->apellidoPaterno = $request->apellidoPaterno;
        $usuario->apellidoMaterno = $request->apellidoMaterno;
        $usuario->ci = $request->ci;
        $usuario->fechaNacimiento = $request->fechaNacimiento;
        $usuario->genero = $request->genero;
        
        // Manejar la verificación de correo electrónico
        if ($request->has('email_verified_at')) {
            // Si el checkbox está marcado, verificar el correo
            $usuario->email_verified_at = now();
        } elseif ($request->has('email_verification_processed') || $emailCambiado) {
            // Si el checkbox está desmarcado explícitamente o el correo cambió
            // y no se marcó como verificado, establecer como no verificado
            $usuario->email_verified_at = null;
        }
        // En cualquier otro caso, mantener el valor actual
        
        if ($request->filled('password')) {
            $usuario->password = Hash::make($request->password);
        }
        
        $usuario->save();

        // Actualizar roles
        DB::table('userrol')->where('id', $usuario->id)->delete();
        
        $esEstudiante = false;
        foreach ($request->roles as $rolId) {
            DB::table('userrol')->insert([
                'id' => $usuario->id,
                'idRol' => $rolId,
                'habilitado' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            // Verificar si el rol es de estudiante
            $rol = Rol::find($rolId);
            if ($rol && strtolower($rol->nombre) === 'estudiante') {
                $esEstudiante = true;
            }
        }
        
        // Verificar si el usuario debe tener registro en la tabla estudiante
        $estudianteExistente = Estudiante::find($usuario->id);
        
        if ($esEstudiante && !$estudianteExistente) {
            // Si es estudiante y no existe registro, crearlo
            Estudiante::create([
                'id' => $usuario->id
            ]);
        } elseif (!$esEstudiante && $estudianteExistente) {
            // Si no es estudiante pero existe registro, eliminarlo
            $estudianteExistente->delete();
        }
        
        // Si el correo cambió y no está verificado, enviar correo de verificación
        if ($emailCambiado && !$request->has('email_verified_at')) {
            event(new Registered($usuario));
        }

        return redirect()->route('usuarios')
            ->with('success', 'Usuario actualizado correctamente' . ($emailCambiado && !$request->has('email_verified_at') ? '. Se ha enviado un correo de verificación.' : ''));
    }

    public function destroy($id)
    {
        $usuario = User::with('roles')->findOrFail($id);
        
        // Verificar si el usuario tiene rol de administrador
        if ($usuario->roles->contains('idRol', 1)) {
            return redirect()->route('usuarios')
                ->with('error', 'No tiene permisos para eliminar este usuario');
        }
        
        // Eliminar relaciones de roles
        DB::table('userrol')->where('id', $id)->delete();
        
        // Eliminar registro de estudiante si existe
        $estudiante = Estudiante::find($id);
        if ($estudiante) {
            $estudiante->delete();
        }
        
        // Eliminar usuario
        $usuario->delete();

        return redirect()->route('usuarios')
            ->with('success', 'Usuario eliminado correctamente');
    }
}