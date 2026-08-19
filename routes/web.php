<?php

use App\Http\Controllers\PostController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\WorkflowController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');

    Route::inertia('/create', 'Create/Index')->name('create');

    Route::get('/drafts', [PostController::class, 'index'])->name('drafts.index');

    Route::post('/drafts', [PostController::class, 'store'])->name('drafts.store');
    Route::patch('/drafts/{id}', [PostController::class, 'update'])->name('drafts.update');
    Route::delete('/drafts/{id}', [PostController::class, 'destroy'])->name('drafts.destroy');

    Route::get('/history', [PostController::class, 'history'])->name('history.index');

    Route::get('/automated', [WorkflowController::class, 'index'])->name('automated.index');
    Route::post('/automated', [WorkflowController::class, 'store'])->name('automated.store');
    Route::delete('/automated/{id}', [WorkflowController::class, 'destroy'])->name('automated.destroy');

    Route::get('/settings/app', [SettingsController::class, 'index'])->name('settings.app');
    Route::post('/settings/app', [SettingsController::class, 'update'])->name('settings.app.update');
    Route::post('/settings/social-accounts', [SettingsController::class, 'storeSocialAccount'])->name('settings.social.store');
    Route::patch('/settings/social-accounts/{id}', [SettingsController::class, 'updateSocialAccount'])->name('settings.social.update');
    Route::delete('/settings/social-accounts/{id}', [SettingsController::class, 'destroySocialAccount'])->name('settings.social.destroy');
    Route::post('/settings/social-accounts/{id}/toggle', [SettingsController::class, 'toggleSocialAccount'])->name('settings.social.toggle');
    Route::post('/settings/users', [SettingsController::class, 'storeUser'])->name('settings.users.store');
    Route::post('/settings/test-webhook', [SettingsController::class, 'testWebhook'])->name('settings.webhook.test');
});

require __DIR__.'/settings.php';

