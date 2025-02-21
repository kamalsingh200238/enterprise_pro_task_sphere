<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Creates the 'project_viewers' table.
     * Store viewers of a projct.
     */
    public function up(): void
    {
        Schema::create('project_viewers', function (Blueprint $table) {
            $table->id();
            // Store ID of the project that can be viewed
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            // Store ID of the user who can view
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            // Ensure a user is viewer to a project only once
            $table->unique(['project_id', 'user_id']);
            $table->timestampsTz();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_viewers');
    }
};
