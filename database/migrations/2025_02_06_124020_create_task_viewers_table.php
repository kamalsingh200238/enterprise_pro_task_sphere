<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Creates the 'task_viewers' table.
     * Store viewers of a task.
     */
    public function up(): void
    {
        Schema::create('task_viewers', function (Blueprint $table) {
            $table->id();
            // Store ID of the task that can be viewed
            $table->foreignId('task_id')->constrained('tasks')->cascadeOnDelete();
            // Store ID of the user who can view
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            // Ensure a user is viewer to a task only once
            $table->unique(['task_id', 'user_id']);
            $table->timestampsTz();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_viewers');
    }
};
