<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Rebuild learning_profiles with a clean, comprehensive schema.
     * Drops legacy columns (boolean facts, q1-q5) and adds structured fields
     * that support the new 10-question rule-based expert system.
     */
    public function up(): void
    {
        Schema::table('learning_profiles', function (Blueprint $table) {
            // Drop all legacy columns
            $table->dropColumn([
                'struggles_memorizing',
                'prefers_visual',
                'weak_jawi',
                'forgets_easily',
                'quiz_anxiety',
                'q1', 'q2', 'q3', 'q4', 'q5',
            ]);
        });

        Schema::table('learning_profiles', function (Blueprint $table) {
            // Raw answers from the 10 questions, stored as JSON map: {"q1":"visual","q2":"auditory",...}
            $table->json('answers')->nullable()->after('user_id');

            // Weighted scores produced by the inference engine
            $table->integer('score_visual')->default(0)->after('answers');
            $table->integer('score_auditory')->default(0)->after('score_visual');
            $table->integer('score_competitive')->default(0)->after('score_auditory');

            // Confidence = winning score / total weight (0–100)
            $table->float('confidence')->default(0)->after('score_competitive');

            // Final diagnosed type: visual | auditory | competitive
            $table->string('learning_style')->nullable()->after('confidence');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('learning_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'answers',
                'score_visual',
                'score_auditory',
                'score_competitive',
                'confidence',
                'learning_style',
            ]);
        });

        Schema::table('learning_profiles', function (Blueprint $table) {
            $table->boolean('struggles_memorizing')->default(false);
            $table->boolean('prefers_visual')->default(false);
            $table->boolean('weak_jawi')->default(false);
            $table->boolean('forgets_easily')->default(false);
            $table->boolean('quiz_anxiety')->default(false);
            $table->string('q1')->nullable();
            $table->string('q2')->nullable();
            $table->string('q3')->nullable();
            $table->string('q4')->nullable();
            $table->string('q5')->nullable();
        });
    }
};
