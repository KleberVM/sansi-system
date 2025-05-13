<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ConvocatoriaAreaCategoria extends Migration
{
    public function up()
    {
        Schema::create('convocatoriaareacategoria', function (Blueprint $table) {

            $table->unsignedBigInteger('idConvocatoria');
            $table->unsignedBigInteger('idArea');
            $table->unsignedBigInteger('idCategoria');
            $table->decimal('precioIndividual', 8, 2)->nullable();
            $table->decimal('precioDuo', 8, 2)->nullable();
            $table->decimal('precioEquipo', 8, 2)->nullable();
            $table->timestamps();

            $table->foreign('idConvocatoria')->references('idConvocatoria')->on('convocatoria')->onDelete('cascade');
            $table->foreign('idArea')->references('idArea')->on('area')->onDelete('cascade');
            $table->foreign('idCategoria')->references('idCategoria')->on('categoria')->onDelete('cascade');
            
            $table->primary(['idConvocatoria', 'idArea', 'idCategoria'], 'convocatoria_area_categoria_pk');
        });
    }

    
    public function down()
    {
        Schema::dropIfExists('convocatoriaareacategoria');
    }
}
