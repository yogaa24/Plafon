<?php

use App\Http\Controllers\SubmissionController;
use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\ViewerController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// ------------------------
// AUTH
// ------------------------
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->group(function () {

    // ------------------------
    // PROFILE MANAGEMENT
    // ------------------------
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
    
    // ------------------------
    // HOME REDIRECT BASED ON ROLE
    // ------------------------
    Route::get('/', function () {
        $role = Auth::user()->role;

        return match($role) {
            'sales' => redirect()->route('submissions.index'),
            'viewer' => redirect()->route('viewer.index'),
            'approver3' => redirect()->route('approvals.level3'),
            'approver4' => redirect()->route('approvals.level4'),
            'approver5' => redirect()->route('approvals.level5'),
            'approver6' => redirect()->route('approvals.level6'),
            default => redirect()->route('approvals.index'),
        };
    })->name('home');

    // ------------------------
    // SALES
    // ------------------------
    Route::middleware(['role:sales'])->group(function () {
        Route::get('submissions/search-nama', [SubmissionController::class, 'searchNama'])->name('submissions.search-nama');
        Route::resource('submissions', SubmissionController::class);
        Route::get('/submissions/customer/{customer}/create-rubah-plafon',
            [SubmissionController::class, 'createRubahPlafon']
        )->name('submissions.create-rubah-plafon');
        Route::get('/submissions/customer/{customer}/create-open-plafon',
            [SubmissionController::class, 'createOpenPlafon']
        )->name('submissions.create-open-plafon');
        Route::get('/submissions/approved-customers',
            [SubmissionController::class, 'getApprovedCustomers']
        )->name('submissions.approved-customers');
    });

    // ------------------------
    // APPROVERS (ALL LEVELS)
    // ------------------------
    Route::middleware(['role:approver1,approver2,approver3,approver4,approver5,approver6'])->group(function () {
        // General approval index (untuk Level 1 & 2)
        Route::get('/approvals', [ApprovalController::class, 'index'])->name('approvals.index');
        
        // Process approval (semua level)
        Route::post('/approvals/{submission}/process', [ApprovalController::class, 'process'])->name('approvals.process');
        
        // Level 3 Dashboard
        Route::get('/approvals/level3', [ApprovalController::class, 'level3'])
            ->name('approvals.level3')
            ->middleware('role:approver3');
        
        // Level 3 Export
        Route::get('/approvals/level3/export', [ApprovalController::class, 'exportLevel3'])
            ->name('approvals.level3.export')
            ->middleware('role:approver3,approver4');
        
        // Route untuk history approval (Level 1 & 2)
        Route::get('/approvals/history', [ApprovalController::class, 'history'])
            ->name('approvals.history');
        
        // Level 4 Dashboard
        Route::get('/approvals/level4', [ApprovalController::class, 'level4'])
            ->name('approvals.level4')
            ->middleware('role:approver4');
        
        // Level 5 Dashboard
        Route::get('/approvals/level5', [ApprovalController::class, 'level5'])
            ->name('approvals.level5')
            ->middleware('role:approver5');
        
        // Level 6 Dashboard
        Route::get('/approvals/level6', [ApprovalController::class, 'level6'])
            ->name('approvals.level6')
            ->middleware('role:approver6');
    });

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