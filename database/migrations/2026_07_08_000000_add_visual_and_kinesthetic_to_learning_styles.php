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
        // 1. Modify learning_style enum in users and learning_profiles tables
        DB::statement("ALTER TABLE users MODIFY COLUMN learning_style ENUM('read_write', 'auditory', 'competitive', 'visual', 'kinesthetic') DEFAULT NULL");
        DB::statement("ALTER TABLE learning_profiles MODIFY COLUMN learning_style ENUM('read_write', 'auditory', 'competitive', 'visual', 'kinesthetic') DEFAULT NULL");

        // 2. Add columns score_visual and score_kinesthetic to learning_profiles table
        Schema::table('learning_profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('learning_profiles', 'score_visual')) {
                $table->integer('score_visual')->default(0)->after('score_auditory');
            }
            if (!Schema::hasColumn('learning_profiles', 'score_kinesthetic')) {
                $table->integer('score_kinesthetic')->default(0)->after('score_competitive');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore column drops
        Schema::table('learning_profiles', function (Blueprint $table) {
            if (Schema::hasColumn('learning_profiles', 'score_visual')) {
                $table->dropColumn('score_visual');
            }
            if (Schema::hasColumn('learning_profiles', 'score_kinesthetic')) {
                $table->dropColumn('score_kinesthetic');
            }
        });

        // Restore enum
        DB::statement("ALTER TABLE users MODIFY COLUMN learning_style ENUM('read_write', 'auditory', 'competitive') DEFAULT NULL");
        DB::statement("ALTER TABLE learning_profiles MODIFY COLUMN learning_style ENUM('read_write', 'auditory', 'competitive') DEFAULT NULL");
    }
};
