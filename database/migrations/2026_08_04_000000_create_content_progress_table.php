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
        if (!Schema::hasTable('content_progress')) {
            Schema::create('content_progress', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('content_id')->constrained('contents')->onDelete('cascade');
                $table->integer('current_page')->default(1);
                $table->integer('total_pages')->default(100);
                $table->timestamps();

                // Unique combination of user and content
                $table->unique(['user_id', 'content_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('content_progress');
    }
};
