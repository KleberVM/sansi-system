<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class AreaCategoriaGradoSeeder extends Seeder
{
    public function run()
    {
        // Insertar grados  
        $gradosPrimaria = [
            '1ro de Primaria',
            '2do de Primaria',
            '3ro de Primaria',
            '4to de Primaria',
            '5to de Primaria',
            '6to de Primaria',
        ];

        $gradosSecundaria = [
            '1ro de Secundaria',
            '2do de Secundaria',
            '3ro de Secundaria',
            '4to de Secundaria',
            '5to de Secundaria',
            '6to de Secundaria',
        ];

        foreach (array_merge($gradosPrimaria, $gradosSecundaria) as $grado) {
            DB::table('grado')->updateOrInsert(['grado' => $grado]);
        }
        // Insertar categorías y asociar grados
        $categorias = [
            '3p' => ['3ro de Primaria'],
            '4p' => ['4to de Primaria'],
            '5P' => ['5to de Primaria'],
            '6P' => ['6to de Primaria'],
            '1S' => ['1ro de Secundaria'],
            '2S' => ['2do de Secundaria'],
            '3S' => ['3ro de Secundaria'],
            '4S' => ['4to de Secundaria'],
            '5S' => ['5to de Secundaria'],
            '6S' => ['6to de Secundaria'],
            'Guacamayo' => ['5to de Primaria', '6to de Primaria'],
            'Guanaco' => ['1ro de Secundaria', '2do de Secundaria', '3ro de Secundaria'],
            'Londra' => ['1ro de Secundaria', '2do de Secundaria', '3ro de Secundaria'],
            'Jucumari' => ['4to de Secundaria', '5to de Secundaria', '6to de Secundaria'],
            'Bufeo' => ['1ro de Secundaria', '2do de Secundaria', '3ro de Secundaria'],
            'Puma' => ['4to de Secundaria', '5to de Secundaria', '6to de Secundaria'],
            'Builders P' => ['5to de Secundaria', '6to de Secundaria'],
            'Builders S' => ['1ro de Secundaria', '2do de Secundaria', '3ro de Secundaria', '4to de Secundaria', '5to de Secundaria', '6to de Secundaria'],
            'Lego P' => ['5to de Secundaria', '6to de Secundaria'],
            'Lego S' => ['1ro de Secundaria', '2do de Secundaria', '3ro de Secundaria', '4to de Secundaria', '5to de Secundaria', '6to de Secundaria']
        ];

        foreach ($categorias as $categoria => $grados) {
            DB::table('categoria')->updateOrInsert(['nombre' => $categoria], ['nombre' => $categoria]);
            $categoriaId = DB::table('categoria')->where('nombre', $categoria)->value('idCategoria');

            foreach ($grados as $grado) {
                $gradoId = DB::table('grado')->where('grado', $grado)->value('idGrado');
                if ($gradoId) {
                    DB::table('gradocategoria')->updateOrInsert([
                        'idGrado' => $gradoId,
                        'idCategoria' => $categoriaId
                    ]);
                }
            }
        }
        // Insertar áreas
        $areas = ['Fisica', 'Quimica', 'Matematicas', 'Informatica', 'Robotica', 'Biologia', 'Astronomia'];
        foreach ($areas as $area) {
            DB::table('area')->updateOrInsert(['nombre' => $area]);
        }
    }
}
