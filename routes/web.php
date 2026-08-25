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
    Route::get('/drafts/{id}', [PostController::class, 'show'])->name('drafts.show');
    Route::post('/drafts', [PostController::class, 'store'])->name('drafts.store');
    Route::post('/posts/custom', [PostController::class, 'storeCustom'])->name('posts.custom');
    Route::patch('/drafts/{id}', [PostController::class, 'update'])->name('drafts.update');
    Route::post('/drafts/{id}/approve', [PostController::class, 'approve'])->name('drafts.approve');
    Route::post('/drafts/{id}/publish', [PostController::class, 'publish'])->name('drafts.publish');
    Route::post('/drafts/{id}/generate-caption', [PostController::class, 'generateCaption'])->name('drafts.generateCaption');
    Route::post('/drafts/{id}/media', [PostController::class, 'uploadMedia'])->name('drafts.media.upload');
    Route::delete('/drafts/{id}/media/{filename}', [PostController::class, 'deleteMedia'])->name('drafts.media.delete');
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

// =========================================================================
// PUBLIC API ENDPOINTS (HEALTH & TOKEN-BASED AUTH)
// =========================================================================
Route::get('/api/health', fn () => response()->json(['status' => 'ok', 'version' => '1.0.0']));
Route::post('/api/auth/login', [\App\Http\Controllers\Api\AuthController::class, 'login']);
Route::post('/api/auth/register', [\App\Http\Controllers\Api\AuthController::class, 'register']);

