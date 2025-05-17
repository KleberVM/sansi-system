<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class TutorEstudianteInscripcion extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tutorestudianteinscripcion', function (Blueprint $table) {
            $table->unsignedBigInteger('idEstudiante');
            $table->unsignedBigInteger('idTutor');
            $table->unsignedBigInteger('idInscripcion');

            $table->foreign('idEstudiante')->references('id')->on('estudiante')->onDelete('cascade');
            $table->foreign('idTutor')->references('id')->on('tutor')->onDelete('cascade');
            $table->foreign('idInscripcion')->references('idInscripcion')->on('inscripcion')->onDelete('cascade');

            $table->primary(['idEstudiante', 'idTutor', 'idInscripcion'], 'tutor_estu_insc_pk');
            $table->timestamps();    
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tutorestudianteinscripcion');
    }
}