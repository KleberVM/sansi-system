<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TutorEstudianteInscripcion extends Model
{
    use HasFactory;
    protected $table = 'tutorestudianteinscripcion'; // Nombre real de la tabla en minúsculas
    protected $primaryKey = null; // No tiene una única clave primaria
    public $incrementing = false; // No es autoincremental

    protected $fillable = [
        'idEstudiante',
        'idTutor',
        'idInscripcion',
    ];

    public function inscripcion()
    {
        return $this->belongsTo(Inscripcion::class, 'idInscripcion', 'idInscripcion');
    }

    public function estudiante()
    {
        return $this->belongsTo(Estudiante::class, 'idEstudiante', 'id');
    }

    public function tutor()
    {
        return $this->belongsTo(Tutor::class, 'idTutor', 'id');
    }

    public function obtenerInscripcionesPorTutor($idTutor)
    {
        return $this->where('idTutor', $idTutor)
            ->with('inscripcion')  // Carga eager loading de la relación inscripcion
            ->get();
    }
}