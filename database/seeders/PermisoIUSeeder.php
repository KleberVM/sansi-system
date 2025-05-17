<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermisoIUSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Insertar funciones
        $funciones = ['Dashboard', 'Notificaciones', 'Delegaciones', 'Convocatoria', 'Registro', 'AreasCategorias', 'Perfil', 'Seguridad', 'InscripcionEstudiante', 'InscripcionTutor', 'Estudiantes', 'Delegados', 'Usuarios', 'Backup', 'VerificacionManual'];
        foreach ($funciones as $funcion) {
            DB::table('funcion')->updateOrInsert(['nombre' => $funcion]);
        }
        // Insertar interfaces de usuario (IU)
        $ius = ['Dashboard', 'Notificaciones', 'Delegaciones', 'Convocatoria', 'Registro', 'AreaCategoria', 'Perfil', 'Seguridad', 'InscripcionEstudiante', 'InscripcionTutor', 'Estudiantes', 'Delegados', 'Usuarios', 'Backup', 'VerificacionManual'];
        foreach ($ius as $iu) {
            DB::table('iu')->updateOrInsert(['nombreIu' => $iu]);
        }
        // Relacionar cada funcion con su interfaz de usuario equivalente
        $funcionesAll = DB::table('funcion')->get(); //obtengo las funciones
        foreach ($funcionesAll as $funcion) {
            // Buscar la IU con el mismo nombre
            $iu = DB::table('iu')->where('nombreIu', $funcion->nombre)->first();

            if ($iu) {
                DB::table('funcionIu')->updateOrInsert([
                    'idFuncion' => $funcion->idFuncion,
                    'idIu' => $iu->idIu
                ]);
            }
        }

        // Obtener roles (ten en cuenta que ya deben existir los roles)
        $rolAdminId = DB::table('rol')->where('nombre', 'administrador')->value('idRol');
        $rolEstudianteId = DB::table('rol')->where('nombre', 'estudiante')->value('idRol');
        $rolTutorId = DB::table('rol')->where('nombre', 'Tutor')->value('idRol');
        //relacionar rol Adminstrador
        $funcionesAdmin = DB::table('funcion')
            ->whereIn('nombre', [
                'Dashboard',
                'Delegaciones',
                'Convocatoria',
                'AreasCategorias',
                'Seguridad',
                'Delegados',
                'Usuarios',
                'Backup',
                'VerificacionManual'
            ])
            ->get();

        foreach ($funcionesAdmin as $funcion) {
            DB::table('rolFuncion')->updateOrInsert([
                'idFuncion' => $funcion->idFuncion,
                'idRol' => $rolAdminId
            ]);
        }
        //relacionar rol estudiante 
        $funcionesEstudiante = DB::table('funcion')
            ->whereIn('nombre', ['Dashboard', 'InscripcionEstudiante'])
            ->get();

        foreach ($funcionesEstudiante as $funcion) {
            DB::table('rolFuncion')->updateOrInsert([
                'idFuncion' => $funcion->idFuncion,
                'idRol' => $rolEstudianteId
            ]);
        }
        // Relacionar Tutor con Dashboard e InscripcionTutor
        $funcionesTutor = DB::table('funcion')
            ->whereIn('nombre', ['Dashboard', 'InscripcionTutor', 'Estudiantes'])
            ->get();

        foreach ($funcionesTutor as $funcion) {
            DB::table('rolFuncion')->updateOrInsert([
                'idFuncion' => $funcion->idFuncion,
                'idRol' => $rolTutorId
            ]);
        }
        $funcionAreasCategorias = DB::table('funcion')->where('nombre', 'AreasCategorias')->first();

        // Obtener el ID de la IU 'AreaCategoria'
        $iuAreaCategoria = DB::table('iu')->where('nombreIu', 'AreaCategoria')->first();
        if ($funcionAreasCategorias && $iuAreaCategoria) {
            DB::table('funcionIu')->updateOrInsert([
                'idFuncion' => $funcionAreasCategorias->idFuncion,
                'idIu' => $iuAreaCategoria->idIu
            ]);
        }
    }
}
