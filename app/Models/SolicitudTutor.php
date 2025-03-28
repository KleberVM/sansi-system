<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SolicitudTutor extends Model
{
    use HasFactory;
    protected $table = 'solicitudTutor';
    protected $primaryKey = 'idSolicitudTutor';
    protected $fillable = [
        'name',
        'email',
        'password',
        'apellidoPaterno',
        'apellidoMaterno',
        'ci',
        'fechaNacimiento',
        'genero',
        'comprobante',
        'telefono',

    ];

}
