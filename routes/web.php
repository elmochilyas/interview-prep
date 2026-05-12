<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DomainController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/', function () {
    return view('welcome');
});

// Authentication routes (Breeze)
require __DIR__.'/auth.php';

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
