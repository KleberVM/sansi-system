<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class GradoCategoria extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gradocategoria', function (Blueprint $table) {

            $table->unsignedBigInteger('idGrado');
            $table->unsignedBigInteger('idCategoria');
            $table->timestamps();

            $table->foreign('idGrado')->references('idGrado')->on('grado')->onDelete('cascade');
            $table->foreign('idCategoria')->references('idCategoria')->on('categoria')->onDelete('cascade');
            
            $table->primary(['idGrado', 'idCategoria']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('gradocategoria');

    }
}
