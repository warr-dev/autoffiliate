<?php

namespace App\Http\Controllers;

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
            'settings' => Setting::all()->pluck('value', 'key'),
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
        if (!empty($validated['access_token']) && $validated['access_token'] !== '••••••••') {
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
        $account->is_enabled = !$account->is_enabled;
        $account->save();

        return back()->with('success', 'Account status updated.');
    }

    public function storeUser(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        $user = new User();
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->password = Hash::make($validated['password']);
        $user->email_verified_at = now();
        $user->save();

        return back()->with('success', "Account [{$user->email}] created successfully.");
    }

    public function testWebhook(Request $request)
    {
        $url = $request->input('url') ?: Setting::get('n8n_outbound_webhook');
        if (!$url) {
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
                'error' => $response->successful() ? null : 'Received HTTP ' . $response->status(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
