<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sub_tasks', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();

            $table->dateTimeTz('start_date');
            $table->dateTimeTz('due_date');

            $table->foreignId('status_id')->constrained('statuses');
            $table->foreignId('priority_id')->constrained('priorities');

            $table->boolean('is_private')->default(false);

            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->constrained('users');
            $table->foreignId('supervisor_id')->constrained('users');
            $table->foreignId('task_id')->constrained('tasks');

            $table->timestampsTz();
            $table->softDeletes();
        });

        Schema::create('sub_task_assignees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sub_task_id')->constrained('sub_tasks')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unique(['sub_task_id', 'user_id']);
            $table->timestampsTz();
        });

        Schema::create('sub_task_viewers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sub_task_id')->constrained('sub_tasks')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
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
