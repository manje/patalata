<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('timelines', function (Blueprint $table) {
            $table->id(); // Llave primaria
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade'); // Relación opcional con users
            $table->foreignId('team_id')->nullable()->constrained()->onDelete('cascade'); // Relación opcional con teams
            // VARCHAR de 255 caracteres para el tipo de actividad
            $table->string('activity');
            $table->timestamps(); // created_at y updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('timelines');
    }
};
