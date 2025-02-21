<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Creates the 'task_assignees' table.
     * Store assignee of a task.
     */
    public function up(): void
    {
        Schema::create('task_assignees', function (Blueprint $table) {
            $table->id();
            // Store ID of the assigned task
            $table->foreignId('task_id')->constrained('tasks')->cascadeOnDelete();
            // Store ID of the assigned user
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            // Ensure a user is assigned to a project only once
            $table->unique(['task_id', 'user_id']);
            $table->timestampsTz();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_assignees');
    }
};
