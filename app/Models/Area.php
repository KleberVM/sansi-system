<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    use HasFactory;
    protected $table = 'area';
    protected $primaryKey = 'idArea';
    public $timestamps = false;
    protected $fillable = [
        'nombre',
    ];

    public function tutores()
    {
        return $this->belongsToMany(Tutor::class, 'tutorAreaDelegacion', 'idArea', 'id');
    }

    public function convocatoriaAreaCategorias()
    {
        return $this->hasMany(ConvocatoriaAreaCategoria::class, 'idArea', 'idArea');
    }
}
