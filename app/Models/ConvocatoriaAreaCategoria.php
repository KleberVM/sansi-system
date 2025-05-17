<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConvocatoriaAreaCategoria extends Model
{
    use HasFactory;

    protected $table = 'convocatoriaareacategoria';
    protected $primaryKey = null;
    public $incrementing = false;

    protected $fillable = [
        'idConvocatoria',
        'idArea',
        'idCategoria',
        'precioIndividual',
        'precioDuo',
        'precioEquipo',
    ];

    public function convocatoria()
    {
        return $this->belongsTo(Convocatoria::class, 'idConvocatoria', 'idConvocatoria');
    }

    public function area()
    {
        return $this->belongsTo(Area::class, 'idArea', 'idArea');
    }

    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'idCategoria', 'idCategoria');
    }
}

