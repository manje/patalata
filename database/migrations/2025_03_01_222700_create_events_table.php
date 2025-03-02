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
	    Schema::create('events', function (Blueprint $table) {
	        $table->id();
	        $table->foreignId('user_id')->constrained()->cascadeOnDelete();
	        $table->foreignId('team_id')->nullable()->constrained()->cascadeOnDelete();
	        $table->foreignId('place_id')->nullable()->constrained('municipios');
	        $table->string('name');
	        $table->text('summary')->nullable();
			$table->text('content')->nullable();
			$table->date('startTime');
			$table->date('endTime')->nullable();
			$table->geography('coordinates', subtype: 'point', srid: 4326);
			$table->string('location_name')->nulable();
			$table->string('location_addressCountry')->nulable();
			$table->string('location_addressLocality')->nulable();
			$table->string('location_addressRegion')->nulable();
			$table->string('location_postalCode')->nulable();
			$table->string('location_streetAddress')->nulable();
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
        Schema::dropIfExists('events');
    }
};
