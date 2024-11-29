<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /* el actor es una URI */
        // Seguidores
        Schema::create('apfollowers', function (Blueprint $table) {
            $table->id();
            $table->string('actor_id')->index(); // ID del actor (URL del usuario seguidor)
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade'); // Usuario local seguido
            $table->timestamps();
        });
        // Siguiendo
        Schema::create('apfollowings', function (Blueprint $table) {
            $table->id();
            $table->string('actor_id')->index(); // ID del actor (URL del usuario seguidor)
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade'); // Usuario local seguido
            $table->boolean('accept')->default(false); // Aceptado
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('apfollowers');
        Schema::dropIfExists('apfollowings');
    }
};
