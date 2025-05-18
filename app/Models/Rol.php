<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Funcion; 

class Rol extends Model
{
    use HasFactory;
    protected $table = 'rol';
    protected $primaryKey ='idRol';
    public $timestamps = false;
    protected $fillable = ['nombre'];


    public function usuarios()
    {
        return $this->belongsToMany(User::class, 'userrol', 'idRol', 'id')->withTimestamps();
    }
    public function funciones()
    {
        return $this->belongsToMany(Funcion::class, 'rolFuncion', 'idRol', 'idFuncion');
    }
}
