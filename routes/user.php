<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\UserController;

/*
|--------------------------------------------------------------------------
| User Management Routes
|--------------------------------------------------------------------------
|
| Routes untuk manajemen User (CRUD, password reset, activation).
| Restricted to Operator Sekolah dan Kepala Sekolah.
|
*/

Route::middleware(['auth', 'profile.completed'])->group(function () {
    
    // ===================================================================
    // USER MANAGEMENT ROUTES (must be before resource to avoid wildcard conflict)
    // ===================================================================
    
    Route::prefix('users')->name('users.')->middleware('role:Operator Sekolah,Kepala Sekolah')->group(function () {
        // Archive routes - MUST be first
        Route::get('/trash', [UserController::class, 'trash'])
            ->name('trash');
        Route::post('/{id}/restore', [UserController::class, 'restore'])
            ->name('restore');
        Route::delete('/{id}/force-delete', [UserController::class, 'forceDelete'])
            ->name('forceDelete');

        // Bulk archive operations
        Route::post('/bulk-restore', [UserController::class, 'bulkRestore'])
            ->name('bulk-restore');

        Route::delete('/bulk-force-delete', [UserController::class, 'bulkForceDelete'])
            ->name('bulk-force-delete');

        // Bulk operations (must be before /{id} routes)
        Route::post('/bulk-activate', [UserController::class, 'bulkActivate'])
            ->name('bulk-activate');

        Route::post('/bulk-deactivate', [UserController::class, 'bulkDeactivate'])
            ->name('bulk-deactivate');

        Route::post('/bulk-delete', [UserController::class, 'bulkDelete'])
            ->name('bulk-delete');

        // Export/Import
        Route::get('/export', [UserController::class, 'export'])
            ->name('export');

        Route::post('/import', [UserController::class, 'import'])
            ->name('import');

        // Password reset (by admin)
        Route::get('/{id}/reset-password', [UserController::class, 'resetPasswordForm'])
            ->name('reset-password.form');

        Route::post('/{id}/reset-password', [UserController::class, 'resetPassword'])
            ->name('reset-password');

        // Toggle activation
        Route::post('/{id}/toggle-activation', [UserController::class, 'toggleActivation'])
            ->name('toggle-activation');

        // Alias for legacy views that use 'toggle-active' instead of 'toggle-activation'
        Route::post('/{id}/toggle-active', [UserController::class, 'toggleActivation'])
            ->name('toggle-active');
    });

    // ===================================================================
    // USER CRUD ROUTES (resource routes - AFTER prefix routes)
    // ===================================================================
    
    Route::resource('users', UserController::class)
        ->names([
            'index' => 'users.index',
            'create' => 'users.create',
            'store' => 'users.store',
            'show' => 'users.show',
            'edit' => 'users.edit',
            'update' => 'users.update',
            'destroy' => 'users.destroy',
        ])
        ->middleware('role:Operator Sekolah,Kepala Sekolah'); // Only admin roles

    // ===================================================================
    // PROFILE ROUTES (All authenticated users)
    // ===================================================================
    
    Route::prefix('profile')->name('profile.')->group(function () {
        // View own profile
        Route::get('/', [UserController::class, 'showProfile'])
            ->name('show');

        // Edit own profile
        Route::get('/edit', [UserController::class, 'editProfile'])
            ->name('edit');

        Route::put('/', [UserController::class, 'updateProfile'])
            ->name('update');

        // Change own password
        Route::get('/change-password', [UserController::class, 'changePasswordForm'])
            ->name('change-password.form');

        Route::post('/change-password', [UserController::class, 'changePassword'])
            ->name('change-password');
    });

    // ===================================================================
    // BACKWARD COMPATIBILITY ROUTES (Legacy Views)
    // ===================================================================
    // Some views still use 'account.*' route names instead of 'profile.*'
    // These aliases maintain compatibility with existing blade templates
    
    Route::prefix('account')->name('account.')->group(function () {
        // Alias for account.show → profile.show
        Route::get('/', [UserController::class, 'showProfile'])
            ->name('show');

        // Alias for account.edit → profile.edit
        Route::get('/edit', [UserController::class, 'editProfile'])
            ->name('edit');

        // Alias for account.update → profile.update
        Route::put('/', [UserController::class, 'updateProfile'])
            ->name('update');

        // Alias for account.password → profile.change-password
        Route::get('/password', [UserController::class, 'changePasswordForm'])
            ->name('password');

        Route::post('/password', [UserController::class, 'changePassword'])
            ->name('password.update');
    });
});
