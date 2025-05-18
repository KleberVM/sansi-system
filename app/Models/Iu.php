<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Funcion;

class Iu extends Model
{
    use HasFactory;
    protected $table = 'iu';
    public $timestamps = false;
    protected $primaryKey = 'idIu';
    protected $fillable = ['nombreIu'];

    public function funciones()
    {
        return $this->belongsToMany(Funcion::class, 'funcionIu', 'idIu', 'idFuncion')->withTimestamps();
    }

}
