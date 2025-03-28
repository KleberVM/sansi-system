<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RolFuncion extends Migration
{
    public function up(){
        Schema::create('rolFuncion', function (Blueprint $table) {

            $table->unsignedBigInteger('idFunc');
            $table->unsignedBigInteger('idRol');
            $table->timestamps();

            $table->foreign('idFunc')->references('idFunc')->on('funciones')->onDelete('cascade');
            $table->foreign('idRol')->references('idRol')->on('rols')->onDelete('cascade');
            
            $table->primary(['idFunc', 'idRol']);
        });
    }

 
    public function down(){
        Schema::drop('rolFuncion');
    }
}
