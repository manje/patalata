<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	/**
	 * Run the migrations.
	 */
	

	public function up()
	{
	    Schema::create('posts', function (Blueprint $table) {
	        $table->id();
	        $table->foreignId('user_id')->constrained()->cascadeOnDelete();
	        $table->foreignId('team_id')->nullable()->constrained()->cascadeOnDelete();
	        $table->foreignId('municipio_id')->constrained('municipios');
	        $table->string('name');
	        $table->text('content')->nullable();
	        $table->string('cover')->nullable();
	        $table->string('slug')->unique();
            $table->string('ip', 45)->nullable(); // Para soportar IPv4 e IPv6;
	        $table->timestamps();
	    });
        Schema::create('post_category', function (Blueprint $table) {
            $table->foreignId('post_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
	}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
        Schema::dropIfExists('post_category');
    }
};
