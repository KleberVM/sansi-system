<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Rol;
use App\Models\Iu;  

class Funcion extends Model
{
    use HasFactory;
    protected $table = 'funcion';
    protected $primaryKey = 'idFuncion';
    public $timestamps = false;
    protected $fillable = ['nombre'];

    public function roles()
    {
        return $this->belongsToMany(Rol::class, 'rolFuncion', 'idFuncion', 'idRol')->withTimestamps();

    }

    public function ius(){
        return $this->belongsToMany(Iu::class, 'funcionIu', 'idFuncion', 'idIu')->withTimestamps();
    }
}
