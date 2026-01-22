<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Admin\AiFeedbackController as AdminAiFeedbackController;
use App\Http\Controllers\AiFeedbackController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GoalController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\TransactionController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// --- Public / Guest Routes ---
Route::get('/', DashboardController::class); // Middleware handles redirection internally if needed
Route::get('/dashboard-data', DashboardController::class)->name('dashboard.data');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.perform');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.perform');
    Route::get('/password/forgot', [AuthController::class, 'showForgot'])->name('password.request');
    Route::post('/password/forgot', [AuthController::class, 'forgot'])->name('password.email');
});

// --- Authenticated User Routes ---
Route::middleware(['auth'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Dashboard & Core Resources (Finance Users)
    Route::middleware('can:financeUser')->group(function () {
        Route::resource('transactions', TransactionController::class)->except(['show']);
        Route::resource('goals', GoalController::class)->except(['show']);
        Route::resource('budgets', BudgetController::class)->except(['show']);

        // Reports
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/', [ReportController::class, 'index'])->name('index');
            Route::get('/export', [ReportController::class, 'export'])->name('export');
            Route::get('/export-excel', [ReportController::class, 'exportExcel'])->name('exportExcel');
            Route::get('/export-pdf', [ReportController::class, 'exportPdf'])->name('exportPdf');
        });

        // Categories (Quick Access)
        Route::get('/categories/by-type', [CategoryController::class, 'byType'])->name('categories.byType');
        Route::post('/categories/quick-store', [CategoryController::class, 'quickStore'])->name('categories.quickStore');
        
        // Transaction Helper
        Route::post('/transactions/check-budget', [TransactionController::class, 'checkBudget'])->name('transactions.checkBudget');
    });

    // Profile & Settings
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::get('/profile/edit', [ProfileController::class, 'index'])->name('profile.edit'); // Alias
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');

    // Notifications
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::post('/{id}/read', [NotificationController::class, 'markAsRead'])->name('read');
        Route::post('/read-all', [NotificationController::class, 'markAllAsRead'])->name('readAll');
    });

    // AI Feedback / Insights
    Route::post('/ai/feedback', [AiFeedbackController::class, 'store'])->name('ai.feedback');
    Route::post('/ai/insights/refresh', [AiFeedbackController::class, 'refresh'])->name('ai.refresh');
    Route::get('/ai/insights/dashboard', [\App\Http\Controllers\AiInsightController::class, 'dashboard'])->name('ai.insights.dashboard');
    Route::get('/ai/insights/reports', [\App\Http\Controllers\AiInsightController::class, 'reports'])->name('ai.insights.reports');
    Route::get('/ai/insights/transactions', [\App\Http\Controllers\AiInsightController::class, 'transactions'])->name('ai.insights.transactions');
    Route::get('/ai/insights/goals', [\App\Http\Controllers\AiInsightController::class, 'goals'])->name('ai.insights.goals');
    Route::get('/ai/insights/budgets', [\App\Http\Controllers\AiInsightController::class, 'budgets'])->name('ai.insights.budgets');
    Route::post('/ai/classify', [\App\Http\Controllers\AiInsightController::class, 'classify'])->name('ai.classify');
});

// --- Admin Routes ---
Route::prefix('admin')->name('admin.')->middleware(['auth', 'can:admin'])->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('dashboard');
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    
    // User Management Actions
    Route::prefix('users/{user}')->name('users.')->group(function () {
        Route::post('/toggle-active', [AdminController::class, 'toggleActive'])->name('toggleActive');
        Route::post('/toggle-admin', [AdminController::class, 'toggleAdmin'])->name('toggleAdmin');
        Route::post('/reset-password', [AdminController::class, 'sendReset'])->name('reset');
    });

    Route::get('/login-attempts', [AdminController::class, 'loginAttempts'])->name('loginAttempts');
    Route::get('/categories', [AdminController::class, 'categories'])->name('categories');
    Route::delete('/categories/{category}', [AdminController::class, 'destroyCategory'])->name('categories.destroy');


    // Admin AI Feedback Management
    Route::get('/ai-feedbacks', [AdminAiFeedbackController::class, 'index'])->name('ai.feedbacks');
    Route::delete('/ai-feedbacks/{id}', [AdminAiFeedbackController::class, 'destroy'])->name('ai.feedbacks.destroy');
});
