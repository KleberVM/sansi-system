<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Rol;
use App\Models\Delegacion;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Support\Str;
use App\Http\Controllers\Inscripcion\ObtenerAreasConvocatoria;
use App\Http\Controllers\Inscripcion\VerificarExistenciaConvocatoria;
use App\Events\CreacionCuenta;
use App\Models\Tutor;
use App\Models\TutorAreaDelegacion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'apellidoPaterno' => $request->apellidoPaterno,
            'apellidoMaterno' => $request->apellidoMaterno,
            'ci' => $request->ci,
            'fechaNacimiento' => $request->fechaNacimiento,
            'genero' => $request->genero,
            'password' => Hash::make($request->password),
        ]);

        $rol = Rol::find(3);
        if ($rol) {
            $user->roles()->attach($rol->idRol, ['habilitado' => true]);
            $user->estudiante()->create();
        }

        // No disparamos el evento Registered aquí para que no se envíe el correo de verificación
        // Solo lo haremos cuando el tutor sea aprobado
        event(new CreacionCuenta(
            $user->id,
            '¡Tu cuenta ha sido creada exitosamente!, Gracias por formar parte de Ohsansi.',
            'sistema'
        ));
        Auth::login($user);

        return redirect(RouteServiceProvider::HOME)->with('message', 'Tu cuenta ha sido creada. Un administrador revisará tu solicitud para aprobarla.');
    }    //Para el registro de tutores//
    public function createTutor()
    {
        $unidades = Delegacion::all();
        
        // Obtener todas las convocatorias en estado "Publicada"
        $convocatorias = \App\Models\Convocatoria::where('estado', 'Publicada')
            ->orderBy('fechaInicio', 'desc')
            ->get();
            
        if ($convocatorias->count() === 0) {
            return response()->json([
                'error' => true,
                'mensaje' => 'No hay convocatorias publicadas en este momento'
            ]);
        }
        
        // Obtener todas las áreas disponibles en el sistema
        $areas = \App\Models\Area::all();
        
        return view('auth.registerTutor', compact('unidades', 'areas', 'convocatorias'));
    }    public function storeTutor(Request $request)
    {
        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255', 'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/u'],
            'apellidoPaterno' => ['required', 'string', 'max:255', 'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/u'],
            'apellidoMaterno' => ['required', 'string', 'max:255', 'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/u'],
            'ci' => ['required', 'string', 'digits:7', 'unique:users,ci'],
            'fechaNacimiento' => ['required', 'date', function ($attribute, $value, $fail) {
                if (Carbon::parse($value)->age < 18) {
                    $fail('Debe tener al menos 18 años para registrarse.');
                }
            }],
            'genero' => ['required', 'string', 'in:M,F'],
            'telefono' => ['required', 'string', 'digits:8', 'unique:tutor,telefono'],
            'profesion' => ['required', 'string', 'max:255', 'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/u'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email', 'regex:/^[a-zA-Z0-9._%+-]+@gmail\.com$/i'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'delegacion_tutoria' => ['required', 'integer', 'exists:delegacion,idDelegacion'],
            'convocatorias' => ['required', 'array', 'min:1'],
            'convocatorias.*' => ['integer', 'exists:convocatoria,idConvocatoria'],
            'areas' => ['required', 'array', 'min:1'],
            'areas.*' => ['required', 'array', 'min:1'],
            'areas.*.*' => ['integer', 'exists:area,idArea'],
            'cv' => ['required', 'file', 'mimes:pdf', 'max:2048'], // Max 2MB PDF
            'terms' => ['required', 'accepted'],
        ]);

        DB::beginTransaction();

        try {
            // 1. Handle CV Upload
            $cvPath = null;
            if ($request->hasFile('cv')) {
                $file = $request->file('cv');
                $fileName = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
                $cvPath = $file->storeAs('cvs_tutores', $fileName, 'public');
            }

            // 2. Create User
            $user = User::create([
                'name' => $validatedData['name'],
                'apellidoPaterno' => $validatedData['apellidoPaterno'],
                'apellidoMaterno' => $validatedData['apellidoMaterno'],
                'ci' => $validatedData['ci'],
                'fechaNacimiento' => $validatedData['fechaNacimiento'],
                'genero' => $validatedData['genero'],
                'email' => $validatedData['email'],
                'password' => Hash::make($validatedData['password']),
                'email_verified_at' => null, // Email will be verified after admin approval
            ]);

            // 3. Assign Role 'Tutor' (ID 2)
            $rolTutor = Rol::find(2);
            if ($rolTutor) {
                $user->roles()->attach($rolTutor->idRol, ['habilitado' => true]);
            } else {
                DB::rollBack();
                return back()->withErrors(['msg' => 'Error interno: Rol de tutor no encontrado.'])->withInput();
            }

            // 4. Generate tokenTutor
            $tokenTutor = Str::random(40);

            // 5. Create Tutor Record
            $tutor = Tutor::create([
                'id' => $user->id,
                'profesion' => $validatedData['profesion'],
                'telefono' => $validatedData['telefono'],
                'linkRecurso' => $cvPath,
                'tokenTutor' => $tokenTutor,
                'estado' => 'pendiente',
            ]);

            // 6. Create TutorAreaDelegacion Records
            $idDelegacion = $validatedData['delegacion_tutoria'];
            if (!empty($validatedData['areas'])) {
                foreach ($validatedData['areas'] as $idConvocatoria => $areaIds) {
                    if (in_array($idConvocatoria, $validatedData['convocatorias'])) {
                        foreach ($areaIds as $idArea) {
                            TutorAreaDelegacion::create([
                                'id' => $user->id,
                                'idArea' => $idArea,
                                'idDelegacion' => $idDelegacion,
                                'idConvocatoria' => $idConvocatoria,
                                'tokenTutor' => $tokenTutor,
                            ]);
                        }
                    }
                }
            }

            DB::commit();

            event(new CreacionCuenta(
                $user->id,
                '¡Tu cuenta ha sido creada exitosamente!, Gracias por formar parte de Ohsansi.',
                'sistema'
            ));
            Auth::login($user);

            return redirect(RouteServiceProvider::HOME)->with('message', 'Tu cuenta ha sido creada exitosamente. Un administrador revisará tu solicitud para aprobarla pronto.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            // \Log::error('Tutor registration error: ' . $e->getMessage() . ' Stack: ' . $e->getTraceAsString());
            return back()->withErrors(['msg' => 'Ocurrió un error durante el registro. Por favor, inténtalo de nuevo. Detalle: ' . $e->getMessage()])->withInput();
        }
    }



    public function storeDelegadoDelegacion(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'apellidoPaterno' => ['required', 'string', 'max:255'],
            'apellidoMaterno' => ['required', 'string', 'max:255'],
            'ci' => ['required', 'numeric', 'min:7'],  // Asegura que el CI sea un número con al menos 7 dígitos
            'fechaNacimiento' => ['required', 'date'],
            'genero' => ['required', 'in:M,F'],  // Validación para "Masculino" y "Femenino"
            'telefono' => ['required', 'numeric', 'min:8'],  // Teléfono con mínimo 8 dígitos
            'profesion' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],  // Validación del email y su unicidad
            'delegacion_tutoria' => ['required', 'exists:delegacion,idDelegacion'],  // Validación de que la delegación existe
            'area_tutoria' => ['required', 'array'],  // Validación de que se seleccionó al menos un área
            'area_tutoria.*' => ['exists:area,idArea'],  // Validación de que cada área seleccionada existe
            'password' => ['required', 'confirmed', Rules\Password::defaults()],  // Validación de la contraseña
            'cv' => ['required', 'mimes:pdf', 'max:2048'],  // Validación del archivo PDF
            'terms' => ['required', 'accepted'],  // Validación para aceptar los términos y condiciones
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'apellidoPaterno' => $request->apellidoPaterno,
            'apellidoMaterno' => $request->apellidoMaterno,
            'ci' => $request->ci,
            'fechaNacimiento' => $request->fechaNacimiento,
            'genero' => $request->genero,
            'password' => Hash::make($request->password),
        ]);

        $rol = Rol::find(2);
        if ($rol) {
            $user->roles()->syncWithoutDetaching([$rol->idRol]);
            $fileUrl = null;
            if ($request->hasFile('cv')) {
                $path = $request->file('cv')->store('public/cvs');
                $fileUrl = asset('storage/' . str_replace('public/', '', $path));
            }
            // Generar un token único para el tutor
            $tokenTutor = Str::random(40);

            $tutor = $user->tutor()->create([
                'profesion' => $request->profesion,
                'telefono' => $request->telefono,
                //aqui poner la loguica para el link de recurso
                'linkRecurso' => $fileUrl,
                'tokenTutor' => $tokenTutor,
                'es_director' => true, // Este tutor será director de la delegación
                'estado' => 'pendiente'
            ]);

            // Adjuntar cada área seleccionada a la delegación
            foreach ($request->area_tutoria as $areaId) {
                $tutor->areas()->attach($areaId, [
                    'idDelegacion' => $request->delegacion_tutoria,
                    'tokenTutor' => $tokenTutor // Usar el mismo token generado para el tutor
                ]);
            }
        }

        // No disparamos el evento Registered aquí para que no se envíe el correo de verificación
        // Solo lo haremos cuando el tutor sea aprobado
        event(new CreacionCuenta(
            $user->id,
            '¡Tu cuenta ha sido creada exitosamente!, Gracias por formar parte de Ohsansi.',
            'sistema'
        ));
        Auth::login($user);

        return redirect(RouteServiceProvider::HOME)->with('message', 'Tu cuenta ha sido creada. Un administrador revisará tu solicitud para aprobarla.');
    }
}
