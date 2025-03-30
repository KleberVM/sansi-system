<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Categoria extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(){
        Schema::create('categoria', function (Blueprint $table) {
            $table->id('idCategoria');
            $table->string('nombre');
        });
        
    }


    public function down()
    {
        Schema::dropIfExists('categoria');
        
    }
}
