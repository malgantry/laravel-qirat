<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\GoalController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\CategoryController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;

Route::get('/', DashboardController::class);
Route::get('/dashboard-data', DashboardController::class)->name('dashboard.data');

Route::middleware(['auth', 'can:financeUser'])->group(function () {
	Route::resource('transactions', TransactionController::class)->except(['show']);
	Route::resource('goals', GoalController::class)->except(['show']);
});

// Reports
Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
Route::get('/reports/export', [ReportController::class, 'export'])->name('reports.export');
Route::get('/reports/export-pdf', [ReportController::class, 'exportPdf'])->name('reports.exportPdf');

// Budgets
Route::middleware(['auth', 'can:financeUser'])->group(function () {
	Route::resource('budgets', BudgetController::class)->except(['show']);
});

// Categories (lightweight API for UI)
Route::middleware(['auth', 'can:financeUser'])->group(function () {
	Route::get('/categories/by-type', [CategoryController::class, 'byType'])->name('categories.byType');
	Route::post('/categories/quick-store', [CategoryController::class, 'quickStore'])->name('categories.quickStore');
});

// Profile & Settings
Route::middleware(['auth'])->group(function () {
	Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
	Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
});
Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');

// Admin area (protected by auth; roles can be added later)
Route::prefix('admin')->name('admin.')->middleware(['auth', 'can:admin'])->group(function () {
	Route::get('/', [AdminController::class, 'index'])->name('dashboard');
	Route::get('/users', [AdminController::class, 'users'])->name('users');
	Route::post('/users/{user}/toggle-active', [AdminController::class, 'toggleActive'])->name('users.toggleActive');
	Route::post('/users/{user}/toggle-admin', [AdminController::class, 'toggleAdmin'])->name('users.toggleAdmin');
	Route::post('/users/{user}/reset-password', [AdminController::class, 'sendReset'])->name('users.reset');
	Route::get('/login-attempts', [AdminController::class, 'loginAttempts'])->name('loginAttempts');
	Route::get('/categories', [AdminController::class, 'categories'])->name('categories');
});

// Auth routes
Route::middleware('guest')->group(function () {
	Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
	Route::post('/login', [AuthController::class, 'login'])->name('login.perform');

	Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
	Route::post('/register', [AuthController::class, 'register'])->name('register.perform');

	Route::get('/password/forgot', [AuthController::class, 'showForgot'])->name('password.request');
	Route::post('/password/forgot', [AuthController::class, 'forgot'])->name('password.email');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');
