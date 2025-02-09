<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**

    Offer{Join}: “Bob offers to join the Project / Group” (an event: the offer stands)
    Accept{Join}: “Alice accepts Bob’s offer to join” (event too: accepted)
    Join{Person}: “Project / Group states that Bob joined as member” (yep, an event)

     */
    public function up(): void
    {
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->foreignId('place_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('team_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('profile_image')->nullable();
            $table->string('image')->nullable();
            $table->text('summary');
            $table->text('content');
            $table->text('public_key')->nullable();
            $table->text('private_key')->nullable();
            $table->timestamps();
        });
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->string('actor');
            $table->string('object');
            $table->enum('role',['admin','editor'])->default('editor');
            $table->enum('status',['admin','editor','Join','Invite'])->default('Join');
            $table->timestamps();
            $table->unique(['actor','object']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaigns');
        Schema::dropIfExists('members');
    }
};
