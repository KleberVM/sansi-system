<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class ConvocatoriaSeeder extends Seeder
{
    public function run()
    {
        $fechaInicio = Carbon::now();
        $fechaFin = $fechaInicio->copy()->addDays(10);

        // Relacionar convocatoria con áreas y categorías
        $estructuraConvocatorias = [
            [
                'datos' => [
                    'nombre' => 'Convocatoria Olimpiada 2024',
                    'descripcion' => 'Participa en la Olimpiada Científica de este año.',
                    'fechaInicio' => '2024-05-01',
                    'fechaFin' => '2024-06-15',
                    'contacto' => 'contacto@olimpiada.edu.bo',
                    'requisitos' => 'Ser estudiante regular. Presentar fotocopia de CI.',
                    'metodoPago' => 'Transferencia bancaria',
                    'estado' => 'Borrador',
                ],
                'relaciones' => [
                    'Matematicas' => ['3p', '4p', '5P'],
                    'Fisica' => ['1S', '2S'],
                ]
            ],
            [
                'datos' => [
                    'nombre' => 'Convocatoria Departamental 2024',
                    'descripcion' => 'Evento departamental clasificatorio.',
                    'fechaInicio' => '2024-04-01',
                    'fechaFin' => '2024-05-10',
                    'contacto' => 'dep.eventos@edu.bo',
                    'requisitos' => 'Autorización del colegio. Fotocopia de RUDE.',
                    'metodoPago' => 'Pago en caja',
                    'estado' => 'Borrador',
                ],
                'relaciones' => [
                    'Biologia' => ['4S', '5S'],
                    'Quimica' => ['3S', '4S', '5S'],
                ]
            ],
            [
                'datos' => [
                    'nombre' => 'Preinscripción Olimpiada 2025',
                    'descripcion' => 'Fase de preinscripción para la siguiente gestión.',
                    'fechaInicio' => $fechaInicio->toDateString(),
                    'fechaFin' => $fechaFin->toDateString(),
                    'contacto' => 'preinscripcion@olimpiada.bo',
                    'requisitos' => 'Formulario de preinscripción completo.',
                    'metodoPago' => 'Sin costo',
                    'estado' => 'Publicada',
                ],
                'relaciones' => [
                    'Informatica' => ['Londra', 'Bufeo'],
                    'Robotica' => ['Guacamayo'],
                    'Fisica' => ['3p','4p','5P','6P','Lego S'],
                    'Biologia' => ['Builders S','Puma'],
                    'Quimica' => ['Builders S','Jucumari'],
                    'Astronomia' => ['Builders S'],
                    'Matematicas' => ['Lego S', 'Lego P' , 'Jucumari'],
                ]
            ],
            [
                'datos' => [
                    'nombre' => 'Convocatoria Matemáticas Avanzadas',
                    'descripcion' => 'Dirigido a estudiantes de nivel avanzado en matemáticas.',
                    'fechaInicio' => '2024-07-01',
                    'fechaFin' => '2024-08-01',
                    'contacto' => 'matematicas@olimpiada.bo',
                    'requisitos' => 'Prueba diagnóstica previa.',
                    'metodoPago' => 'QR de Banco Unión',
                    'estado' => 'Borrador',
                ],
                'relaciones' => [
                    'Matematicas' => ['5S', '6S', 'Puma'],
                ]
            ],
            [
                'datos' => [
                    'nombre' => 'Convocatoria Cancelada de Prueba',
                    'descripcion' => 'Convocatoria de prueba cancelada por falta de participantes.',
                    'fechaInicio' => '2023-09-01',
                    'fechaFin' => '2023-10-01',
                    'contacto' => 'prueba@olimpiada.bo',
                    'requisitos' => 'Ninguno',
                    'metodoPago' => 'Sin costo',
                    'estado' => 'Cancelada',
                ],
                'relaciones' => [] // sin relaciones
            ]
        ];

        foreach ($estructuraConvocatorias as $conv) {
            // Insertar convocatoria
            $datos = $conv['datos'];
            $datos['created_at'] = now();
            $datos['updated_at'] = now();

            $idConvocatoria = DB::table('convocatoria')->insertGetId($datos);


            // Insertar relaciones con áreas y categorías
            foreach ($conv['relaciones'] as $nombreArea => $categorias) {
                $idArea = DB::table('area')->where('nombre', $nombreArea)->value('idArea');
                if (!$idArea) continue;

                foreach ($categorias as $nombreCategoria) {
                    $idCategoria = DB::table('categoria')->where('nombre', $nombreCategoria)->value('idCategoria');
                    if (!$idCategoria) continue;

                    DB::table('convocatoriaareacategoria')->insert([
                        'idConvocatoria' => $idConvocatoria,
                        'idArea' => $idArea,
                        'idCategoria' => $idCategoria,
                        'precioIndividual' => mt_rand(80, 300),
                        'precioDuo' => mt_rand(80, 300),// Precio aleatorio realista
                        'precioEquipo' => mt_rand(80, 300),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }
}
