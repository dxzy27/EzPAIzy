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
        Schema::table('question_bank', function (Blueprint $table) {
            $table->text('question_text')->after('id');
            $table->string('type')->default('mcq')->after('question_text');
            $table->json('options')->nullable()->after('type');
            $table->text('correct_answer')->after('options');
            $table->string('topic')->after('correct_answer');
            $table->string('difficulty')->default('easy')->after('topic');
            $table->integer('points')->default(10)->after('difficulty');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('question_bank', function (Blueprint $table) {
            $table->dropColumn(['question_text', 'type', 'options', 'correct_answer', 'topic', 'difficulty', 'points']);
        });
    }
};
