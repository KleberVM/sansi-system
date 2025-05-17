<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Convocatoria extends Model
{
    use HasFactory;
    
    protected $table = 'convocatoria';
    protected $primaryKey = 'idConvocatoria';
    
    protected $fillable = [
        'nombre',
        'descripcion',
        'fechaInicio',
        'fechaFin',
        'contacto',
        'requisitos',
        'metodoPago',
        'estado',
    ];
    
    public $timestamps = true;
      /**
     * Relación muchos a muchos con tutores a través de tutorAreaDelegacion
     */
    public function tutores()
    {
        return $this->belongsToMany(Tutor::class, 'tutorareadelegacion', 'idConvocatoria', 'id')
                    ->withPivot('idArea', 'idDelegacion', 'tokenTutor')
                    ->withTimestamps();
    }
    
    /**
     * Obtener las áreas relacionadas con esta convocatoria a través de los tutores
     */
    public function areas()
    {
        return $this->hasManyThrough(
            Area::class,
            tutorareadelegacion::class,
            'idConvocatoria', // Clave externa en tutorAreaDelegacion
            'idArea',         // Clave externa en area
            'idConvocatoria', // Clave local en convocatoria
            'idArea'          // Clave local en tutorAreaDelegacion
        );
    }

    public function convocatoriaAreaCategorias()
    {
        return $this->hasMany(ConvocatoriaAreaCategoria::class, 'idConvocatoria', 'idConvocatoria');
    }
}
