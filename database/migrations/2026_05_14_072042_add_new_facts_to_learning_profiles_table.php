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
        Schema::table('learning_profiles', function (Blueprint $table) {
            $table->string('q1')->nullable()->after('user_id');
            $table->string('q2')->nullable()->after('q1');
            $table->string('q3')->nullable()->after('q2');
            $table->string('q4')->nullable()->after('q3');
            $table->string('q5')->nullable()->after('q4');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('learning_profiles', function (Blueprint $table) {
            $table->dropColumn(['q1', 'q2', 'q3', 'q4', 'q5']);
        });
    }
};
