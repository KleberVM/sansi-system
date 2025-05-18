<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GrupoInscripcion extends Model
{
    use HasFactory;
    //protected $table = 'grupo_inscripcions'; //ESTA FILA DEBERIA EXISTIR, PERO NO SE ENCUENTRA EN EL CÓDIGO ORIGINAL
    protected $fillable = [
        'codigoInvitacion',
        'nombreGrupo',
        'modalidad',
        'estado',
        'idDelegacion'
    ];

    protected $attributes = [
        'estado' => 'incompleto'
    ];

    // Relación directa con delegación
    public function delegacion()
    {
        return $this->belongsTo(Delegacion::class, 'idDelegacion', 'idDelegacion');
    }

    // Un grupo tiene muchos detalles de inscripción
    public function detallesInscripcion()
    {
        return $this->hasMany(DetalleInscripcion::class, 'idGrupoInscripcion');
    }

    // Método para obtener grupos por nombre de delegación
    public static function porDelegacion($nombreDelegacion)
    {
        return self::whereHas('delegacion', function($query) use ($nombreDelegacion) {
            $query->where('nombre', 'like', "%{$nombreDelegacion}%");
        });
    }

    // Método para crear un nuevo grupo para una delegación
    public static function crearParaDelegacion($datos, $idDelegacion)
    {
        return self::create([
            'codigoInvitacion' => $datos['codigoInvitacion'] ?? uniqid(),
            'nombreGrupo' => $datos['nombreGrupo'],
            'modalidad' => $datos['modalidad'],
            'estado' => 'incompleto',
            'idDelegacion' => $idDelegacion
        ]);
    }

    // Scopes para filtrar por estado
    public function scopeActivos($query)
    {
        return $query->where('estado', 'activo');
    }

    public function scopeIncompletos($query)
    {
        return $query->where('estado', 'incompleto');
    }

    public function scopeCancelados($query)
    {
        return $query->where('estado', 'cancelado');
    }
}
