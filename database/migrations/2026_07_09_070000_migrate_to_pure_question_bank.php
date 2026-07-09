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
        // 1. Add new columns
        Schema::table('progress', function (Blueprint $table) {
            $table->string('topic')->nullable()->after('student_id');
            $table->string('difficulty')->nullable()->after('topic');
        });

        Schema::table('favorites', function (Blueprint $table) {
            $table->string('quiz_topic')->nullable()->after('flashcard_set_id');
            $table->string('quiz_difficulty')->nullable()->after('quiz_topic');
        });

        // 2. Backfill topic and difficulty into progress and favorites from quizzes
        $progress = DB::table('progress')->get();
        foreach ($progress as $p) {
            if ($p->quiz_id) {
                $quiz = DB::table('quizzes')->where('id', $p->quiz_id)->first();
                if ($quiz) {
                    DB::table('progress')->where('id', $p->id)->update([
                        'topic' => $quiz->topic,
                        'difficulty' => $quiz->difficulty,
                    ]);
                }
            }
        }

        $favorites = DB::table('favorites')->get();
        foreach ($favorites as $f) {
            if (isset($f->quiz_id) && $f->quiz_id) {
                $quiz = DB::table('quizzes')->where('id', $f->quiz_id)->first();
                if ($quiz) {
                    DB::table('favorites')->where('id', $f->id)->update([
                        'quiz_topic' => $quiz->topic,
                        'quiz_difficulty' => $quiz->difficulty,
                    ]);
                }
            }
        }

        // Fill empty values before setting not null
        DB::table('progress')->whereNull('topic')->update(['topic' => 'General', 'difficulty' => 'easy']);

        // Make columns not nullable for progress now that we've backfilled
        Schema::table('progress', function (Blueprint $table) {
            $table->string('topic')->nullable(false)->change();
            $table->string('difficulty')->nullable(false)->change();
        });

        // 3. Drop unique constraints & foreign keys
        // favorites constraint
        try {
            Schema::table('favorites', function (Blueprint $table) {
                $table->dropUnique('fav_unique_v2');
            });
        } catch (\Exception $e) {}

        try {
            Schema::table('favorites', function (Blueprint $table) {
                $table->dropForeign(['quiz_id']);
                $table->dropColumn('quiz_id');
            });
        } catch (\Exception $e) {}

        // Add new unique constraint to favorites including topic and difficulty
        try {
            Schema::table('favorites', function (Blueprint $table) {
                $table->unique(['student_id', 'content_id', 'flashcard_set_id', 'quiz_topic', 'quiz_difficulty'], 'fav_unique_v3');
            });
        } catch (\Exception $e) {}

        // progress constraint
        try {
            Schema::table('progress', function (Blueprint $table) {
                $table->dropForeign(['quiz_id']);
                $table->dropColumn('quiz_id');
            });
        } catch (\Exception $e) {}

        // questions constraint
        try {
            Schema::table('questions', function (Blueprint $table) {
                $table->dropForeign(['quiz_id']);
                $table->dropColumn('quiz_id');
            });
        } catch (\Exception $e) {}

        // 4. Drop quizzes table
        Schema::dropIfExists('quizzes');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reversal not supported directly
    }
};
