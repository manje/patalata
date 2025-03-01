<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('announces', function (Blueprint $table) {
            $table->index('actor');
        });
        Schema::table('apfollowers', function (Blueprint $table) {
            $table->index('actor');
        });
        Schema::table('apfollowings', function (Blueprint $table) {
            $table->index('actor');
        });
        Schema::table('blocks', function (Blueprint $table) {
            $table->index('actor');
        });
        Schema::table('likes', function (Blueprint $table) {
            $table->index('actor');
            $table->index('object');
        });
        Schema::table('members', function (Blueprint $table) {
            $table->index('actor');
            $table->index('object');
        });
        Schema::table('outboxes', function (Blueprint $table) {
            $table->index('actor');
            $table->index('object');
        });
        Schema::table('timelines', function (Blueprint $table) {
            $table->index('actor_id');
        });
    }
	

    public function down(): void
    {
        Schema::table('announces', function (Blueprint $table) {
            $table->dropIndex('announces_actor_index');
        });
        Schema::table('apfollowers', function (Blueprint $table) {
            $table->dropIndex('apfollowers_actor_index');
        });
        Schema::table('apfollowings', function (Blueprint $table) {
            $table->dropIndex('apfollowings_actor_index');
        });
        Schema::table('blocks', function (Blueprint $table) {
            $table->dropIndex('blocks_actor_index');
        });
        Schema::table('likes', function (Blueprint $table) {
            $table->dropIndex('likes_actor_index');
            $table->dropIndex('likes_object_index');
        });
        Schema::table('members', function (Blueprint $table) {
            $table->dropIndex('members_actor_index');
            $table->dropIndex('members_object_index');
        });
        Schema::table('outboxes', function (Blueprint $table) {
            $table->dropIndex('outboxes_actor_index');
            $table->dropIndex('outboxes_object_index');
        });
        Schema::table('timelines', function (Blueprint $table) {
            $table->dropIndex('timelines_actor_id_index');
        });
    }
};
