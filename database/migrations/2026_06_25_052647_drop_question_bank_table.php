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
        Schema::dropIfExists('question_bank');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('question_bank', function (Blueprint $table) {
            $table->id();
            $table->text('question_text');
            $table->string('type')->default('mcq');
            $table->json('options')->nullable();
            $table->text('correct_answer');
            $table->string('topic');
            $table->string('difficulty')->default('easy');
            $table->integer('points')->default(10);
            $table->timestamps();
        });
    }
};
