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
        Schema::create('learning_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // The Facts (User Inputs)
            $table->boolean('struggles_memorizing')->default(false);
            $table->boolean('prefers_visual')->default(false);
            $table->boolean('weak_jawi')->default(false);
            $table->boolean('forgets_easily')->default(false);
            $table->boolean('quiz_anxiety')->default(false);
            
            // The Inferences (Expert System Outputs)
            $table->string('persona')->nullable();
            $table->json('recommendations')->nullable(); // Array of recommended actions
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('learning_profiles');
    }
};
