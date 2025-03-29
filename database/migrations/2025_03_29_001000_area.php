<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Area extends Migration
{

    public function up(){
        Schema::create('area', function (Blueprint $table) {
            $table->id('idArea');
            $table->string('nombre');
            $table->string('descripcion');
        });
        
    }


    public function down()
    {
        Schema::dropIfExists('area');
        
    }
}
