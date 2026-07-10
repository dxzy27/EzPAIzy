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
        // 1. Update any users or learning profiles currently set to 'competitive' to NULL
        DB::table('users')->where('learning_style', 'competitive')->update(['learning_style' => null]);
        DB::table('learning_profiles')->where('learning_style', 'competitive')->update(['learning_style' => null]);

        // 2. Modify ENUM column on users table to exclude 'competitive'
        DB::statement("ALTER TABLE users MODIFY COLUMN learning_style ENUM('read_write', 'auditory', 'visual', 'kinesthetic') DEFAULT NULL");

        // 3. Modify ENUM column on learning_profiles table to exclude 'competitive' and drop column score_competitive
        DB::statement("ALTER TABLE learning_profiles MODIFY COLUMN learning_style ENUM('read_write', 'auditory', 'visual', 'kinesthetic') DEFAULT NULL");

        Schema::table('learning_profiles', function (Blueprint $table) {
            if (Schema::hasColumn('learning_profiles', 'score_competitive')) {
                $table->dropColumn('score_competitive');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('learning_profiles', function (Blueprint $table) {
            $table->integer('score_competitive')->default(0)->after('score_auditory');
        });

        DB::statement("ALTER TABLE users MODIFY COLUMN learning_style ENUM('read_write', 'auditory', 'competitive', 'visual', 'kinesthetic') DEFAULT NULL");
        DB::statement("ALTER TABLE learning_profiles MODIFY COLUMN learning_style ENUM('read_write', 'auditory', 'competitive', 'visual', 'kinesthetic') DEFAULT NULL");
    }
};
