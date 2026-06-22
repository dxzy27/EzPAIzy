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
        Schema::table('progress', function (Blueprint $table) {
            $table->json('student_answers')->nullable()->after('score');
            // Adding status here too in case the other migration failed
            if (!Schema::hasColumn('progress', 'status')) {
                $table->string('status')->default('completed')->after('student_answers');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('progress', function (Blueprint $table) {
            $table->dropColumn(['student_answers', 'status']);
        });
    }
};
