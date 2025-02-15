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
	    Schema::create('articles', function (Blueprint $table) {
	        $table->id();
	        $table->foreignId('user_id')->constrained()->cascadeOnDelete();
	        $table->foreignId('team_id')->nullable()->constrained()->cascadeOnDelete();
	        $table->foreignId('place_id')->nullable()->constrained('municipios');
	        $table->string('name');
	        $table->text('summary')->nullable();
			$table->text('content');
	        $table->string('slug')->unique();
            $table->string('ip', 45)->nullable(); // Para soportar IPv4 e IPv6;
	        $table->timestamps();
	    });
	}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
