<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Authentication routes (Breeze)
require __DIR__.'/auth.php';

<<<<<<< Updated upstream
// Dashboard - protected
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');
=======
// Protected routes - all require authentication
Route::middleware('auth')->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Domain routes
    Route::resource('domains', DomainController::class);

    // Concept routes will be added in feature/concepts-crud
    // Route::resource('domains.concepts', ConceptController::class);
    // Route::patch('concepts/{concept}/status', [ConceptController::class, 'updateStatus'])->name('concepts.updateStatus');
    // Route::get('concepts/archived', [ConceptController::class, 'archived'])->name('concepts.archived');
    // Route::patch('concepts/{concept}/restore', [ConceptController::class, 'restore'])->name('concepts.restore');

    // AI generation routes will be added in feature/ai-generation
    // Route::post('concepts/{concept}/generate', [GeneratedQuestionController::class, 'store'])->name('questions.generate');
    // Route::delete('generated-questions/{generatedQuestion}', [GeneratedQuestionController::class, 'destroy'])->name('questions.destroy');
});
>>>>>>> Stashed changes
