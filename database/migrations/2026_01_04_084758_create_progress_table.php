<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up()
{
    Schema::create('progress', function (Blueprint $table) {
        $table->id();
        
        // This MUST point to the quizzes table and use the same type
        $table->foreignId('quiz_id')->constrained('quizzes')->onDelete('cascade');
        
        // Ensure student_id is also correct
        $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
        
        $table->integer('score');
        $table->timestamps();
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('progress');
    }
};
