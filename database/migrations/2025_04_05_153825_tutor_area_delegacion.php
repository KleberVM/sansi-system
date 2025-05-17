<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class TutorAreaDelegacion extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tutorareadelegacion', function (Blueprint $table) {
            $table->unsignedBigInteger('id');
            $table->unsignedBigInteger('idArea');
            $table->unsignedBigInteger('idDelegacion');
            $table->unsignedBigInteger('idConvocatoria')->nullable();
            $table->string('tokenTutor');
            $table->timestamps();

            $table->foreign('id')->references('id')->on('tutor')->onDelete('cascade');
            $table->foreign('idArea')->references('idArea')->on('area')->onDelete('cascade');
            $table->foreign('idDelegacion')->references('idDelegacion')->on('delegacion')->onDelete('cascade');
            $table->foreign('idConvocatoria')->references('idConvocatoria')->on('convocatoria')->onDelete('cascade');
            // Modificamos la clave primaria para no incluir idConvocatoria, ya que puede ser nulo
            $table->primary(['id', 'idArea', 'idDelegacion']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tutorareadelegacion');    
    }
}