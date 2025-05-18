<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
class RolSeeder extends Seeder
{
    public function run()
    {
        $roles = ['Administrador', 'Tutor', 'Estudiante'];

        // Insertar roles si no existen
        foreach ($roles as $rol) {
            DB::table('rol')->updateOrInsert(['nombre' => $rol]);
        }

        // Obtener los roles
        $rolAdmin = DB::table('rol')->where('nombre', 'Administrador')->first();
        $rolTutor = DB::table('rol')->where('nombre', 'Tutor')->first();
        $rolEstudiante = DB::table('rol')->where('nombre', 'Estudiante')->first();

        // Usuarios de prueba
        $usuarios = [
            [
                'name' => 'Admin',
                'email' => 'admin@gmail.com',
                'password' => Hash::make('12345678'),
                'apellidoPaterno' => 'Lopez',
                'apellidoMaterno' => 'Mamani',
                'ci' => 12345678,
                'fechaNacimiento' => '1985-01-01',
                'genero' => 'M',
                'rol' => $rolAdmin->idRol,
            ],
            [
                'name' => 'Tutor',
                'email' => 'tutor@gmail.com',
                'password' => Hash::make('12345678'),
                'apellidoPaterno' => 'Quispe',
                'apellidoMaterno' => 'Choque',
                'ci' => 87654321,
                'fechaNacimiento' => '1990-05-12',
                'genero' => 'F',
                'rol' => $rolTutor->idRol,
            ],
            [
                'name' => 'Estudiante',
                'email' => 'estudiante@gmail.com',
                'password' => Hash::make('12345678'),
                'apellidoPaterno' => 'Fernandez',
                'apellidoMaterno' => 'Perez',
                'ci' => 44556677,
                'fechaNacimiento' => '2007-09-20',
                'genero' => 'M',
                'rol' => $rolEstudiante->idRol,
            ],
        ];

        foreach ($usuarios as $usuario) {
            // Verificar si el usuario ya existe por email
            $userExistente = DB::table('users')->where('email', $usuario['email'])->first();

            if (!$userExistente) {
                // Insertar usuario
                $userId = DB::table('users')->insertGetId([
                    'name' => $usuario['name'],
                    'email' => $usuario['email'],
                    'password' => $usuario['password'],
                    'apellidoPaterno' => $usuario['apellidoPaterno'],
                    'apellidoMaterno' => $usuario['apellidoMaterno'],
                    'ci' => $usuario['ci'],
                    'fechaNacimiento' => $usuario['fechaNacimiento'],
                    'genero' => $usuario['genero'],
                    'email_verified_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Preparar datos para la tabla userrol
                $datosUserRol = [
                    'id' => $userId,
                    'idRol' => $usuario['rol'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                // Si es tutor, agregar campo habilitado
                if ($usuario['rol'] == $rolTutor->idRol) {
                    $esPrimerTutor = DB::table('userrol')->where('idRol', $rolTutor->idRol)->count() == 0;
                    $datosUserRol['habilitado'] = $esPrimerTutor;
                }

                DB::table('userrol')->insert($datosUserRol);
            }
        }
    }
}
