<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Creates the 'tasks' table.
     * This table will store every detail about tasks
     */
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('slug')->unique()->default('');
            $table->text('description')->nullable();

            $table->dateTimeTz('start_date');
            $table->dateTimeTz('due_date');

            // store id of status
            $table->foreignId('status_id')->constrained('statuses');
            // store id of priority
            $table->foreignId('priority_id')->constrained('priorities');

            $table->boolean('is_private')->default(false);

            // store id of user who created the task
            $table->foreignId('created_by')->constrained('users');
            // store id of user who updated the task last
            $table->foreignId('updated_by')->constrained('users');
            // store id of user who is supervisor of the task
            $table->foreignId('supervisor_id')->constrained('users');
            // store id of project which is parent of the task
            $table->foreignId('project_id')->constrained('projects');

            $table->timestampsTz();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
