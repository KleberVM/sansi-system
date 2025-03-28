<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Iu extends Migration
{

    public function up(){
        Schema::create('ius',function(Blueprint $table){
            $table->id('idIu');
            $table->string('nombreIu');
            $table->timestamps();
        });
    }


    public function down(){
        Schema::drop('ius');
    }
}

