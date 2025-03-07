<?php

use App\Http\Controllers\Project\ProjectController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// TODO: ADD THIS ROUTES IN AUTH MIDDLEWARE
Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/projects', [ProjectController::class, 'index'])->name('projects.show-all');
    Route::get('/projects/new', [ProjectController::class, 'create'])->name('projects.create');
    Route::post('/projects', [ProjectController::class, 'store'])->name('projects.store');
    Route::put('/projects/status/{project}', [ProjectController::class, 'updateStatus'])->name('projects.update-status');
    Route::get('/projects/{id}', [ProjectController::class, 'show'])->name('projects.show');
    Route::put('/projects/{project}', [ProjectController::class, 'edit'])->name('projects.edit');
    Route::delete('/projects/{project}', [ProjectController::class, 'delete'])->name('projects.delete');
    Route::post('/projects/{project}/comment', [ProjectController::class, 'createComment'])->name('projects.add-comment');

    Route::delete('/comments/{comment}', [CommentController::class, 'delete'])->name('comments.delete');
});

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
