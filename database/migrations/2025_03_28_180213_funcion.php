<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Funcion extends Migration{

    public function up(){
        Schema::create('funciones',function(Blueprint $table){
            $table->id('idFunc');
            $table->string('nombreFunc');
            $table->timestamps();
        });
       
    }

 
    public function down(){
        Schema::drop('funciones');
    }
}