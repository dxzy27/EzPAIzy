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
        if (!Schema::hasTable('widgets')) {
            Schema::create('widgets', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->string('widget_type'); // 'progress', 'recent_quizzes', 'announcements', 'stats'
                $table->integer('position')->default(0); // Order of widgets
                $table->json('settings')->nullable(); // Custom widget settings
                $table->timestamps();
                
                $table->unique(['user_id', 'widget_type']);
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('widgets');
    }
};
