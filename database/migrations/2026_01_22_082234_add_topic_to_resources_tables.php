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
        if (Schema::hasTable('flashcard_sets') && !Schema::hasColumn('flashcard_sets', 'topic')) {
            Schema::table('flashcard_sets', function (Blueprint $table) {
                $table->string('topic')->nullable()->after('description');
            });
        }
        
        if (Schema::hasTable('quizzes') && !Schema::hasColumn('quizzes', 'topic')) {
            Schema::table('quizzes', function (Blueprint $table) {
                $table->string('topic')->nullable()->after('difficulty');
            });
        }

        if (Schema::hasTable('contents') && !Schema::hasColumn('contents', 'topic')) {
            Schema::table('contents', function (Blueprint $table) {
                $table->string('topic')->nullable()->after('content');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('flashcard_sets', function (Blueprint $table) {
            $table->dropColumn('topic');
        });
        Schema::table('quizzes', function (Blueprint $table) {
            $table->dropColumn('topic');
        });
        Schema::table('contents', function (Blueprint $table) {
            $table->dropColumn('topic');
        });
    }
};
