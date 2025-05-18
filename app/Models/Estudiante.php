<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Tutor;   

class Estudiante extends Model
{
    use HasFactory;
    protected $table = 'estudiante';
    protected $primaryKey = 'id';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = ['id'];

    public function user()
    {
        return $this->belongsTo(User::class, 'id');
    }

    public function tutores()
    {
        return $this->belongsToMany(Tutor::class, 'tutorestudianteinscripcion', 'idEstudiante', 'idTutor')
            ->withPivot('idInscripcion') // para tener acceso a ese dato
            ->withTimestamps();
    }
    
    public function inscripciones()
    {
        return $this->belongsToMany(Inscripcion::class, 'tutorestudianteinscripcion', 'idEstudiante', 'idInscripcion')
            ->withPivot('idTutor')
            ->withTimestamps();
    }
}
