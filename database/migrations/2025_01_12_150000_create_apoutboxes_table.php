<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('outboxes', function (Blueprint $table) {
            $table->id(); // Llave primaria
            $table->string('actor');
            $table->string('object');
            $table->timestamps(); // created_at y updated_at
            $table->unique(['actor', 'object'], 'unique_actor_object');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('outboxes');
    }
};
