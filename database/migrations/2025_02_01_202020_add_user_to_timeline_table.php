<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('timelines', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['team_id']);
            $table->dropColumn(['user_id', 'team_id']);
            $table->string('user', 250)->after('activity')->nullable();
        });
    }

    public function down()
    {
        Schema::table('timelines', function (Blueprint $table) {
            $table->bigInteger('user_id')->unsigned()->nullable()->after('id');
            $table->bigInteger('team_id')->unsigned()->nullable()->after('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('team_id')->references('id')->on('teams')->onDelete('cascade');
            $table->dropColumn('user');
        });
    }
};
