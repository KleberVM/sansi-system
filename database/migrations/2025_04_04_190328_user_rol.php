<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UserRol extends Migration
{
    public function up()
    {
        Schema::create('userrol', function (Blueprint $table) {

            $table->unsignedBigInteger('id');
            $table->unsignedBigInteger('idRol');
            $table->timestamps();
            $table->boolean('habilitado')->default(false);

            $table->foreign('id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('idRol')->references('idRol')->on('rol')->onDelete('cascade');
            
            $table->primary(['id', 'idRol']);
        });
    }

    public function down()
    {
        Schema::drop('userrol');
    }
}
