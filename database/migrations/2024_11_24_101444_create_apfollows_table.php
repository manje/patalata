<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ap_follows', function (Blueprint $table) {
            $table->id();
            $table->string('actor_id')->index(); // ID del actor (URL del usuario seguidor)
            $table->string('actor_type')->nullable(); // Tipo del actor, por ejemplo, "Person"
            $table->string('actor_preferred_username')->nullable(); // Username del seguidor
            $table->string('actor_inbox')->nullable(); // URL del inbox del seguidor
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade'); // Usuario local seguido
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ap_follows');
    }
};
