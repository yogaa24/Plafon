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

        // Autocomplete
        Route::get('submissions/search-nama', [SubmissionController::class, 'searchNama'])
            ->name('submissions.search-nama');

        // Resource utama (store, index, create, edit, dst.)
        Route::resource('submissions', SubmissionController::class);

        // Create Rubah Plafon
        Route::get('/submissions/{submission}/rubah-plafon', 
            [SubmissionController::class, 'createRubahPlafon'])
            ->name('submissions.create-rubah-plafon');

        // Create Open Plafon
        Route::get('/submissions/create-open-plafon/{submission}', 
            [SubmissionController::class, 'createOpenPlafon'])
            ->name('submissions.create-open-plafon');

        // API customers
        Route::get('/submissions/approved-customers', 
            [SubmissionController::class, 'getApprovedCustomers'])
            ->name('submissions.approved-customers');
    });

    // Approver Routes
    Route::middleware(['role:approver1,approver2,approver3'])->group(function () {
        Route::get('/approvals', [ApprovalController::class, 'index'])->name('approvals.index');
        Route::get('/approvals/{submission}', [ApprovalController::class, 'show'])->name('approvals.show');
        Route::post('/approvals/{submission}/process', [ApprovalController::class, 'process'])->name('approvals.process');
    });

    // Viewer Routes
    Route::middleware(['role:viewer'])->group(function () {
        // Viewer utama
        Route::get('/viewer', [ViewerController::class, 'index'])->name('viewer.index');
        Route::get('/viewer/{submission}', [ViewerController::class, 'show'])->name('viewer.show');
        Route::post('/viewer/{submission}/done', [ViewerController::class, 'markDone'])->name('viewer.done');

        // CSV Import routes
        Route::post('/viewer/import', [ViewerController::class, 'import'])->name('viewer.import');
    });

});
