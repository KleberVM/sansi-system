<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Rol extends Migration{
   
    public function up(){
        Schema::create('rols',function(Blueprint $table){
            $table->id('idRol');
            $table->string('nombreRol');
            $table->boolean('activoRol');
            $table->timestamps();
        });
    }


    
    public function down(){
        Schema::drop('rols');
    }
}