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
        Schema::create('flashcard_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('flashcard_id')->constrained()->cascadeOnDelete();
            
            // SM-2 Spaced Repetition Fields
            $table->float('ease_factor')->default(2.5); // Starts at 2.5
            $table->integer('interval')->default(0); // Days until next review
            $table->integer('repetitions')->default(0); // Consecutive correct answers
            $table->timestamp('next_review_date')->nullable(); // When it's due next
            
            $table->timestamps();
            
            // A user can only have one progress record per flashcard
            $table->unique(['user_id', 'flashcard_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('flashcard_progress');
    }
};
