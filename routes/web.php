<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SubmissionController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\BudgetController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/login', [AuthController::class, 'index'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

Route::middleware(['auth.session'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/', [SubmissionController::class, 'dashboard'])->name('dashboard');

    Route::prefix('submissions')->group(function () {
        Route::get('/', [SubmissionController::class, 'index'])->name('submissions.index');
        Route::get('/create', [SubmissionController::class, 'create'])->name('submissions.create');
        Route::post('/', [SubmissionController::class, 'store'])->name('submissions.store');
        Route::get('/history', [SubmissionController::class, 'history'])->name('submissions.history');
        Route::get('/export-excel', [SubmissionController::class, 'exportExcel'])->name('submissions.exportExcel');
        Route::get('/details/{id}', [SubmissionController::class, 'show'])->name('submissions.show');
        Route::get('/{id}/export-pdf', [SubmissionController::class, 'exportPdf'])->name('submissions.exportPdf');
        // Approvals
        Route::post('/{id}/approve', [SubmissionController::class, 'approve'])->name('submissions.approve');
        // Payments
        Route::post('/payment/{id}', [SubmissionController::class, 'payment'])->name('submissions.payment');
    });


    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('users.index');
        Route::get('/list', [UserController::class, 'list']);
        Route::post('/', [UserController::class, 'store']);
        Route::post('/{id}', [UserController::class, 'update']);
        Route::post('/{id}/toggle-status', [UserController::class, 'toggleStatus']);
        Route::post('/{id}/reset-password', [UserController::class, 'resetPassword']);
    });

    //Category
    Route::controller(CategoryController::class)->group(function () {
        Route::get('/categories', 'index')->name('categories.index');
        Route::post('/categories', 'store')->name('categories.store');
        Route::post('/categories/{uuid}', 'update')->name('categories.update');
        Route::post('/categories/{uuid}/toggle-status', 'toggleStatus')->name('categories.toggleStatus');
    });

    //Budget
    Route::controller(BudgetController::class)->group(function () {
        Route::get('/budgets', 'index')->name('budgets.index');
        Route::post('/budgets', 'store')->name('budgets.store');
        Route::post('/budgets/{uuid}', 'update')->name('budgets.update');
    });

    Route::post('/profile/update', [UserController::class, 'updateProfile'])->name('profile.update');

    Route::get('/audit-trail', [UserController::class, 'auditTrail'])->name('audit-trail.index');
    Route::get('/audit-trail/list', [UserController::class, 'auditTrailList'])->name('audit-trail.list');

});
