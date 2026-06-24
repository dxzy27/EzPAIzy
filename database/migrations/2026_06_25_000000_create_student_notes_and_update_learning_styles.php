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
        // 1. Create student_notes table if not exists
        if (!Schema::hasTable('student_notes')) {
            Schema::create('student_notes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('topic');
                $table->string('difficulty')->nullable();
                $table->string('title');
                $table->text('content');
                $table->string('resource_type')->nullable(); // 'quiz', 'content', 'flashcard'
                $table->unsignedBigInteger('resource_id')->nullable();
                $table->timestamps();
            });
        }

        // 2. Modify learning_style enum in users and learning_profiles tables
        // For MySQL: use raw statement to alter enum
        DB::statement("ALTER TABLE users MODIFY COLUMN learning_style ENUM('read_write', 'auditory', 'competitive') DEFAULT NULL");
        DB::statement("ALTER TABLE learning_profiles MODIFY COLUMN learning_style ENUM('read_write', 'auditory', 'competitive') DEFAULT NULL");

        // 3. Rename score_visual to score_read_write
        DB::statement("ALTER TABLE learning_profiles CHANGE COLUMN score_visual score_read_write INT DEFAULT 0");

        // 4. Update any existing 'visual' users to 'read_write'
        DB::table('users')->where('learning_style', 'visual')->update(['learning_style' => 'read_write']);
        DB::table('learning_profiles')->where('learning_style', 'visual')->update(['learning_style' => 'read_write']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_notes');

        // Restore enum
        DB::statement("ALTER TABLE users MODIFY COLUMN learning_style ENUM('visual', 'auditory', 'competitive') DEFAULT NULL");
        DB::statement("ALTER TABLE learning_profiles MODIFY COLUMN learning_style ENUM('visual', 'auditory', 'competitive') DEFAULT NULL");

        // Restore column name
        DB::statement("ALTER TABLE learning_profiles CHANGE COLUMN score_read_write score_visual INT DEFAULT 0");

        DB::table('users')->where('learning_style', 'read_write')->update(['learning_style' => 'visual']);
        DB::table('learning_profiles')->where('learning_style', 'read_write')->update(['learning_style' => 'visual']);
    }
};
