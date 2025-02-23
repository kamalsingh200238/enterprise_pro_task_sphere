<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Creates the 'projects' table.
     * This table will store every detail about projects
     */
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('slug')->unique()->default('');
            $table->text('description')->nullable();

            $table->dateTimeTz('start_date');
            $table->dateTimeTz('due_date');

            // Store id of status
            $table->foreignId('status_id')->constrained('statuses');
            // Store id of priority
            $table->foreignId('priority_id')->constrained('priorities');

            $table->boolean('is_private')->default(false);

            // Store id of user who created the project
            $table->foreignId('created_by')->constrained('users');
            // Store id of user who updated the project last
            $table->foreignId('updated_by')->constrained('users');
            // Store id of user who is supervisor of the project
            $table->foreignId('supervisor_id')->constrained('users');

            $table->timestampsTz(); // Created and updated timestamps
            $table->softDeletes(); // Soft deletes the project.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
