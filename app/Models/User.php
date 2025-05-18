<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Rol;
use App\Models\Estudiante;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'apellidoPaterno',
        'apellidoMaterno',
        'ci',
        'fechaNacimiento',
        'genero',
        'email_verified_at',
    ];

    public function roles(){
        return $this->belongsToMany(Rol::class, 'userrol', 'id', 'idRol')->withPivot('habilitado');;
    }


    public function estudiante()
    {
        return $this->hasOne(Estudiante::class, 'id');
    }

public function tutor()
    {
        return $this->hasOne(Tutor::class, 'id');
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
}
