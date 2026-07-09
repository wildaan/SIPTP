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
        Route::get('/{id}', [SubmissionController::class, 'show'])->name('submissions.show');
        Route::get('/{id}/export-pdf', [SubmissionController::class, 'exportPdf'])->name('submissions.exportPdf');

        // Approvals
        Route::post('/{id}/approve', [\App\Http\Controllers\ApprovalController::class, 'store'])->name('submissions.approve');

        // Payments
        Route::post('/{id}/pay', [\App\Http\Controllers\PaymentController::class, 'store'])->name('submissions.pay');
    });


    Route::middleware(['role:staff'])->prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('users.index');
        Route::get('/list', [UserController::class, 'list']);
        Route::post('/', [UserController::class, 'store']);
        Route::post('/{id}', [UserController::class, 'update']);
        Route::post('/{id}/toggle-status', [UserController::class, 'toggleStatus']);
        Route::post('/{id}/reset-password', [UserController::class, 'resetPassword']);
    });

    Route::middleware(['role:staff'])->group(function () {
        // Categories
        Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
        Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
        Route::post('/categories/{uuid}', [CategoryController::class, 'update'])->name('categories.update');
        Route::post('/categories/{uuid}/toggle-status', [CategoryController::class, 'toggleStatus'])->name('categories.toggleStatus');

        // Budgets
        Route::get('/budgets', [BudgetController::class, 'index'])->name('budgets.index');
        Route::post('/budgets', [BudgetController::class, 'store'])->name('budgets.store');
        Route::post('/budgets/{uuid}', [BudgetController::class, 'update'])->name('budgets.update');
    });

});
