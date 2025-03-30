<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Area;
use App\Models\Delegacion;

class SolicitudTutor extends Model
{
    use HasFactory;
    protected $table = 'solicitudTutor';
    protected $primaryKey = 'idSolicitudTutor';
    public $timestamps = false;
    protected $fillable = [
        'nombre',
        'email',
        'password',
        'apellidoPaterno',
        'apellidoMaterno',
        'ci',
        'fechaNacimiento',
        'genero',
        'telefono',
        'comprobante',


    ];


    public function areas()
    {
        return $this->belongsToMany(Area::class, 'solicitudAreaDelegacion', 'idSolicitudTutor', 'idArea')
                    ->withPivot('idDelegacion'); // Incluye el campo idDelegacion de la tabla pivote
    }
    
    public function delegaciones()
    {
        return $this->belongsToMany(Delegacion::class, 'solicitudAreaDelegacion', 'idSolicitudTutor', 'idDelegacion')
                    ->withPivot('idArea'); // Incluye el campo idArea de la tabla pivote
    }

}
