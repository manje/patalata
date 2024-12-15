<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use phpseclib3\Crypt\RSA;

return new class extends Migration
{


    public function up()
    {
        Schema::table('timelines', function (Blueprint $table) {
            $table->string('actor_id')->after('activity');
        });
    }

    public function down()
    {
        Schema::table('timelines', function (Blueprint $table) {
            $table->dropColumn(['actor_id']);
        });
    }


};
