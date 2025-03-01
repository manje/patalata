<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('cache', function (Blueprint $table) {
            $table->index('expiration');
        });
    }
	

    public function down(): void
    {
        Schema::table('cache', function (Blueprint $table) {
            $table->dropIndex('cache_expiration_index');
        });
    }
};
