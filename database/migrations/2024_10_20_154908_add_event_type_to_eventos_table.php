<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('eventos', function (Blueprint $table) {
            $table->foreignId('event_type_id')->nullable()->constrained('event_types')->after('ip');
        });
    }

    public function down()
    {
        Schema::table('eventos', function (Blueprint $table) {
            $table->dropForeign(['event_type_id']);
            $table->dropColumn('event_type_id');
        });
    }
};

