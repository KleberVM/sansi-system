<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FuncionIu extends Migration
{
    public function up()
    {
        Schema::create('funcionIu', function (Blueprint $table) {

            $table->unsignedBigInteger('idFunc');
            $table->unsignedBigInteger('idIu');
            $table->timestamps();

            $table->foreign('idFunc')->references('idFunc')->on('funciones')->onDelete('cascade');
            $table->foreign('idIu')->references('idIu')->on('ius')->onDelete('cascade');
            
            $table->primary(['idFunc', 'idIu']);
        });
    }


    public function down()
    {
        Schema::drop('funcionIu');
    }
}
