<?php

use App\Http\Controllers\SubmissionController;
use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\ViewerController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::get('/', function () {
        $role = Auth::user()->role;
        
        if ($role == 'sales') {
            return redirect()->route('submissions.index');
        } elseif ($role == 'viewer') {
            return redirect()->route('viewer.index');
        } else {
            return redirect()->route('approvals.index');
        }
    })->name('home');

    // Sales Routes
    Route::middleware(['role:sales'])->group(function () {
        // IMPORTANT: Route ini HARUS di atas Route::resource
        Route::get('submissions/search-nama', [SubmissionController::class, 'searchNama'])->name('submissions.search-nama');
        
        // Resource routes
        Route::resource('submissions', SubmissionController::class);
    });

    // Approver Routes
    Route::middleware(['role:approver1,approver2,approver3'])->group(function () {
        Route::get('/approvals', [ApprovalController::class, 'index'])->name('approvals.index');
        Route::get('/approvals/{submission}', [ApprovalController::class, 'show'])->name('approvals.show');
        Route::post('/approvals/{submission}/process', [ApprovalController::class, 'process'])->name('approvals.process');
    });

    // Viewer Routes
    Route::middleware(['role:viewer'])->group(function () {
        Route::get('/viewer', [ViewerController::class, 'index'])->name('viewer.index');
        Route::get('/viewer/{submission}', [ViewerController::class, 'show'])->name('viewer.show');
        Route::post('/viewer/{submission}/done', [ViewerController::class, 'markDone'])
        ->name('viewer.done');
    });
});
