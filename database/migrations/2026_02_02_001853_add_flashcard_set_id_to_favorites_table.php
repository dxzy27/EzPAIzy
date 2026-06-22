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
        // Drop unique index if it exists (using raw SQL for safer execution)
        try {
            DB::statement('ALTER TABLE favorites DROP INDEX favorites_student_id_content_id_unique');
        } catch (\Exception $e) {
            // Index might not exist or have a different name
        }

        Schema::table('favorites', function (Blueprint $table) {
            if (!Schema::hasColumn('favorites', 'flashcard_set_id')) {
                $table->foreignId('flashcard_set_id')->nullable()->constrained()->onDelete('cascade');
            }
        });
        
        // Raw SQL to make content_id nullable (MySQL specific)
        DB::statement('ALTER TABLE favorites MODIFY COLUMN content_id BIGINT UNSIGNED NULL');

        // Add new unique constraint if it doesn't already exist
        try {
            Schema::table('favorites', function (Blueprint $table) {
                $table->unique(['student_id', 'content_id', 'flashcard_set_id'], 'fav_unique');
            });
        } catch (\Exception $e) {
            // Unique might already exist
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert content_id to NOT NULL
        DB::statement('DELETE FROM favorites WHERE content_id IS NULL'); // Clean up before reverting
        DB::statement('ALTER TABLE favorites MODIFY COLUMN content_id BIGINT UNSIGNED NOT NULL');
        
        Schema::table('favorites', function (Blueprint $table) {
            $table->dropForeign(['flashcard_set_id']);
            $table->dropColumn('flashcard_set_id');
        });
    }
};
