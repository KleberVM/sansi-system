<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BoletaPagoInscripcion extends Model
{
    use HasFactory;
    protected $table = 'boletapagoinscripcion'; // nombre de la tabla
    protected $fillable = [
        'idInscripcion',
        'idBoleta',
    ];
    //public $timestamps = true;


    public function inscripcion()
    {
        return $this->belongsTo(Inscripcion::class, 'idInscripcion', 'idInscripcion');
    }

    public function boletaPago()
    {
        return $this->belongsTo(BoletaPago::class, 'idBoleta', 'idBoleta'); // UpperCamelCase para el modelo
    }
}
