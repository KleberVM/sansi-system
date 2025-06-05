<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Area;
use App\Models\Categoria;

class Inscripcion extends Model
{
    use HasFactory;

    protected $table = 'inscripcion';
    protected $primaryKey = 'idInscripcion';
    public $timestamps = true;

    protected $fillable = [
        'fechaInscripcion',
        'numeroContacto',
        'status',
        'idGrado',
        'idConvocatoria',
        'idDelegacion',
        'nombreApellidosTutor',
        'correoTutor',
    ];

    public function grado()
    {
        return $this->belongsTo(Grado::class, 'idGrado', 'idGrado');
    }

    public function convocatoria()
    {
        return $this->belongsTo(Convocatoria::class, 'idConvocatoria', 'idConvocatoria');
    }

    public function delegacion()
    {
        return $this->belongsTo(Delegacion::class, 'idDelegacion', 'idDelegacion');
    }

    public function tutores()
    {
        return $this->belongsToMany(Tutor::class, 'tutorestudianteinscripcion', 'idInscripcion', 'idTutor')
            ->withPivot('idEstudiante')
            ->withTimestamps();
    }

    public function estudiantes()
    {
        return $this->belongsToMany(Estudiante::class, 'tutorestudianteinscripcion', 'idInscripcion', 'idEstudiante')
            ->withPivot('idTutor')
            ->withTimestamps();
    }

    public function detalles()
    {
        return $this->hasMany(DetalleInscripcion::class, 'idInscripcion', 'idInscripcion');
    }

    public function area()
    {
        return $this->hasOneThrough(
            Area::class,
            DetalleInscripcion::class,
            'idInscripcion', // Clave foránea en DetalleInscripcion
            'idArea', // Clave primaria en Area
            'idInscripcion', // Clave local en Inscripcion
            'idArea' // Clave foránea en DetalleInscripcion que apunta a Area
        );
    }

    public function categoria()
    {
        return $this->hasOneThrough(
            Categoria::class,
            DetalleInscripcion::class,
            'idInscripcion', // Clave foránea en DetalleInscripcion
            'idCategoria', // Clave primaria en Categoria
            'idInscripcion', // Clave local en Inscripcion
            'idCategoria' // Clave foránea en DetalleInscripcion que apunta a Categoria
        );
    }

    public function tutoresEstudiantes()
    {
        return $this->hasMany(\App\Models\TutorEstudianteInscripcion::class, 'idInscripcion', 'idInscripcion');
    }
}

