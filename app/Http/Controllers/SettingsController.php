<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Setting;
use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class SettingsController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Settings/Index', [
            'settings' => Setting::query()->pluck('value', 'key'),
            'socialAccounts' => SocialAccount::all(),
            'users' => User::select('id', 'name', 'email', 'created_at')->get(),
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->except(['_token']);

        foreach ($data as $key => $value) {
            Setting::set($key, (string) $value);
        }

        return back()->with('success', 'Settings saved successfully.');
    }

    public function storeSocialAccount(Request $request)
    {
        $validated = $request->validate([
            'platform' => 'required|string',
            'name' => 'required|string',
            'account_id' => 'nullable|string',
            'access_token' => 'nullable|string',
            'extra_config' => 'nullable|array',
            'is_enabled' => 'nullable|boolean',
        ]);

        SocialAccount::create([
            'id' => (string) Str::uuid(),
            'platform' => $validated['platform'],
            'name' => $validated['name'],
            'account_id' => $validated['account_id'] ?? null,
            'access_token' => $validated['access_token'] ?? null,
            'extra_config' => $validated['extra_config'] ?? [],
            'is_enabled' => $validated['is_enabled'] ?? true,
            'status' => 'active',
        ]);

        return back()->with('success', 'Social account connected successfully.');
    }

    public function updateSocialAccount(Request $request, string $id)
    {
        $account = SocialAccount::findOrFail($id);

        $validated = $request->validate([
            'platform' => 'sometimes|string',
            'name' => 'sometimes|string',
            'account_id' => 'nullable|string',
            'access_token' => 'nullable|string',
            'extra_config' => 'nullable|array',
            'is_enabled' => 'sometimes|boolean',
        ]);

        $updateData = [];
        if (isset($validated['platform'])) {
            $updateData['platform'] = $validated['platform'];
        }
        if (isset($validated['name'])) {
            $updateData['name'] = $validated['name'];
        }
        if (array_key_exists('account_id', $validated)) {
            $updateData['account_id'] = $validated['account_id'];
        }
        if (! empty($validated['access_token']) && $validated['access_token'] !== '••••••••') {
            $updateData['access_token'] = $validated['access_token'];
        }
        if (isset($validated['extra_config'])) {
            $updateData['extra_config'] = array_merge($account->extra_config ?? [], $validated['extra_config']);
        }
        if (isset($validated['is_enabled'])) {
            $updateData['is_enabled'] = $validated['is_enabled'];
        }

        $account->update($updateData);

        return back()->with('success', 'Social account updated.');
    }

    public function destroySocialAccount(string $id)
    {
        SocialAccount::destroy($id);

        return back()->with('success', 'Social account deleted.');
    }

    public function toggleSocialAccount(string $id)
    {
        $account = SocialAccount::findOrFail($id);
        $account->is_enabled = ! $account->is_enabled;
        $account->save();

        return back()->with('success', 'Account status updated.');
    }

    public function testPostSocialAccount(string $id)
    {
        $account = SocialAccount::findOrFail($id);

        if ($account->platform !== 'facebook' || empty($account->access_token) || empty($account->account_id)) {
            return response()->json([
                'success' => false,
                'error' => 'Social account is missing Facebook Page ID or Access Token.',
            ], 400);
        }

        $pageId = $account->account_id;
        $token = $account->access_token;
        $pageName = $account->name;
        $extraConfig = $account->extra_config ?? [];

        $nowStr = now()->format('M d, Y h:i A');
        $tags = ! empty($extraConfig['default_hashtags']) ? $extraConfig['default_hashtags'] : Setting::get('default_hashtags', '#TechSulitDeals #ShopeePH');
        $message = "⚡ Autoffiliate Connection Test Post 🚀\n\nAutomated integration test for {$pageName}.\nTime: {$nowStr}\n\n{$tags}";

        try {
            $response = Http::timeout(30)->post("https://graph.facebook.com/v20.0/{$pageId}/feed", [
                'message' => $message,
                'access_token' => $token,
            ]);

            if ($response->successful()) {
                $fbData = $response->json();
                $fbPostId = $fbData['id'] ?? null;
                $fbPostUrl = $fbPostId ? "https://facebook.com/{$fbPostId}" : "https://facebook.com/{$pageId}";

                // Record in posts table
                Post::create([
                    'id' => 'post_'.Str::random(12),
                    'product_title' => "⚡ [Connection Test] {$pageName}",
                    'affiliate_url' => 'https://facebook.com/'.$pageId,
                    'caption' => $message,
                    'tags' => $tags,
                    'status' => 'published',
                    'facebook_post_id' => $fbPostId,
                    'facebook_post_url' => $fbPostUrl,
                    'media_files' => [],
                ]);

                return response()->json([
                    'success' => true,
                    'message' => "Test post published successfully to {$pageName}!",
                    'facebook_post_id' => $fbPostId,
                    'facebook_post_url' => $fbPostUrl,
                ]);
            }

            $errorMsg = $response->json()['error']['message'] ?? 'Facebook Graph API returned error.';

            return response()->json([
                'success' => false,
                'error' => $errorMsg,
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function storeUser(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        $user = new User;
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->password = Hash::make($validated['password']);
        $user->forceFill(['email_verified_at' => now()]);
        $user->save();

        return back()->with('success', "Account [{$user->email}] created successfully.");
    }

    public function testWebhook(Request $request)
    {
        $url = $request->input('url') ?: Setting::get('n8n_outbound_webhook');
        if (! $url) {
            return response()->json(['success' => false, 'error' => 'No webhook URL configured'], 400);
        }

        try {
            $response = Http::timeout(5)->post($url, [
                'event' => 'test_webhook',
                'timestamp' => now()->toIso8601String(),
                'sample_payload' => [
                    'product_title' => 'Sample Shopee Tech Deal',
                    'affiliate_url' => 'https://shope.ee/sample',
                    'caption' => 'Check out this awesome deal! #TechSulitDeals #ShopeePH',
                ],
            ]);

            return response()->json([
                'success' => $response->successful(),
                'status_code' => $response->status(),
                'error' => $response->successful() ? null : 'Received HTTP '.$response->status(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function exchangeToken(Request $request)
    {
        $shortToken = (string) $request->input('fb_page_token');
        $appId = (string) ($request->input('fb_app_id') ?: Setting::get('fb_app_id'));
        $appSecret = (string) ($request->input('fb_app_secret') ?: Setting::get('fb_app_secret'));
        $targetPageId = (string) ($request->input('fb_page_id') ?: Setting::get('fb_page_id'));

        if (empty($shortToken)) {
            return response()->json(['success' => false, 'error' => 'Short-lived User Token (fb_page_token) is required.'], 400);
        }

        if (empty($appId) || empty($appSecret)) {
            return response()->json(['success' => false, 'error' => 'Facebook App ID and App Secret are required for token exchange.'], 400);
        }

        // Step 1: Exchange short-lived token for long-lived User Token (valid ~60 days)
        try {
            $exchangeRes = Http::timeout(15)->get('https://graph.facebook.com/v20.0/oauth/access_token', [
                'grant_type' => 'fb_exchange_token',
                'client_id' => $appId,
                'client_secret' => $appSecret,
                'fb_exchange_token' => $shortToken,
            ]);

            $exchangeData = $exchangeRes->json();
            $longLivedUserToken = $exchangeData['access_token'] ?? null;

            if (! $longLivedUserToken) {
                $errorMsg = $exchangeData['error']['message'] ?? 'Failed to exchange short-lived token.';

                return response()->json(['success' => false, 'step' => 'exchange', 'error' => $errorMsg], 400);
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'step' => 'exchange', 'error' => 'Connection failed: '.$e->getMessage()], 500);
        }

        // Step 2: Fetch Page Access Tokens using long-lived User Token
        try {
            $accountsRes = Http::timeout(15)->get('https://graph.facebook.com/v20.0/me/accounts', [
                'fields' => 'id,name,access_token',
                'access_token' => $longLivedUserToken,
            ]);

            $accountsData = $accountsRes->json();
            $pagesData = $accountsData['data'] ?? [];

            if (empty($pagesData)) {
                return response()->json([
                    'success' => false,
                    'step' => 'page_token',
                    'error' => 'No Facebook Pages found. Ensure pages_show_list, pages_read_engagement, and pages_manage_posts permissions are granted.',
                ], 400);
            }

            $matchedPageToken = null;
            $matchedPageName = null;
            $matchedPageId = null;

            // Try to match targetPageId
            if (! empty($targetPageId)) {
                foreach ($pagesData as $page) {
                    if ((string) ($page['id'] ?? '') === $targetPageId) {
                        $matchedPageToken = $page['access_token'] ?? null;
                        $matchedPageName = $page['name'] ?? null;
                        $matchedPageId = (string) $page['id'];
                        break;
                    }
                }
            }

            // Fallback to first page if no match or not specified
            if (! $matchedPageToken && count($pagesData) > 0) {
                $firstPage = $pagesData[0];
                $matchedPageToken = $firstPage['access_token'] ?? null;
                $matchedPageName = $firstPage['name'] ?? null;
                $matchedPageId = (string) ($firstPage['id'] ?? '');
            }

            if (! $matchedPageToken) {
                return response()->json([
                    'success' => false,
                    'step' => 'page_token',
                    'error' => 'Could not extract Page Access Token from returned pages.',
                ], 400);
            }

            // Persist App ID and Secret if provided in request
            if ($request->filled('fb_app_id')) {
                Setting::set('fb_app_id', (string) $request->input('fb_app_id'));
            }
            if ($request->filled('fb_app_secret')) {
                Setting::set('fb_app_secret', (string) $request->input('fb_app_secret'));
            }

            return response()->json([
                'success' => true,
                'page_token' => $matchedPageToken,
                'page_name' => $matchedPageName,
                'page_id' => $matchedPageId,
                'pages' => array_map(function ($p) {
                    return [
                        'id' => (string) ($p['id'] ?? ''),
                        'name' => (string) ($p['name'] ?? ''),
                        'access_token' => (string) ($p['access_token'] ?? ''),
                    ];
                }, $pagesData),
                'message' => 'Long-lived Page Access Token generated successfully!',
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'step' => 'page_token', 'error' => 'Page token retrieval failed: '.$e->getMessage()], 500);
        }
    }

    public function verifyToken(Request $request)
    {
        $token = (string) $request->input('access_token');
        $accountId = (string) $request->input('account_id');
        $appId = (string) ($request->input('fb_app_id') ?: Setting::get('fb_app_id'));
        $appSecret = (string) ($request->input('fb_app_secret') ?: Setting::get('fb_app_secret'));

        if (empty($token) || $token === '••••••••') {
            return response()->json(['valid' => false, 'error' => 'No access token provided.'], 400);
        }

        $appToken = (! empty($appId) && ! empty($appSecret)) ? "{$appId}|{$appSecret}" : $token;

        try {
            // 1. Verify basic page access
            $targetEndpoint = ! empty($accountId) ? $accountId : 'me';
            $pageRes = Http::timeout(10)->get("https://graph.facebook.com/v20.0/{$targetEndpoint}", [
                'fields' => 'id,name',
                'access_token' => $token,
            ]);

            $pageData = $pageRes->json();
            if (! isset($pageData['id'])) {
                $errorMsg = $pageData['error']['message'] ?? 'Invalid token or page not accessible.';

                return response()->json(['valid' => false, 'error' => $errorMsg]);
            }

            $result = [
                'valid' => true,
                'page_name' => $pageData['name'] ?? null,
                'page_id' => (string) $pageData['id'],
            ];

            // 2. Debug token details (expiry, scopes, is_valid)
            try {
                $debugRes = Http::timeout(10)->get('https://graph.facebook.com/v20.0/debug_token', [
                    'input_token' => $token,
                    'access_token' => $appToken,
                ]);

                $debugData = $debugRes->json()['data'] ?? [];
                $expiresAt = $debugData['expires_at'] ?? null;

                if ($expiresAt !== null) {
                    $result['expires_at'] = $expiresAt;
                    if ($expiresAt === 0) {
                        $result['expires_in_days'] = 'never';
                        $result['is_long_lived'] = true;
                        $result['token_type'] = 'PAGE';
                    } else {
                        $daysLeft = max(0, (int) round(($expiresAt - time()) / 86400));
                        $result['expires_in_days'] = $daysLeft;
                        $result['is_long_lived'] = $daysLeft > 5;
                        $result['token_type'] = $debugData['type'] ?? 'USER';
                    }
                }
                $result['scopes'] = $debugData['scopes'] ?? [];
            } catch (\Exception $e) {
                // Debug token inspection is optional
            }

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json(['valid' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function suggestHashtags(Request $request)
    {
        $name = $request->input('name') ?: 'Tech Deals';
        $platform = $request->input('platform') ?: 'facebook';
        $context = $request->input('context') ?: '';

        $cleanName = preg_replace('/[^A-Za-z0-9]/', '', ucwords($name));
        $tags = [];
        if ($cleanName) {
            $tags[] = "#{$cleanName}";
        }
        $tags = array_merge($tags, ['#ShopeePH', '#BudolFinds', '#SulitDeals', '#ShopeePayDay']);

        $combined = strtolower("{$name} {$context}");
        $topicMap = [
            'wfh' => ['#WFHSetup', '#WorkFromHomePH', '#DeskSetup'],
            'tech' => ['#TechSulitDeals', '#TechPH', '#GadgetDealsPH'],
            'gadget' => ['#GadgetDealsPH', '#TechBudol', '#TechEssentials'],
            'coffee' => ['#CoffeeTime', '#WFHVibes'],
            'audio' => ['#AudioPH', '#HeadphonesPH', '#WirelessEarbuds'],
            'headphone' => ['#AudioPH', '#HeadphonesPH', '#NoiseCancelling'],
            'keyboard' => ['#MechanicalKeyboard', '#CustomKeyboardPH'],
            'gaming' => ['#GamingPH', '#PCSetup', '#GamerPH'],
            'kitchen' => ['#HomeAndKitchen', '#BudolHome'],
            'beauty' => ['#BeautyPH', '#SkincarePH', '#ShopeeBeauty'],
            'fashion' => ['#OOTDPH', '#FashionPH', '#AffordableFashion'],
            'sale' => ['#ShopeeSale', '#FlashSalePH', '#AffiliatePH'],
        ];

        foreach ($topicMap as $key => $topicTags) {
            if (str_contains($combined, $key)) {
                $tags = array_merge($tags, $topicTags);
            }
        }

        $uniqueTags = array_values(array_unique($tags));

        return response()->json([
            'success' => true,
            'hashtags' => $uniqueTags,
            'raw' => implode(' ', $uniqueTags),
        ]);
    }
}
