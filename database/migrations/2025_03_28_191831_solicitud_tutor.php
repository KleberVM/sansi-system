<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SolicitudTutor extends Migration{

    public function up(){
        Schema::create('solicitudTutor', function (Blueprint $table) {
            $table->id('idSolicitudTutor');
            $table->string('nombre');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('apellidoPaterno');
            $table->string('apellidoMaterno');
            $table->string('ci');
            $table->date('fechaNacimiento');  
            $table->char('genero');
            $table->integer('telefono');
            $table->string('comprobante');
        });
    }

   
    public function down(){
        Schema::dropIfExists('solicitudTutor');
    }
}
