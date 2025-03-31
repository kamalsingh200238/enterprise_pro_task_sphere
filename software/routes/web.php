<?php

use App\Http\Controllers\Comment\CommentController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Logs\LogsController;
use App\Http\Controllers\Project\ProjectController;
use App\Http\Controllers\SubTask\SubTaskController;
use App\Http\Controllers\Task\TaskController;
use App\Http\Controllers\User\UserController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// TODO: ADD THIS ROUTES IN AUTH MIDDLEWARE
Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::middleware('auth')->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/users', [UserController::class, 'index'])->name('users.show-all');
    Route::get('/users/new', [UserController::class, 'create'])->name('users.create');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
    Route::put('/users/{user}', [UserController::class, 'edit'])->name('users.edit');
    Route::delete('/users/{user}', [UserController::class, 'delete'])->name('users.delete');

    Route::get('/projects', [ProjectController::class, 'index'])->name('projects.show-all');
    Route::get('/projects/new', [ProjectController::class, 'create'])->name('projects.create');
    Route::post('/projects', [ProjectController::class, 'store'])->name('projects.store');
    Route::put('/projects/status/{project}', [ProjectController::class, 'updateStatus'])->name('projects.update-status');
    Route::get('/projects/{id}', [ProjectController::class, 'show'])->name('projects.show');
    Route::put('/projects/{project}', [ProjectController::class, 'edit'])->name('projects.edit');
    Route::delete('/projects/{project}', [ProjectController::class, 'delete'])->name('projects.delete');
    Route::post('/projects/{project}/comment', [ProjectController::class, 'createComment'])->name('projects.add-comment');

    Route::delete('/comments/{comment}', [CommentController::class, 'delete'])->name('comments.delete');

    Route::get('/tasks', [TaskController::class, 'index'])->name('tasks.show-all');
    Route::get('/tasks/new', [TaskController::class, 'create'])->name('tasks.create');
    Route::post('/tasks', [TaskController::class, 'store'])->name('tasks.store');
    Route::put('/tasks/status/{task}', [TaskController::class, 'updateStatus'])->name('tasks.update-status');
    Route::get('/tasks/{id}', [TaskController::class, 'show'])->name('tasks.show');
    Route::put('/tasks/{task}', [TaskController::class, 'edit'])->name('tasks.edit');
    Route::delete('/tasks/{task}', [TaskController::class, 'delete'])->name('tasks.delete');
    Route::post('/tasks/{task}/comment', [TaskController::class, 'createComment'])->name('tasks.add-comment');

    Route::get('/sub-tasks', [SubTaskController::class, 'index'])->name('sub-tasks.show-all');
    Route::get('/sub-tasks/new', [SubTaskController::class, 'create'])->name('sub-tasks.create');
    Route::post('/sub-tasks', [SubTaskController::class, 'store'])->name('sub-tasks.store');
    Route::put('/sub-tasks/status/{subTask}', [SubTaskController::class, 'updateStatus'])->name('sub-tasks.update-status');
    Route::get('/sub-tasks/{id}', [SubTaskController::class, 'show'])->name('sub-tasks.show');
    Route::put('/sub-tasks/{subTask}', [SubTaskController::class, 'edit'])->name('sub-tasks.edit');
    Route::delete('/sub-tasks/{subTask}', [SubTaskController::class, 'delete'])->name('sub-tasks.delete');
    Route::post('/sub-tasks/{subTask}/comment', [SubTaskController::class, 'createComment'])->name('sub-tasks.add-comment');

    Route::get('logs', [LogsController::class, 'index'])->name('logs.show-all');
    Route::get('logs/export', [LogsController::class, 'exportLogs'])->name('logs.export');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
