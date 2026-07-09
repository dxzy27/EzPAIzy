<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->foreignId('quiz_id')->nullable()->change();
            $table->string('topic')->nullable()->after('correct_answer');
            $table->string('difficulty')->nullable()->after('topic');
        });

        // Copy existing quiz topics and difficulties to questions
        $questions = DB::table('questions')->get();
        foreach ($questions as $q) {
            if ($q->quiz_id) {
                $quiz = DB::table('quizzes')->where('id', $q->quiz_id)->first();
                if ($quiz) {
                    DB::table('questions')->where('id', $q->id)->update([
                        'topic' => $quiz->topic,
                        'difficulty' => $quiz->difficulty,
                    ]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->foreignId('quiz_id')->nullable(false)->change();
            $table->dropColumn(['topic', 'difficulty']);
        });
    }
};
