<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SolicitudAreaDelegacion extends Migration
{

    public function up()
    {
        Schema::create('solicitudAreaDelegacion', function (Blueprint $table) {
   
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('solicitudAreaDelegacion');

    }
}
