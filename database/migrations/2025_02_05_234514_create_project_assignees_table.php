<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     * Creates the 'project_assignees' table.
     * Store assignee of a projct.
     */
    public function up(): void
    {
        Schema::create('project_assignees', function (Blueprint $table) {
            $table->id();
            // Store ID of the assigned project
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            // Store ID of the assigned user
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            // Ensure a user is assigned to a project only once
            $table->unique(['project_id', 'user_id']);
            $table->timestampsTz();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_assignees');
    }
};
