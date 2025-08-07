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
	    Schema::create('replies', function (Blueprint $table) {
	        $table->id();
	        $table->string('object');
	        $table->string('reply');
	        $table->timestamps();
	        $table->unique(['object', 'reply']);
	    });
	}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('replies');
    }
};
