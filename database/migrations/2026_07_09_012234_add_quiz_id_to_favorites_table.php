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
        // Drop old unique constraint
        try {
            Schema::table('favorites', function (Blueprint $table) {
                $table->dropUnique('fav_unique');
            });
        } catch (\Exception $e) {
            // Index might not exist or have a different name
        }

        Schema::table('favorites', function (Blueprint $table) {
            if (!Schema::hasColumn('favorites', 'quiz_id')) {
                $table->foreignId('quiz_id')->nullable()->constrained()->onDelete('cascade');
            }
        });

        // Add new unique constraint including quiz_id
        try {
            Schema::table('favorites', function (Blueprint $table) {
                $table->unique(['student_id', 'content_id', 'flashcard_set_id', 'quiz_id'], 'fav_unique_v2');
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
        try {
            Schema::table('favorites', function (Blueprint $table) {
                $table->dropUnique('fav_unique_v2');
            });
        } catch (\Exception $e) {}

        Schema::table('favorites', function (Blueprint $table) {
            $table->dropForeign(['quiz_id']);
            $table->dropColumn('quiz_id');
        });

        try {
            Schema::table('favorites', function (Blueprint $table) {
                $table->unique(['student_id', 'content_id', 'flashcard_set_id'], 'fav_unique');
            });
        } catch (\Exception $e) {}
    }
};
