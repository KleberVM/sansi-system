<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Delegacion extends Model
{
    use HasFactory;
    protected $table = 'delegacions';
    protected $fillable = [
        'codigo_sie', 'nombre', 'dependencia', 'departamento', 'provincia', 
        'municipio', 'zona', 'direccion', 'telefono', 'responsable_nombre', 'responsable_email'
    ];
}
