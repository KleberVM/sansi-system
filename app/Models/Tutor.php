<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tutor extends Model
{
    use HasFactory;
    protected $table = 'tutor';
    protected $primaryKey = 'id';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id',
        'profesion',
        'telefono',
        'linkRecurso',
        'tokenTutor',
        'es_director',
        'estado',
    ];
    protected $casts = [
        'es_director' => 'boolean',
    ];
    public function user()
    {
        return $this->belongsTo(User::class, 'id');
    }

    public function tutorAreaDelegacion()
    {
        return $this->hasOne(TutorAreaDelegacion::class, 'id');
    }
    public function areas()
    {
        return $this->belongsToMany(Area::class, 'tutorareadelegacion', 'id', 'idArea')
            ->withPivot('idDelegacion', 'idConvocatoria', 'tokenTutor')
            ->withTimestamps();
    }

    public function areasSimple()
    {
        return $this->belongsToMany(Area::class, 'tutorareadelegacion', 'id', 'idArea')
            ->select('area.idArea', 'area.nombre');
    }
    public function delegaciones()
    {
        return $this->belongsToMany(Delegacion::class, 'tutorareadelegacion', 'id', 'idDelegacion')
            ->withPivot('idArea', 'idConvocatoria', 'tokenTutor')
            ->withTimestamps();
    }

    public function convocatorias()
    {
        return $this->belongsToMany(Convocatoria::class, 'tutorareadelegacion', 'id', 'idConvocatoria')
            ->withPivot('idArea', 'idDelegacion', 'tokenTutor')
            ->withTimestamps();
    }
    /**
     * Obtener áreas para una convocatoria específica
     */
    public function areasPorConvocatoria($idConvocatoria)
    {
        if (empty($idConvocatoria)) {
            \Illuminate\Support\Facades\Log::warning('Se intentó obtener áreas con ID de convocatoria vacío');
            return $this->belongsToMany(Area::class, 'tutorareadelegacion', 'id', 'idArea')
                ->whereRaw('1 = 0'); // Retorna una relación vacía
        }

        // Registrar información para depuración
        \Illuminate\Support\Facades\Log::info('Obteniendo áreas para Tutor ID: ' . $this->id . ' y Convocatoria ID: ' . $idConvocatoria);

        return $this->belongsToMany(Area::class, 'tutorareadelegacion', 'id', 'idArea')
            ->wherePivot('idConvocatoria', $idConvocatoria)
            ->withPivot('idDelegacion', 'tokenTutor')
            ->withTimestamps();
    }

    public function estudiantes()
    {
        return $this->belongsToMany(Estudiante::class, 'tutorestudianteinscripcion', 'idTutor', 'idEstudiante')
            ->withPivot('idInscripcion')
            ->withTimestamps();
    }

    // ...existing code...

    public function primerIdDelegacion($idConvocatoria = null)
    {
        $query = $this->belongsToMany(Delegacion::class, 'tutorareadelegacion', 'id', 'idDelegacion')
            ->select('delegacion.idDelegacion');

        // Si se proporciona ID de convocatoria, filtrar por esa convocatoria
        if ($idConvocatoria) {
            $query->wherePivot('idConvocatoria', $idConvocatoria);
        }

        return $query->first()->idDelegacion ?? null;
    }

    public function getColegio($idConvocatoria = null)
    {
        $delegacion = $this->primerIdDelegacion($idConvocatoria);
        $response = $delegacion = Delegacion::find($delegacion);
        return $response ? $response->nombre : 'No asignado';
    }
}
