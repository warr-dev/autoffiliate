<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\WorkflowController;
use App\Models\SocialAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/api/analytics/ai', [DashboardController::class, 'aiAnalytics'])->name('analytics.ai');

    Route::inertia('/create', 'Create/Index')->name('create');

    Route::get('/drafts', [PostController::class, 'index'])->name('drafts.index');
    Route::post('/drafts', [PostController::class, 'store'])->name('drafts.store');
    Route::post('/posts/custom', [PostController::class, 'storeCustom'])->name('posts.custom');
    Route::patch('/drafts/{id}', [PostController::class, 'update'])->name('drafts.update');
    Route::post('/drafts/{id}/approve', [PostController::class, 'approve'])->name('drafts.approve');
    Route::post('/drafts/{id}/publish', [PostController::class, 'publish'])->name('drafts.publish');
    Route::post('/drafts/{id}/generate-caption', [PostController::class, 'generateCaption'])->name('drafts.generateCaption');
    Route::delete('/drafts/{id}', [PostController::class, 'destroy'])->name('drafts.destroy');

    Route::get('/history', [PostController::class, 'history'])->name('history.index');

    Route::get('/automated', [WorkflowController::class, 'index'])->name('automated.index');
    Route::get('/automated/export', [WorkflowController::class, 'export'])->name('automated.export');
    Route::post('/automated/import', [WorkflowController::class, 'import'])->name('automated.import');
    Route::post('/automated', [WorkflowController::class, 'store'])->name('automated.store');
    Route::post('/automated/execute', [WorkflowController::class, 'execute'])->name('automated.execute');
    Route::post('/automated/{id}/toggle', [WorkflowController::class, 'toggleStatus'])->name('automated.toggle');
    Route::delete('/automated/{id}', [WorkflowController::class, 'destroy'])->name('automated.destroy');

    // API aliases for automated workflows
    Route::get('/api/workflows/export', [WorkflowController::class, 'export']);
    Route::post('/api/workflows/import', [WorkflowController::class, 'import']);
    Route::post('/api/workflows/execute', [WorkflowController::class, 'execute']);
    Route::post('/api/workflows/rules', [WorkflowController::class, 'store']);
    Route::put('/api/workflows/rules/{id}/status', [WorkflowController::class, 'toggleStatus']);
    Route::delete('/api/workflows/rules/{id}', [WorkflowController::class, 'destroy']);

    Route::get('/settings/app', [SettingsController::class, 'index'])->name('settings.app');
    Route::post('/settings/app', [SettingsController::class, 'update'])->name('settings.app.update');
    Route::get('/settings/social-accounts/export', [SettingsController::class, 'exportSocialAccounts'])->name('settings.social.export');
    Route::post('/settings/social-accounts/import', [SettingsController::class, 'importSocialAccounts'])->name('settings.social.import');
    Route::get('/api/social-accounts/export', [SettingsController::class, 'exportSocialAccounts']);
    Route::post('/api/social-accounts/import', [SettingsController::class, 'importSocialAccounts']);
    Route::get('/settings/social-accounts', function (Request $request) {
        if ($request->wantsJson()) {
            return response()->json(SocialAccount::all());
        }

        return redirect()->route('settings.app');
    })->name('settings.social.index');
    Route::post('/settings/social-accounts', [SettingsController::class, 'storeSocialAccount'])->name('settings.social.store');
    Route::patch('/settings/social-accounts/{id}', [SettingsController::class, 'updateSocialAccount'])->name('settings.social.update');
    Route::delete('/settings/social-accounts/{id}', [SettingsController::class, 'destroySocialAccount'])->name('settings.social.destroy');
    Route::post('/settings/social-accounts/{id}/toggle', [SettingsController::class, 'toggleSocialAccount'])->name('settings.social.toggle');
    Route::post('/settings/social-accounts/{id}/test-post', [SettingsController::class, 'testPostSocialAccount'])->name('settings.social.testPost');
    Route::get('/settings/users', fn () => redirect()->route('settings.app'));
    Route::post('/settings/users', [SettingsController::class, 'storeUser'])->name('settings.users.store');
    Route::post('/settings/test-webhook', [SettingsController::class, 'testWebhook'])->name('settings.webhook.test');
    Route::post('/settings/token/exchange', [SettingsController::class, 'exchangeToken'])->name('settings.token.exchange');
    Route::post('/settings/token/verify', [SettingsController::class, 'verifyToken'])->name('settings.token.verify');
    Route::post('/settings/suggest-hashtags', [SettingsController::class, 'suggestHashtags'])->name('settings.hashtags.suggest');
});

// Secured Web-Cron Trigger for Hostinger / External Schedulers
Route::get('/api/cron/workflows', function (Request $request) {
    $token = $request->query('token') ?: $request->header('X-Cron-Token');
    $secret = config('app.cron_secret');

    if ($secret && $token !== $secret) {
        return response()->json(['error' => 'Unauthorized cron ping. Provide valid ?token='], 403);
    }

    Artisan::call('workflows:run');
    $output = Artisan::output();

    return response()->json([
        'success' => true,
        'message' => 'Workflow runner evaluated successfully.',
        'timestamp' => now()->timezone('Asia/Manila')->toIso8601String(),
        'output' => trim($output),
    ]);
})->name('cron.workflows');

require __DIR__.'/settings.php';
