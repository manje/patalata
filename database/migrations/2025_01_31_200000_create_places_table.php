<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('places', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nombre del lugar
            $table->bigInteger('geonames_id')->nullable()->unique(); // ID en GeoNames
            $table->decimal('latitude', 10, 7); // Latitud con precisión suficiente
            $table->decimal('longitude', 10, 7); // Longitud con precisión suficiente
            $table->integer('radius')->default(10); // Radio en km (entero)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('places');
    }
};
