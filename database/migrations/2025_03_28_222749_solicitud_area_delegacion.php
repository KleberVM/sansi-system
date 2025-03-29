<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SolicitudAreaDelegacion extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('solicitudAreaDelegacion', function (Blueprint $table) {

            $table->unsignedBigInteger('idSolicitudTutor');
            $table->unsignedBigInteger('idArea');
            $table->unsignedBigInteger('idDelegacion');
            $table->boolean('estado')->default(0);

            $table->foreign('idSolicitudTutor')->references('idSolicitudTutor')->on('solicitudTutor')->onDelete('cascade');
            $table->foreign('idArea')->references('idArea')->on('idArea')->onDelete('area');
            $table->foreign('idDelegacion')->references('idDelegacion')->on('delegacion')->onDelete('area');
            
            $table->primary(['idSolicitudTutor', 'idArea'.'idDelegacion']);
        });
    
    }

 
    public function down()
    {
        Schema::dropIfExists('solicitudAreaDelegacion');
    }
}
