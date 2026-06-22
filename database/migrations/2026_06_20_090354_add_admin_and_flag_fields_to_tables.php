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
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_suspended')->default(false)->after('role');
        });

        Schema::table('contents', function (Blueprint $table) {
            $table->boolean('is_flagged')->default(false)->after('topic');
        });

        Schema::table('flashcard_sets', function (Blueprint $table) {
            $table->boolean('is_flagged')->default(false)->after('topic');
        });

        Schema::table('quizzes', function (Blueprint $table) {
            $table->boolean('is_flagged')->default(false)->after('topic');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_suspended');
        });

        Schema::table('contents', function (Blueprint $table) {
            $table->dropColumn('is_flagged');
        });

        Schema::table('flashcard_sets', function (Blueprint $table) {
            $table->dropColumn('is_flagged');
        });

        Schema::table('quizzes', function (Blueprint $table) {
            $table->dropColumn('is_flagged');
        });
    }
};
