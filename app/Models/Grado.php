<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Grado extends Model
{
    use HasFactory;
    protected $table = 'grado';
    protected $primaryKey = 'idGrado';
    protected $fillable = [
        'grado',
    ];
    public function categorias(){
        return $this->belongsToMany(Categoria::class, 'gradoCategoria','idGrado','idCategoria');
    }
}