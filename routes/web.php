<?php

use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExportHistoryController;
use App\Http\Controllers\HolidayController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\TaskMasterController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\WbsBuilderController;
use App\Http\Controllers\WorkingHourController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : view('welcome');
});

Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::resource('projects', ProjectController::class)->except(['show', 'destroy']);
    Route::resource('pics', UserManagementController::class)->except(['show', 'destroy']);
    Route::resource('task-master', TaskMasterController::class)->except(['show', 'destroy']);
    Route::resource('holidays', HolidayController::class)->except(['show', 'destroy']);
    Route::resource('working-hours', WorkingHourController::class)->except(['show', 'destroy']);

    Route::get('/wbs-builder', [WbsBuilderController::class, 'search'])->name('wbs-builder.search');
    Route::get('/projects/{project}/wbs-builder', [WbsBuilderController::class, 'index'])->name('wbs-builder.index');
    Route::get('/projects/{project}/wbs-builder/create', [WbsBuilderController::class, 'create'])->name('wbs-builder.create');
    Route::post('/projects/{project}/wbs-items', [WbsBuilderController::class, 'store'])->name('wbs-builder.store');
    Route::get('/wbs-items/{wbs_item}/edit', [WbsBuilderController::class, 'edit'])->name('wbs-builder.edit');
    Route::put('/wbs-items/{wbs_item}', [WbsBuilderController::class, 'update'])->name('wbs-builder.update');
    Route::delete('/wbs-items/{wbs_item}', [WbsBuilderController::class, 'destroy'])->name('wbs-builder.destroy');

    Route::get('/assignments', [AssignmentController::class, 'search'])->name('assignments.search');
    Route::get('/projects/{project}/assignments', [AssignmentController::class, 'index'])->name('assignments.index');
    Route::get('/projects/{project}/assignments/create', [AssignmentController::class, 'create'])->name('assignments.create');
    Route::post('/projects/{project}/assignments', [AssignmentController::class, 'store'])->name('assignments.store');
    Route::get('/assignments/{assignment}/edit', [AssignmentController::class, 'edit'])->name('assignments.edit');
    Route::put('/assignments/{assignment}', [AssignmentController::class, 'update'])->name('assignments.update');
    Route::post('/assignments/{assignment}/recalculate', [AssignmentController::class, 'recalculate'])->name('assignments.recalculate');

    Route::get('/schedule', [ScheduleController::class, 'search'])->name('schedule.search');
    Route::get('/projects/{project}/schedule', [ScheduleController::class, 'show'])->name('schedule.show');
    Route::post('/projects/{project}/schedule/progress', [ScheduleController::class, 'storeProgress'])->name('schedule.progress.store');

    Route::get('/exports', [ExportHistoryController::class, 'index'])->name('exports.index');
    Route::post('/projects/{project}/exports', [ExportHistoryController::class, 'store'])->name('exports.store');
    Route::get('/exports/{export}/download', [ExportHistoryController::class, 'download'])->name('exports.download');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/generate/{type}', [NotificationController::class, 'generate'])->name('notifications.generate');
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
