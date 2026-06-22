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
        try {
            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasColumn('users', 'class_name')) {
                    $table->string('class_name')->nullable();
                }
            });
        } catch (\Exception $e) {
            // Column likely exists, ignore
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('class_name');
        });
    }
};
