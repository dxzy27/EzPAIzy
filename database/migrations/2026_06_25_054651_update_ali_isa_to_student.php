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
        $user = \App\Models\User::where('email', 'ali123@gmail.com')->first();
        if ($user) {
            $user->update([
                'role' => 'student',
                'class_name' => '5A1'
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $user = \App\Models\User::where('email', 'ali123@gmail.com')->first();
        if ($user) {
            $user->update([
                'role' => 'teacher',
                'class_name' => null
            ]);
        }
    }
};
