<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rol extends Model
{
    use HasFactory;
    protected $table = 'rols';
    protected $primaryKey ='idRol';
    public $timestamps = true;
    protected $fillable = ['nombreRol','activoRol'];


    public function funciones()
    {
        return $this->belongsToMany(Funcion::class, 'rolFuncion', 'idRol', 'idFunc');
    }
}
