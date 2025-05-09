<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users_games', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('game_id')->constrained()->onDelete('cascade');
            $table->string('status')->default('playing'); // 'playing', 'completed', 'dropped', 'on_hold', 'plan_to_play'
            $table->integer('rating')->nullable(); // 1-10 rating
            // $table->integer('playtime')->nullable(); // Total playtime in hours
            // $table->date('start_date')->nullable(); // Date when the user started playing the game
            // $table->date('end_date')->nullable(); // Date when the user finished the game
            // $table->boolean('is_favorite')->default(false); // Whether the game is marked as favorite by the user
            // $table->boolean('is_wishlist')->default(false); // Whether the game is marked as wishlist by the user
            // $table->boolean('is_recommended')->default(false); // Whether the game is marked as recommended by the user
            // $table->boolean('is_own')->default(false); // Whether the user owns the game
            // $table->boolean('is_playing')->default(false); // Whether the user is currently playing the game
            // $table->boolean('is_completed')->default(false); // Whether the user has completed the game
            // $table->boolean('is_dropped')->default(false); // Whether the user has dropped the game
            // $table->boolean('is_on_hold')->default(false); // Whether the user has put the game on hold
            // $table->boolean('is_plan_to_play')->default(false); // Whether the user plans to play the game
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users_games');
    }
};
