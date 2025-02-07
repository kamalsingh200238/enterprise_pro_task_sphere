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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->text('description')->nullable();

            $table->dateTimeTz('start_date');
            $table->dateTimeTz('due_date');

            $table->foreignId('status_id')->constrained('statuses');
            $table->foreignId('priority_id')->constrained('priorities');

            $table->boolean('is_private')->default(false);

            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->constrained('users');
            $table->foreignId('supervisor_id')->constrained('users');
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
