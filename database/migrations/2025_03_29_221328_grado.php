<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Grado extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(){
        Schema::create('grado', function (Blueprint $table) {
            $table->id('idGrado');
            $table->string('grado');
        });
        
    }


    public function down()
    {
        Schema::dropIfExists('grado');
        
    }
}
