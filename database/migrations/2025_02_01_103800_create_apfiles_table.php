<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('apfiles', function (Blueprint $table) {
            $table->id();
            $table->string('file_path'); // Ruta del archivo en el storage
            $table->string('file_type'); // Tipo de archivo (imagen, audio, video)
            $table->text('alt_text')->nullable(); // Texto alternativo (para imágenes)
            $table->morphs('apfileable'); // Relación polimórfica
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('apfiles');
    }
};
