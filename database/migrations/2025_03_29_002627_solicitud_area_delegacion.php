<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SolicitudAreaDelegacion extends Migration
{

    public function up()
    {
        Schema::create('solicitudAreaDelegacion', function (Blueprint $table) {
           
            $table->unsignedBigInteger('idSolicitudTutor');
            $table->unsignedBigInteger('idArea');
            $table->unsignedBigInteger('idDelegacion');

         
            $table->primary(['idSolicitudTutor', 'idArea', 'idDelegacion'], 'solicitud_area_del_pk');

            $table->foreign('idSolicitudTutor')->references('idSolicitudTutor')->on('solicitudTutor')->onDelete('cascade');
            $table->foreign('idArea')->references('idArea')->on('area')->onDelete('cascade');
            $table->foreign('idDelegacion')->references('id')->on('delegacions')->onDelete('cascade');

           
            $table->timestamps(); 
        });
    }

    public function down()
    {
        Schema::dropIfExists('solicitudAreaDelegacion');

    }
}