// =========================================================================
// PROTECTED API SUITE (REQUIRES BEARER TOKEN OR ACTIVE SESSION)
// =========================================================================
Route::middleware([\App\Http\Middleware\AuthenticateApiToken::class])->group(function () {
    // Current User & Token Management
    Route::get('/api/auth/me', [\App\Http\Controllers\Api\AuthController::class, 'me']);
    Route::post('/api/auth/logout', [\App\Http\Controllers\Api\AuthController::class, 'logout']);
    Route::get('/api/auth/tokens', [\App\Http\Controllers\Api\AuthController::class, 'listTokens']);
    Route::post('/api/auth/tokens', [\App\Http\Controllers\Api\AuthController::class, 'createToken']);
    Route::delete('/api/auth/tokens/{id}', [\App\Http\Controllers\Api\AuthController::class, 'revokeToken']);

    // Extraction
    Route::post('/api/extract', function (Request $request) {
        $url = $request->input('url');
        if (! $url) {
            return response()->json(['error' => 'URL is required'], 400);
        }
        return response()->json(\App\Services\ShopeeExtractService::extract($url));
    });

    // Posts & Drafts
    Route::get('/api/posts', function (Request $request) {
        $limit = (int) $request->input('limit', 50);
        $offset = (int) $request->input('offset', 0);
        return response()->json(\App\Models\Post::latest()->skip($offset)->take($limit)->get());
    });
    Route::post('/api/posts', [PostController::class, 'store']);
    Route::get('/api/posts/{id}', fn (string $id) => response()->json(\App\Models\Post::findOrFail($id)));
    Route::delete('/api/posts/{id}', [PostController::class, 'destroy']);

    Route::put('/api/posts/caption', function (Request $request) {
        $postId = $request->input('post_id');
        $post = \App\Models\Post::findOrFail($postId);
        $post->update([
            'caption' => $request->input('caption', $post->caption),
            'tags' => $request->input('tags', $post->tags),
            'product_price' => $request->input('product_price', $post->product_price),
        ]);
        return response()->json(['status' => 'success', 'post' => $post]);
    });

    Route::post('/api/draft/generate', function (Request $request) {
        $postId = $request->input('post_id');
        $style = $request->input('caption_style', 'viral_ai');
        $post = \App\Models\Post::findOrFail($postId);
        $aiGen = \App\Services\AiContentGeneratorService::generateProductDeal([
            'product_title' => $post->product_title ?? 'Featured Deal',
            'product_description' => $post->product_description ?? '',
            'product_price' => $post->product_price ?? '',
            'shop_name' => $post->shop_name ?? '',
            'affiliate_url' => $post->affiliate_url,
            'caption_style' => $style,
        ]);
        $post->update([
            'caption' => $aiGen['caption'],
            'tags' => $aiGen['tags'],
        ]);
        return response()->json([
            'post_id' => $post->id,
            'caption' => $aiGen['caption'],
            'caption_style' => $style,
            'tags' => $aiGen['tags'],
            'recommended_hashtags' => ['#ShopeePH', '#BudolFinds', '#TechSulitDeals', '#ShopeeFinds', '#MustHave'],
            'ai_usage' => [
                'provider' => \App\Models\Setting::get('ai_provider', 'openai'),
                'model' => \App\Models\Setting::get('ai_model', 'gpt-4o-mini'),
                'prompt_tokens' => $aiGen['prompt_tokens'],
                'completion_tokens' => $aiGen['completion_tokens'],
                'total_tokens' => $aiGen['total_tokens'],
            ],
        ]);
    });

    Route::post('/api/publish', function (Request $request) {
        $postId = $request->input('post_id');
        return (new PostController())->publish($request, $postId);
    });

    Route::post('/api/posts/extract-media', function (Request $request) {
        $postId = $request->input('post_id');
        $post = \App\Models\Post::findOrFail($postId);
        $url = $request->input('url') ?: $post->affiliate_url;
        $extracted = \App\Services\ShopeeExtractService::extract($url);
        if ($extracted['success']) {
            $existing = $post->media_files ?? [];
            $merged = array_values(array_unique(array_merge($existing, $extracted['media_files'])));
            $post->update([
                'media_files' => $merged,
                'product_title' => $extracted['product_title'] ?: $post->product_title,
                'product_price' => $extracted['product_price'] ?: $post->product_price,
            ]);
            return response()->json([
                'success' => true,
                'post_id' => $post->id,
                'media_count' => count($merged),
                'new_media_count' => count($extracted['media_files']),
                'media_files' => $merged,
                'product_title' => $post->product_title,
            ]);
        }
        return response()->json(['success' => false, 'error' => 'Could not extract media'], 400);
    });

    Route::post('/api/posts/media/upload', function (Request $request) {
        $postId = $request->input('post_id');
        return (new PostController())->uploadMedia($request, $postId);
    });

    Route::post('/api/posts/media/delete', function (Request $request) {
        $postId = $request->input('post_id');
        $filename = $request->input('filename');
        return (new PostController())->deleteMedia($request, $postId, $filename);
    });

    // Settings & Tokens
    Route::get('/api/settings', fn () => response()->json(\App\Models\Setting::query()->pluck('value', 'key')));
    Route::post('/api/settings', [SettingsController::class, 'update']);

    Route::get('/api/token/verify', [SettingsController::class, 'verifyToken']);
    Route::post('/api/token/verify-account', function (Request $request) {
        $id = $request->input('id');
        $account = SocialAccount::findOrFail($id);
        $res = \Illuminate\Support\Facades\Http::get("https://graph.facebook.com/v20.0/{$account->account_id}", [
            'fields' => 'id,name,category',
            'access_token' => $account->access_token,
        ]);
        return response()->json([
            'valid' => $res->successful(),
            'page_name' => $res->json('name') ?: $account->name,
            'page_id' => $account->account_id,
            'error' => $res->successful() ? null : $res->json('error.message', 'Invalid token'),
        ]);
    });
    Route::get('/api/token/verify-all', function () {
        $statuses = [];
        foreach (SocialAccount::where('platform', 'facebook')->get() as $acc) {
            $res = \Illuminate\Support\Facades\Http::get("https://graph.facebook.com/v20.0/{$acc->account_id}", [
                'fields' => 'id,name',
                'access_token' => $acc->access_token,
            ]);
            $statuses[$acc->id] = [
                'valid' => $res->successful(),
                'page_name' => $res->json('name') ?: $acc->name,
                'page_id' => $acc->account_id,
            ];
        }
        return response()->json(['statuses' => $statuses]);
    });
    Route::get('/api/token/debug', fn () => response()->json(['available' => true, 'is_valid' => true, 'is_page_token' => true]));
    Route::post('/api/token/exchange', [SettingsController::class, 'exchangeToken']);

    // Integrations
    Route::get('/api/integrations', fn () => response()->json(['integrations' => SocialAccount::all()]));
    Route::post('/api/integrations/toggle', function (Request $request) {
        $id = $request->input('id');
        $isEnabled = (bool) $request->input('is_enabled');
        $acc = SocialAccount::findOrFail($id);
        $acc->update(['is_enabled' => $isEnabled]);
        return response()->json(['status' => 'success', 'id' => $id, 'is_enabled' => $isEnabled]);
    });
    Route::post('/api/integrations/facebook/add', function (Request $request) {
        $acc = SocialAccount::create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'platform' => 'facebook',
            'name' => $request->input('page_name'),
            'account_id' => $request->input('page_id'),
            'access_token' => $request->input('page_token'),
            'is_enabled' => true,
            'status' => 'active',
        ]);
        return response()->json(['status' => 'success', 'id' => $acc->id, 'name' => $acc->name]);
    });
    Route::post('/api/integrations/account/add', function (Request $request) {
        $acc = SocialAccount::create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'platform' => $request->input('platform', 'facebook'),
            'name' => $request->input('name'),
            'account_id' => $request->input('account_id'),
            'access_token' => $request->input('access_token'),
            'extra_config' => $request->input('extra_config', []),
            'is_enabled' => true,
            'status' => 'active',
        ]);
        return response()->json(['status' => 'success', 'id' => $acc->id, 'name' => $acc->name, 'platform' => $acc->platform]);
    });
    Route::delete('/api/integrations/{id}', function (string $id) {
        SocialAccount::findOrFail($id)->delete();
        return response()->json(['status' => 'success', 'id' => $id]);
    });
    Route::put('/api/integrations/{id}', function (Request $request, string $id) {
        $acc = SocialAccount::findOrFail($id);
        $acc->update($request->only(['name', 'account_id', 'access_token']));
        return response()->json(['status' => 'success', 'id' => $id, 'name' => $acc->name, 'account_id' => $acc->account_id]);
    });
    Route::post('/api/integrations/suggest-hashtags', [SettingsController::class, 'suggestHashtags']);
    Route::post('/api/webhooks/test', [SettingsController::class, 'testWebhook']);
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
