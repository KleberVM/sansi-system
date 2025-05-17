<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Convocatoria extends Migration
{
     /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(){
        Schema::create('convocatoria', function (Blueprint $table) {
            $table->id('idConvocatoria');
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->date('fechaInicio');
            $table->date('fechaFin');
            $table->string('contacto');
            $table->string('requisitos',300);
            $table->string('metodoPago');
            $table->enum('estado', ['Borrador', 'Publicada', 'Cancelada','Finalizado'])->default('Borrador');
            $table->timestamps();
        });
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('convocatoria');
        
    }
}
