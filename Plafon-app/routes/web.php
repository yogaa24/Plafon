<?php

use App\Http\Controllers\SubmissionController;
use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\ViewerController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

// ------------------------
// AUTH
// ------------------------
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->group(function () {

    // ------------------------
    // HOME REDIRECT BASED ON ROLE
    // ------------------------
    Route::get('/', function () {
        $role = Auth::user()->role;

        if ($role == 'sales') {
            return redirect()->route('submissions.index');
        } elseif ($role == 'viewer') {
            return redirect()->route('viewer.index');
        } elseif ($role == 'approver3') {
            return redirect()->route('approvals.level3');
        } else {
            return redirect()->route('approvals.index');
        }
    })->name('home');

    // ------------------------
    // SALES
    // ------------------------
    Route::middleware(['role:sales'])->group(function () {
        Route::get('submissions/search-nama', [SubmissionController::class, 'searchNama'])->name('submissions.search-nama');
        Route::resource('submissions', SubmissionController::class);
        Route::get('/submissions/{submission}/rubah-plafon', [SubmissionController::class, 'createRubahPlafon'])->name('submissions.create-rubah-plafon');
        Route::get('/submissions/create-open-plafon/{submission}', [SubmissionController::class, 'createOpenPlafon'])->name('submissions.create-open-plafon');
        Route::get('/submissions/approved-customers', [SubmissionController::class, 'getApprovedCustomers'])->name('submissions.approved-customers');
    });

    // ------------------------
    // APPROVER 1, 2, 3 (UMUM)
    // ------------------------
    Route::middleware(['role:approver1,approver2,approver3'])->group(function () {
        Route::get('/approvals', [ApprovalController::class, 'index'])->name('approvals.index');
        // Route::get('/approvals/{submission}', [ApprovalController::class, 'show'])->name('approvals.show');
        Route::post('/approvals/{submission}/process', [ApprovalController::class, 'process'])->name('approvals.process');
    });

    // ------------------------
    // APPROVER LEVEL 3 (KHUSUS)
    // ------------------------
    Route::get('/approvals/level3', [ApprovalController::class, 'level3'])->name('approvals.level3')->middleware('role:approver3');

    // ------------------------
    // VIEWER
    // ------------------------
    Route::middleware(['role:viewer'])->group(function () {
        Route::get('/viewer', [ViewerController::class, 'index'])->name('viewer.index');
        Route::get('/viewer/{submission}', [ViewerController::class, 'show'])->name('viewer.show');
        Route::post('/viewer/{submission}/done', [ViewerController::class, 'markDone'])->name('viewer.done');
        Route::post('/viewer/import', [ViewerController::class, 'import'])->name('viewer.import');
    });

});
