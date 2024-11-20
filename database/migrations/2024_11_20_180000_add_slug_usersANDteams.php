<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        Schema::table('users', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('name'); // Sin restricción de unique
        });

        Schema::table('teams', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('name'); // Sin restricción de unique
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('slug');
        });

        Schema::table('teams', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
};
