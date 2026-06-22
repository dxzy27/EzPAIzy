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
        // Schema::table('users', function (Blueprint $table) {
        //     // Add role column with default value 'student'
        //     if (!Schema::hasColumn('users', 'role')) {
        //         $table->enum('role', ['admin', 'teacher', 'student'])->default('student')->after('email');
        //     }
            
        //     // Add phone column if it doesn't exist (rename from phone_number if needed)
        //     if (Schema::hasColumn('users', 'phone_number') && !Schema::hasColumn('users', 'phone')) {
        //         $table->renameColumn('phone_number', 'phone');
        //     } elseif (!Schema::hasColumn('users', 'phone')) {
        //         $table->string('phone')->nullable()->after('role');
        //     }
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};
