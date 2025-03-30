<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Categoria extends Model
{
    use HasFactory;
    protected $table = 'categoria';
    protected $primaryKey = 'idCategoria';
    protected $fillable = [
        'nombre',
    ];

    public function grados(){
        return $this->belongsToMany(Grado::class, 'gradoCategoria', 'idCategoria', 'idGrado');
    }
}