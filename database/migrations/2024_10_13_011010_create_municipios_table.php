<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMunicipiosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('municipios', function (Blueprint $table) {
            $table->id();
            $table->char('cpro', 2); // Código de provincia
            $table->char('cmun', 3); // Código de municipio
            $table->string('nombre'); // Nombre del municipio
            $table->timestamps();

            $table->index(['cpro', 'cmun']); // Índice para buscar por provincia y municipio
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('municipios');
    }
}
