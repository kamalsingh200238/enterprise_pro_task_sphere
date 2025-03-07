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
        /**
         * Creates the 'sub_tasks' table.
         * This table will store every detail about sub-tasks
         */
        Schema::create('sub_tasks', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();

            $table->dateTimeTz('start_date');
            $table->dateTimeTz('due_date');

            // Store id of status
            $table->foreignId('status_id')->constrained('statuses');
            // Store id of priority
            $table->foreignId('priority_id')->constrained('priorities');

            $table->boolean('is_private')->default(false);

            // Store id of user who created the sub-task
            $table->foreignId('created_by')->constrained('users');
            // Store id of user who updated the sub-task last
            $table->foreignId('updated_by')->constrained('users');
            // Store id of user who is supervisor of the sub-task
            $table->foreignId('supervisor_id')->constrained('users');
            // Store id of parent task
            $table->foreignId('task_id')->constrained('tasks');

            $table->timestampsTz();
            $table->softDeletes();
        });

        /**
         * Creates the 'sub_task_assignees' table.
         * Store assignees of a sub-task.
         */
        Schema::create('sub_task_assignees', function (Blueprint $table) {
            $table->id();
            // Store ID of the assigned sub-task
            $table->foreignId('sub_task_id')->constrained('sub_tasks')->cascadeOnDelete();
            // Store ID of the assigned user
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            // Ensure a user is assigned to a sub-task only once
            $table->unique(['sub_task_id', 'user_id']);
            $table->timestampsTz();
        });

        /**
         * Creates the 'sub_task_assignees' table.
         * Store viewers of a sub-task.
         */
        Schema::create('sub_task_viewers', function (Blueprint $table) {
            $table->id();
            // Store ID of the sub-task that can be viewed
            $table->foreignId('sub_task_id')->constrained('sub_tasks')->cascadeOnDelete();
            // Store ID of the user who can view
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            // Ensure a user is viewer to a project only once
            $table->unique(['sub_tasks_id', 'user_id']);
            $table->timestampsTz();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sub_tasks');
        Schema::dropIfExists('sub_tasks_assignees');
        Schema::dropIfExists('sub_tasks_viewers');
    }
};
